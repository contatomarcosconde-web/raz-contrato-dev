<?php
/**
 * robots.txt gerenciável (contrato §6).
 *
 * Respeita a chave global de indexação: site não-indexável → bloqueia tudo.
 * Indexável → regras padrão + regras extras do painel + (opcional) bloqueio de IA +
 * link do sitemap. Só funciona se NÃO houver um robots.txt físico na raiz.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lista de robôs de IA/LLMs para bloqueio opcional.
 *
 * @return string[]
 */
function raz_ai_bots() {
	return apply_filters( 'raz_ai_bots', array(
		'GPTBot', 'ChatGPT-User', 'OAI-SearchBot', 'CCBot', 'Google-Extended',
		'anthropic-ai', 'ClaudeBot', 'Claude-Web', 'PerplexityBot', 'Applebot-Extended',
		'Bytespider', 'Amazonbot', 'Meta-ExternalAgent', 'cohere-ai', 'Diffbot',
	) );
}

/**
 * Bloco de Disallow para os robôs de IA.
 *
 * @return string
 */
function raz_robots_ai_block() {
	$out = "\n# IA/LLMs bloqueados\n";
	foreach ( raz_ai_bots() as $bot ) {
		$out .= 'User-agent: ' . $bot . "\nDisallow: /\n\n";
	}
	return $out;
}

add_filter( 'robots_txt', 'raz_robots_txt', 10, 2 );
/**
 * Monta o robots.txt conforme o painel.
 *
 * @param string $output Conteúdo padrão do WP.
 * @param bool   $public blog_public.
 * @return string
 */
function raz_robots_txt( $output, $public ) {
	// Site não-indexável (staging / chave global desligada): bloqueia tudo.
	if ( ! raz_seo_indexable() ) {
		return "User-agent: *\nDisallow: /\n";
	}

	$out  = "User-agent: *\n";
	$out .= "Disallow: /wp-admin/\n";
	$out .= "Allow: /wp-admin/admin-ajax.php\n";

	// Regras extras do cliente.
	$extra = raz_option( 'robots_rules' );
	if ( ! raz_is_empty( $extra ) ) {
		$out .= "\n# Regras do site\n" . trim( $extra ) . "\n";
	}

	// Bloqueio de IA (postura llms_block).
	if ( raz_option( 'llms_block' ) ) {
		$out .= raz_robots_ai_block();
	}

	// Link do sitemap, se ativo.
	if ( raz_seo_sitemap_enabled() ) {
		$out .= "\nSitemap: " . esc_url_raw( home_url( '/wp-sitemap.xml' ) ) . "\n";
	}

	return $out;
}
