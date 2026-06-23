<?php
/**
 * /llms.txt (contrato §6) — declara a postura do site para IA/LLMs.
 *
 * Servido só quando ligado no painel. Conteúdo customizável; vazio = gerado a partir
 * do nome/descrição do site, da política (permitir/bloquear) e do sitemap.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * Conteúdo padrão do /llms.txt (Markdown), conforme a política do painel.
 *
 * @return string
 */
function raz_llms_default() {
	$name  = get_bloginfo( 'name' );
	$desc  = get_bloginfo( 'description' );
	$home  = home_url( '/' );
	$block = (bool) raz_option( 'llms_block' );

	$l   = array();
	$l[] = '# ' . $name;
	if ( ! raz_is_empty( $desc ) ) {
		$l[] = '';
		$l[] = '> ' . $desc;
	}
	$l[] = '';
	$l[] = '## Política';
	if ( $block ) {
		$l[] = 'O rastreamento e o treinamento de IA/LLMs com este conteúdo NÃO são permitidos.';
		$l[] = 'AI crawling and training on this content is NOT permitted.';
	} else {
		$l[] = 'Assistentes de IA podem referenciar o conteúdo público deste site com atribuição.';
		$l[] = '';
		$l[] = '## Site';
		$l[] = '- Home: ' . $home;
		if ( raz_seo_sitemap_enabled() ) {
			$l[] = '- Sitemap: ' . home_url( '/wp-sitemap.xml' );
		}
	}
	$l[] = '';

	return implode( "\n", $l ) . "\n";
}

add_action( 'template_redirect', 'raz_serve_llms', 0 );
/**
 * Intercepta /llms.txt e devolve o conteúdo como texto puro.
 */
function raz_serve_llms() {
	if ( ! raz_option( 'llms_on' ) ) {
		return;
	}

	$req  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path = (string) wp_parse_url( $req, PHP_URL_PATH );
	$home = function_exists( 'raz_home_path' ) ? raz_home_path() : '/';

	if ( $path !== $home . 'llms.txt' && '/llms.txt' !== $path ) {
		return;
	}

	$content = raz_option( 'llms_content' );
	if ( raz_is_empty( $content ) ) {
		$content = raz_llms_default();
	}

	nocache_headers();
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput — texto puro, não HTML
	exit;
}
