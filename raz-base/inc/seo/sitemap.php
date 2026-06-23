<?php
/**
 * Sitemap (contrato §6) — usa o sitemap NATIVO do WordPress (/wp-sitemap.xml),
 * com controles do painel: liga/desliga, exclui tipos de conteúdo e remove páginas
 * marcadas como noindex. CPTs não-públicos (raz_popup/raz_form) já ficam de fora.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'wp_sitemaps_enabled', 'raz_sitemap_enabled' );
/**
 * Liga/desliga o sitemap nativo conforme o painel/indexação.
 *
 * @param bool $enabled
 * @return bool
 */
function raz_sitemap_enabled( $enabled ) {
	return raz_seo_sitemap_enabled();
}

add_filter( 'wp_sitemaps_post_types', 'raz_sitemap_post_types' );
/**
 * Remove do sitemap os tipos de conteúdo excluídos no painel.
 *
 * @param array $post_types Mapa name => WP_Post_Type.
 * @return array
 */
function raz_sitemap_post_types( $post_types ) {
	$raw = (string) raz_option( 'sitemap_exclude' );
	if ( '' === trim( $raw ) ) {
		return $post_types;
	}
	foreach ( explode( ',', $raw ) as $slug ) {
		$slug = sanitize_key( trim( $slug ) );
		if ( $slug && isset( $post_types[ $slug ] ) ) {
			unset( $post_types[ $slug ] );
		}
	}
	return $post_types;
}

add_filter( 'wp_sitemaps_posts_query_args', 'raz_sitemap_exclude_noindex', 10, 2 );
/**
 * Exclui do sitemap os posts marcados com noindex (meta raz_noindex = 1).
 *
 * @param array  $args
 * @param string $post_type
 * @return array
 */
function raz_sitemap_exclude_noindex( $args, $post_type ) {
	$meta = isset( $args['meta_query'] ) ? $args['meta_query'] : array();
	$meta[] = array(
		'relation' => 'OR',
		array( 'key' => 'raz_noindex', 'compare' => 'NOT EXISTS' ),
		array( 'key' => 'raz_noindex', 'value' => '1', 'compare' => '!=' ),
	);
	$args['meta_query'] = $meta;
	return $args;
}
