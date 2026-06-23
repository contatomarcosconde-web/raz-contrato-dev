<?php
/**
 * Manutenção — API REST de arquivos (edição via HTTP sem SSH).
 *
 * Autenticação: usuário WordPress com manage_options (cookie OU Application Password
 * nativa, enviada por Basic Auth). Reaproveita os helpers seguros de maintenance.php
 * (resolve dentro da raiz, lint antes de salvar, backup .bak, log). Allowlist de IP.
 *
 * Uso (Application Password):
 *   curl -u "usuario:APP PASSWORD" https://site/wp-json/raz/v1/fs/read?path=wp-content/themes/raz-base/style.css
 *   curl -u "usuario:APP PASSWORD" -X POST https://site/wp-json/raz/v1/fs/write \
 *        -H "Content-Type: application/json" -d '{"path":"...","content":"..."}'
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', 'raz_fs_register_routes' );
/**
 * Registra as rotas REST do gerenciador de arquivos.
 */
function raz_fs_register_routes() {
	$perm = 'raz_fs_rest_permission';
	register_rest_route( 'raz/v1', '/fs/list', array(
		'methods'             => 'GET',
		'permission_callback' => $perm,
		'callback'            => 'raz_fs_rest_list',
	) );
	register_rest_route( 'raz/v1', '/fs/read', array(
		'methods'             => 'GET',
		'permission_callback' => $perm,
		'callback'            => 'raz_fs_rest_read',
	) );
	register_rest_route( 'raz/v1', '/fs/write', array(
		'methods'             => 'POST',
		'permission_callback' => $perm,
		'callback'            => 'raz_fs_rest_write',
	) );
	register_rest_route( 'raz/v1', '/fs/purge', array(
		'methods'             => 'POST',
		'permission_callback' => $perm,
		'callback'            => 'raz_fs_rest_purge',
	) );
}

/**
 * POST /fs/purge — limpa os caches conhecidos (página/objeto).
 *
 * @return WP_REST_Response
 */
function raz_fs_rest_purge() {
	$done = raz_fs_purge_cache();
	raz_fs_log( 'rest-purge', implode( ',', $done ) );
	return new WP_REST_Response( array( 'ok' => true, 'purged' => $done ), 200 );
}

/**
 * Permissão das rotas: admin autenticado + recurso ligado + IP permitido.
 *
 * @return bool|WP_Error
 */
function raz_fs_rest_permission() {
	raz_fs_nocache(); // NUNCA cachear respostas de arquivos (vazaria conteúdo autenticado)
	if ( ! raz_fs_enabled() ) {
		return new WP_Error( 'raz_fs_disabled', __( 'Gerenciador de arquivos desativado.', 'raz' ), array( 'status' => 403 ) );
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return new WP_Error( 'raz_fs_forbidden', __( 'Autenticação de administrador necessária.', 'raz' ), array( 'status' => 401 ) );
	}
	if ( ! raz_fs_ip_allowed() ) {
		return new WP_Error( 'raz_fs_ip', __( 'IP não autorizado.', 'raz' ), array( 'status' => 403 ) );
	}
	return true;
}

/**
 * GET /fs/list?path= — lista um diretório.
 *
 * @param WP_REST_Request $req
 * @return WP_REST_Response|WP_Error
 */
function raz_fs_rest_list( $req ) {
	$abs = raz_fs_resolve( (string) $req->get_param( 'path' ) );
	if ( ! $abs || ! is_dir( $abs ) ) {
		return new WP_Error( 'raz_fs_dir', __( 'Diretório inválido.', 'raz' ), array( 'status' => 400 ) );
	}
	$items = array();
	foreach ( (array) @scandir( $abs ) as $entry ) {
		if ( '.' === $entry || '..' === $entry ) {
			continue;
		}
		$child = $abs . '/' . $entry;
		$items[] = array(
			'name' => $entry,
			'path' => raz_fs_rel( $child ),
			'type' => is_dir( $child ) ? 'dir' : 'file',
			'size' => is_dir( $child ) ? null : (int) @filesize( $child ),
		);
	}
	return new WP_REST_Response( array( 'path' => raz_fs_rel( $abs ), 'items' => $items ), 200 );
}

/**
 * GET /fs/read?path= — devolve o conteúdo de um arquivo.
 *
 * @param WP_REST_Request $req
 * @return WP_REST_Response|WP_Error
 */
function raz_fs_rest_read( $req ) {
	$abs = raz_fs_resolve( (string) $req->get_param( 'path' ) );
	if ( ! $abs || ! is_file( $abs ) ) {
		return new WP_Error( 'raz_fs_file', __( 'Arquivo inválido.', 'raz' ), array( 'status' => 400 ) );
	}
	return new WP_REST_Response( array(
		'path'     => raz_fs_rel( $abs ),
		'writable' => is_writable( $abs ),
		'content'  => (string) file_get_contents( $abs ),
	), 200 );
}

/**
 * POST /fs/write {path, content} — grava (lint + backup) e loga.
 *
 * @param WP_REST_Request $req
 * @return WP_REST_Response|WP_Error
 */
function raz_fs_rest_write( $req ) {
	$abs = raz_fs_resolve( (string) $req->get_param( 'path' ) );
	if ( ! $abs || ! is_file( $abs ) ) {
		return new WP_Error( 'raz_fs_file', __( 'Arquivo inválido.', 'raz' ), array( 'status' => 400 ) );
	}
	$content = (string) $req->get_param( 'content' );
	$res     = raz_fs_write( $abs, $content );
	if ( is_wp_error( $res ) ) {
		$res->add_data( array( 'status' => 422 ) );
		return $res;
	}
	raz_fs_log( 'rest-write', raz_fs_rel( $abs ) );
	return new WP_REST_Response( array( 'ok' => true, 'path' => raz_fs_rel( $abs ), 'backup' => raz_fs_rel( $abs ) . '.bak' ), 200 );
}
