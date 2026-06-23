<?php
/**
 * CPT `raz_form` — Sistema de Formulários (contrato §7-bis).
 *
 * Template livre (mesma base do popup): o cliente escreve o HTML do <form> por idioma,
 * + CSS (escopado) + JS, e configura o ENVIO (destino/provedores). Ao salvar, o tema
 * gera um SHORTCODE `[raz_form id="X"]` para colar onde quiser. O envio passa por um
 * handler único e pelo registry de provedores (v1: e-mail).
 *
 * Meta keys (raz_form_):
 *   conteúdo: raz_form_html__{lang} · raz_form_css · raz_form_js
 *   envio:    raz_form_email, raz_form_subject, raz_form_success, raz_form_error,
 *             raz_form_consent_require (1/0), raz_form_consent_field, raz_form_providers (array)
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'raz_register_form_cpt' );
/**
 * Registra o CPT de formulários.
 */
function raz_register_form_cpt() {
	register_post_type( 'raz_form', array(
		'labels'              => array(
			'name'          => __( 'Formulários', 'raz' ),
			'singular_name' => __( 'Formulário', 'raz' ),
			'add_new_item'  => __( 'Adicionar novo formulário', 'raz' ),
			'edit_item'     => __( 'Editar formulário', 'raz' ),
			'menu_name'     => __( 'Formulários', 'raz' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => false,
		'menu_icon'           => 'dashicons-feedback',
		'menu_position'       => 57,
		'supports'            => array( 'title' ),
		'has_archive'         => false,
		'rewrite'             => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
	) );
}

add_action( 'add_meta_boxes', 'raz_form_meta_boxes' );
/**
 * Registra as meta boxes do formulário.
 */
function raz_form_meta_boxes() {
	add_meta_box( 'raz_form_content', __( 'HTML do formulário (por idioma)', 'raz' ), 'raz_form_box_content', 'raz_form', 'normal', 'high' );
	add_meta_box( 'raz_form_code', __( 'CSS & JS', 'raz' ), 'raz_form_box_code', 'raz_form', 'normal', 'default' );
	add_meta_box( 'raz_form_send', __( 'Envio & Integrações', 'raz' ), 'raz_form_box_send', 'raz_form', 'normal', 'default' );
	add_meta_box( 'raz_form_shortcode', __( 'Shortcode', 'raz' ), 'raz_form_box_shortcode', 'raz_form', 'side', 'high' );
}

/**
 * Meta box: HTML do <form> por idioma.
 *
 * @param WP_Post $post
 */
function raz_form_box_content( $post ) {
	wp_nonce_field( 'raz_form_save', 'raz_form_nonce' );
	$langs = function_exists( 'raz_supported_languages' ) ? raz_supported_languages() : array( 'pt' => 'Português' );

	echo '<div style="background:#f6f7f7;border:1px solid #dcdcde;padding:8px 10px;margin:0 0 10px;font-size:12px;line-height:1.6">';
	echo '<strong>' . esc_html__( 'Como escrever:', 'raz' ) . '</strong> ' . esc_html__( 'inclua a tag <form> completa, com seus campos e o botão de enviar. A segurança (nonce, anti-spam) é injetada automaticamente — você só monta o markup.', 'raz' );
	echo '<br>' . esc_html__( 'Para LGPD (se exigido em Envio), inclua um checkbox com name="consent". Use names claros nos campos (ex.: name="nome", name="email", name="telefone").', 'raz' );
	echo '</div>';

	foreach ( $langs as $slug => $label ) {
		$key = 'raz_form_html__' . $slug;
		$val = get_post_meta( $post->ID, $key, true );
		printf( '<p><label for="%1$s"><strong>%2$s (%3$s)</strong></label></p>', esc_attr( $key ), esc_html( $label ), esc_html( strtoupper( $slug ) ) );
		printf(
			'<textarea id="%1$s" name="%1$s" class="widefat code" rows="9" placeholder="%3$s">%2$s</textarea>',
			esc_attr( $key ),
			esc_textarea( (string) $val ),
			esc_attr__( "<form>\n  <input name=\"nome\" placeholder=\"Nome\" required>\n  <input type=\"email\" name=\"email\" required>\n  <button type=\"submit\">Enviar</button>\n</form>", 'raz' )
		);
	}
}

/**
 * Meta box: CSS (escopado) + JS.
 *
 * @param WP_Post $post
 */
function raz_form_box_code( $post ) {
	$css = get_post_meta( $post->ID, 'raz_form_css', true );
	$js  = get_post_meta( $post->ID, 'raz_form_js', true );

	echo '<p><strong>' . esc_html__( 'CSS (aplicado só a este formulário)', 'raz' ) . '</strong></p>';
	echo '<p class="description">' . esc_html__( 'As regras são escopadas automaticamente ao formulário (não vazam). Classes prontas: .raz-form (wrapper), .raz-form__msg (mensagem de status).', 'raz' ) . '</p>';
	printf( '<textarea name="raz_form_css" class="widefat code" rows="6" placeholder="%s">%s</textarea>', esc_attr__( '.raz-form input { width:100%; } .raz-form__msg.is-error { color:#b00; }', 'raz' ), esc_textarea( (string) $css ) );

	echo '<p style="margin-top:14px"><strong>' . esc_html__( 'JavaScript (executado nesta página)', 'raz' ) . '</strong></p>';
	echo '<p class="description">' . esc_html__( 'Código próprio do formulário (máscaras, validações extras…). Roda no front. Não inclua a tag <script>.', 'raz' ) . '</p>';
	printf( '<textarea name="raz_form_js" class="widefat code" rows="6" placeholder="%s">%s</textarea>', esc_attr__( "// ex.: máscara de telefone", 'raz' ), esc_textarea( (string) $js ) );
}

/**
 * Meta box: configuração de envio + provedores.
 *
 * @param WP_Post $post
 */
function raz_form_box_send( $post ) {
	$g = function ( $key, $default = '' ) use ( $post ) {
		$v = get_post_meta( $post->ID, $key, true );
		return ( '' === $v || null === $v ) ? $default : $v;
	};

	$email          = $g( 'raz_form_email', get_option( 'admin_email' ) );
	$subject        = $g( 'raz_form_subject' );
	$success        = $g( 'raz_form_success', __( 'Mensagem enviada com sucesso!', 'raz' ) );
	$error          = $g( 'raz_form_error', __( 'Não foi possível enviar agora. Tente novamente.', 'raz' ) );
	$consent_req    = $g( 'raz_form_consent_require', '1' );
	$consent_field  = $g( 'raz_form_consent_field', 'consent' );
	$enabled        = get_post_meta( $post->ID, 'raz_form_providers', true );
	$enabled        = is_array( $enabled ) ? $enabled : array( 'email' );

	echo '<table class="form-table" role="presentation"><tbody>';
	printf( '<tr><th scope="row">%s</th><td><input type="text" name="raz_form_email" value="%s" class="regular-text" /> <span class="description">%s</span></td></tr>', esc_html__( 'E-mail de destino', 'raz' ), esc_attr( $email ), esc_html__( 'separe por vírgula p/ vários', 'raz' ) );
	printf( '<tr><th scope="row">%s</th><td><input type="text" name="raz_form_subject" value="%s" class="regular-text" placeholder="%s" /></td></tr>', esc_html__( 'Assunto do e-mail', 'raz' ), esc_attr( $subject ), esc_attr__( 'Novo envio: (título do form)', 'raz' ) );
	printf( '<tr><th scope="row">%s</th><td><input type="text" name="raz_form_success" value="%s" class="large-text" /></td></tr>', esc_html__( 'Mensagem de sucesso', 'raz' ), esc_attr( $success ) );
	printf( '<tr><th scope="row">%s</th><td><input type="text" name="raz_form_error" value="%s" class="large-text" /></td></tr>', esc_html__( 'Mensagem de erro', 'raz' ), esc_attr( $error ) );

	// Consentimento LGPD
	printf(
		'<tr><th scope="row">%s</th><td><label><input type="checkbox" name="raz_form_consent_require" value="1" %s /> %s</label><p style="margin:6px 0 0">%s <input type="text" name="raz_form_consent_field" value="%s" /> <span class="description">%s</span></p></td></tr>',
		esc_html__( 'Consentimento (LGPD)', 'raz' ),
		checked( '1', $consent_req, false ),
		esc_html__( 'Exigir consentimento para enviar', 'raz' ),
		esc_html__( 'Nome do campo do checkbox:', 'raz' ),
		esc_attr( $consent_field ),
		esc_html__( 'inclua <input type="checkbox" name="consent" required> no seu HTML', 'raz' )
	);

	// Provedores (do registry — futuros aparecem sozinhos)
	$providers = function_exists( 'raz_form_providers' ) ? raz_form_providers() : array();
	$checks    = '';
	foreach ( $providers as $pid => $provider ) {
		$checks .= sprintf(
			'<label style="display:block;margin:2px 0"><input type="checkbox" name="raz_form_providers[]" value="%s" %s /> %s</label>',
			esc_attr( $pid ),
			checked( true, in_array( $pid, $enabled, true ), false ),
			esc_html( $provider->label() )
		);
	}
	printf( '<tr><th scope="row">%s</th><td>%s<p class="description">%s</p></td></tr>', esc_html__( 'Enviar para', 'raz' ), $checks, esc_html__( 'Outros provedores (RD Station, ActiveCampaign, Mailchimp) entram como adaptadores nas próximas versões.', 'raz' ) );

	echo '</tbody></table>';
}

/**
 * Meta box lateral: shortcode gerado.
 *
 * @param WP_Post $post
 */
function raz_form_box_shortcode( $post ) {
	$sc = '[raz_form id="' . (int) $post->ID . '"]';
	echo '<p>' . esc_html__( 'Cole onde quiser exibir o formulário (página, post, widget):', 'raz' ) . '</p>';
	printf( '<input type="text" readonly value="%s" class="widefat code" onclick="this.select()" />', esc_attr( $sc ) );
	echo '<p class="description">' . esc_html__( 'Publique o formulário para ativá-lo. O conteúdo é exibido no idioma atual da página.', 'raz' ) . '</p>';
}

add_action( 'save_post_raz_form', 'raz_form_save', 10, 2 );
/**
 * Salva os campos do formulário (nonce + capability + sanitização).
 *
 * @param int     $post_id
 * @param WP_Post $post
 */
function raz_form_save( $post_id, $post ) {
	if ( ! isset( $_POST['raz_form_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['raz_form_nonce'] ), 'raz_form_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// HTML por idioma — admin (unfiltered_html) salva cru; demais via raz_kses (mantém <form>/inputs).
	$langs = function_exists( 'raz_supported_languages' ) ? array_keys( raz_supported_languages() ) : array( 'pt' );
	foreach ( $langs as $slug ) {
		$key = 'raz_form_html__' . $slug;
		if ( isset( $_POST[ $key ] ) ) {
			$raw  = wp_unslash( $_POST[ $key ] );
			$html = current_user_can( 'unfiltered_html' ) ? $raw : ( function_exists( 'raz_kses_form' ) ? raz_kses_form( $raw ) : wp_kses_post( $raw ) );
			update_post_meta( $post_id, $key, $html );
		}
	}

	// CSS (escopado na renderização) e JS (qualquer editor do form pode salvar).
	if ( isset( $_POST['raz_form_css'] ) ) {
		$css = wp_unslash( $_POST['raz_form_css'] );
		update_post_meta( $post_id, 'raz_form_css', function_exists( 'raz_sanitize_css' ) ? raz_sanitize_css( $css ) : wp_strip_all_tags( $css ) );
	}
	if ( isset( $_POST['raz_form_js'] ) ) {
		$js = wp_unslash( $_POST['raz_form_js'] );
		// Não pode conter tag de script (é embrulhado em <script> na saída).
		$js = str_ireplace( array( '</script', '<script' ), '', $js );
		update_post_meta( $post_id, 'raz_form_js', $js );
	}

	// Textos de envio
	foreach ( array( 'raz_form_email', 'raz_form_subject', 'raz_form_success', 'raz_form_error', 'raz_form_consent_field' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	update_post_meta( $post_id, 'raz_form_consent_require', empty( $_POST['raz_form_consent_require'] ) ? '0' : '1' );

	// Provedores
	$providers = isset( $_POST['raz_form_providers'] ) ? (array) wp_unslash( $_POST['raz_form_providers'] ) : array();
	$providers = array_values( array_map( 'sanitize_key', $providers ) );
	update_post_meta( $post_id, 'raz_form_providers', $providers ?: array( 'email' ) );
}

/**
 * Config consolidada de um formulário (para o handler/provedores).
 *
 * @param int $form_id
 * @return array
 */
function raz_form_get_config( $form_id ) {
	$get = function ( $key, $default = '' ) use ( $form_id ) {
		$v = get_post_meta( $form_id, $key, true );
		return ( '' === $v || null === $v ) ? $default : $v;
	};
	$providers = get_post_meta( $form_id, 'raz_form_providers', true );

	return array(
		'email'           => $get( 'raz_form_email', get_option( 'admin_email' ) ),
		'subject'         => $get( 'raz_form_subject' ),
		'success'         => $get( 'raz_form_success', __( 'Mensagem enviada com sucesso!', 'raz' ) ),
		'error'           => $get( 'raz_form_error', __( 'Não foi possível enviar agora. Tente novamente.', 'raz' ) ),
		'consent_require' => ( '1' === (string) $get( 'raz_form_consent_require', '1' ) ),
		'consent_field'   => $get( 'raz_form_consent_field', 'consent' ),
		'providers'       => is_array( $providers ) && $providers ? $providers : array( 'email' ),
	);
}
