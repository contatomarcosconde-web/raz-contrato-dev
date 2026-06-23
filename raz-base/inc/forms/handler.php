<?php
/**
 * Formulários — handler ÚNICO via REST (contrato §7-bis / §9).
 *
 * Rotas:
 *   GET  /wp-json/raz/v1/nonce   → devolve um nonce fresco (à prova de cache de página).
 *   POST /wp-json/raz/v1/submit  → valida (nonce + anti-spam), coleta campos e despacha.
 *
 * Segurança: nonce + honeypot + tempo mínimo + rate-limit por IP; segredos só no servidor;
 * sanitização de toda entrada. Sem CPT de lead (só despacho aos provedores).
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', 'raz_form_register_routes' );
/**
 * Registra as rotas REST do formulário.
 */
function raz_form_register_routes() {
	register_rest_route( 'raz/v1', '/nonce', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => 'raz_form_rest_nonce',
	) );
	register_rest_route( 'raz/v1', '/submit', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'raz_form_rest_submit',
	) );
}

/**
 * Resposta padrão da API do formulário (status 200; o estado vai no campo success).
 *
 * @param bool   $success
 * @param string $message
 * @return WP_REST_Response
 */
function raz_form_resp( $success, $message ) {
	return new WP_REST_Response( array( 'success' => (bool) $success, 'message' => $message ), 200 );
}

/**
 * IP do cliente (best-effort).
 *
 * @return string
 */
function raz_form_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '0.0.0.0';
	return sanitize_text_field( $ip );
}

/**
 * GET /nonce — nonce fresco para o envio (não é cacheado pelo cache de página).
 *
 * @return WP_REST_Response
 */
function raz_form_rest_nonce() {
	return new WP_REST_Response( array( 'nonce' => wp_create_nonce( 'raz_form' ) ), 200 );
}

/**
 * POST /submit — valida, coleta e despacha o envio.
 *
 * @param WP_REST_Request $req
 * @return WP_REST_Response
 */
function raz_form_rest_submit( $req ) {
	$p = $req->get_params();

	// Rate-limit por IP.
	$rl_key = 'raz_form_rl_' . md5( raz_form_client_ip() );
	$hits   = (int) get_transient( $rl_key );
	if ( $hits >= 8 ) {
		return raz_form_resp( false, __( 'Muitas tentativas. Aguarde um instante e tente de novo.', 'raz' ) );
	}

	// Nonce.
	$nonce = isset( $p['raz_nonce'] ) ? $p['raz_nonce'] : '';
	if ( ! wp_verify_nonce( $nonce, 'raz_form' ) ) {
		return raz_form_resp( false, __( 'Sua sessão expirou. Recarregue a página e tente novamente.', 'raz' ) );
	}
	set_transient( $rl_key, $hits + 1, MINUTE_IN_SECONDS );

	// Anti-spam: honeypot preenchido ou envio rápido demais → finge sucesso (não envia).
	$hp      = isset( $p['raz_hp'] ) ? trim( (string) $p['raz_hp'] ) : '';
	$elapsed = isset( $p['raz_elapsed'] ) ? intval( $p['raz_elapsed'] ) : 9999;
	if ( '' !== $hp || $elapsed < 1500 ) {
		return raz_form_resp( true, __( 'Mensagem enviada com sucesso!', 'raz' ) );
	}

	// Formulário válido e publicado.
	$form_id = isset( $p['raz_form_id'] ) ? absint( $p['raz_form_id'] ) : 0;
	$form    = $form_id ? get_post( $form_id ) : null;
	if ( ! $form || 'raz_form' !== $form->post_type || 'publish' !== $form->post_status ) {
		return raz_form_resp( false, __( 'Formulário inválido.', 'raz' ) );
	}

	$cfg = raz_form_get_config( $form_id );

	// Consentimento (LGPD), se exigido.
	if ( ! empty( $cfg['consent_require'] ) ) {
		$field = $cfg['consent_field'] ?: 'consent';
		if ( empty( $p[ $field ] ) ) {
			return raz_form_resp( false, __( 'É necessário aceitar os termos para enviar.', 'raz' ) );
		}
	}

	// Coleta e sanitiza os campos do usuário.
	$internal = array( 'raz_nonce', 'raz_hp', 'raz_elapsed', 'raz_origin', 'raz_form_id', '_wpnonce', 'action', 'rest_route' );
	$lead     = array();
	foreach ( $p as $k => $v ) {
		if ( in_array( $k, $internal, true ) ) {
			continue;
		}
		$key = sanitize_key( $k );
		if ( '' === $key ) {
			continue;
		}
		$lead[ $key ] = is_array( $v ) ? array_map( 'sanitize_text_field', $v ) : sanitize_textarea_field( (string) $v );
	}

	if ( empty( $lead ) ) {
		return raz_form_resp( false, $cfg['error'] );
	}

	$lead['_origin']     = isset( $p['raz_origin'] ) ? esc_url_raw( $p['raz_origin'] ) : '';
	$lead['_form_id']    = $form_id;
	$lead['_form_title'] = get_the_title( $form_id );

	// Despacha aos provedores habilitados.
	$results = raz_form_dispatch( $lead, $cfg );
	$ok      = in_array( true, $results, true );

	/** Hook pós-envio (logs, CRM custom, etc.). */
	do_action( 'raz_form_submitted', $lead, $cfg, $results );

	return $ok ? raz_form_resp( true, $cfg['success'] ) : raz_form_resp( false, $cfg['error'] );
}
