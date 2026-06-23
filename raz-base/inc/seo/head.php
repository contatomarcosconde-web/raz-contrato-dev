<?php
/**
 * Saída de SEO no <head> (contrato §6): título, meta description, canonical e
 * Open Graph / Twitter Cards. Lê os campos por página (inc/seo/meta-box.php) com
 * fallbacks sensatos e defaults globais (Raz → Opções).
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta de SEO do post da query atual.
 *
 * @param string $key
 * @return string
 */
function raz_seo_meta( $key ) {
	$id = get_queried_object_id();
	return $id ? (string) get_post_meta( $id, $key, true ) : '';
}

/**
 * Lê um campo de SEO LOCALIZADO (por idioma) do post atual, com fallbacks.
 *
 * @param string $base Nome base (ex.: 'raz_seo_title').
 * @return string
 */
function raz_seo_lang_meta( $base ) {
	$id = get_queried_object_id();
	if ( ! $id ) {
		return '';
	}
	if ( function_exists( 'raz_lang_field' ) ) {
		return (string) raz_lang_field( $base, '', $id );
	}
	return (string) get_post_meta( $id, $base, true );
}

/**
 * Localiza uma URL para o idioma atual (rota /{lang}/), quando o i18n está ativo.
 *
 * @param string $url
 * @return string
 */
function raz_seo_localize( $url ) {
	if ( function_exists( 'raz_localize_url' ) && function_exists( 'raz_current_lang' ) ) {
		return raz_localize_url( raz_current_lang(), $url );
	}
	return $url;
}

/**
 * Meta description efetiva (campo → resumo → tagline).
 *
 * @return string
 */
function raz_seo_description() {
	if ( is_singular() ) {
		$d = raz_seo_lang_meta( 'raz_seo_desc' );
		if ( ! raz_is_empty( $d ) ) {
			return $d;
		}
		$post = get_queried_object();
		if ( $post ) {
			$excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 );
			if ( ! raz_is_empty( $excerpt ) ) {
				return $excerpt;
			}
		}
	}
	return (string) get_bloginfo( 'description' );
}

/**
 * URL canônica da página atual.
 *
 * @return string
 */
function raz_seo_current_url() {
	if ( is_singular() ) {
		$c = raz_seo_meta( 'raz_canonical' );
		if ( ! raz_is_empty( $c ) ) {
			return $c;
		}
		return raz_seo_localize( (string) get_permalink( get_queried_object_id() ) );
	}
	if ( is_front_page() || is_home() ) {
		return raz_seo_localize( home_url( '/' ) );
	}
	$req = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	return home_url( $req );
}

/**
 * Imagem para Open Graph (campo → destacada → padrão global).
 *
 * @return string
 */
function raz_seo_og_image() {
	if ( is_singular() ) {
		$img = raz_seo_meta( 'raz_og_image' );
		if ( ! raz_is_empty( $img ) ) {
			return $img;
		}
		$id = get_queried_object_id();
		if ( has_post_thumbnail( $id ) ) {
			$src = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'large' );
			if ( $src ) {
				return $src[0];
			}
		}
	}
	return (string) raz_option( 'og_default_image' );
}

add_filter( 'pre_get_document_title', 'raz_seo_document_title', 20 );
/**
 * Título SEO custom por página (sobrescreve o título do documento).
 *
 * @param string $title
 * @return string
 */
function raz_seo_document_title( $title ) {
	if ( is_singular() ) {
		$t = raz_seo_lang_meta( 'raz_seo_title' );
		if ( ! raz_is_empty( $t ) ) {
			return $t;
		}
	}
	return $title;
}

add_filter( 'get_canonical_url', 'raz_seo_filter_canonical', 10, 2 );
/**
 * Sobrescreve a canônica quando há campo preenchido.
 *
 * @param string  $url
 * @param WP_Post $post
 * @return string
 */
function raz_seo_filter_canonical( $url, $post ) {
	$c = get_post_meta( $post->ID, 'raz_canonical', true );
	return raz_is_empty( $c ) ? raz_seo_localize( $url ) : $c;
}

add_action( 'wp_head', 'raz_seo_head_tags', 1 );
/**
 * Imprime meta description + Open Graph + Twitter Cards.
 */
function raz_seo_head_tags() {
	if ( is_404() ) {
		return;
	}

	$desc     = raz_seo_description();
	$url      = raz_seo_current_url();
	$title    = wp_get_document_title();
	$og_title_m = is_singular() ? raz_seo_lang_meta( 'raz_og_title' ) : '';
	$og_title   = ! raz_is_empty( $og_title_m ) ? $og_title_m : $title;
	$og_desc_m  = is_singular() ? raz_seo_lang_meta( 'raz_og_desc' ) : '';
	$og_desc    = ! raz_is_empty( $og_desc_m ) ? $og_desc_m : $desc;
	$image    = raz_seo_og_image();
	$type     = is_singular( 'post' ) ? 'article' : 'website';
	$twitter  = raz_option( 'twitter_site' );

	echo "\n<!-- Raz SEO -->\n";
	if ( ! raz_is_empty( $desc ) ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $desc ) );
	}

	printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $og_title ) );
	if ( ! raz_is_empty( $og_desc ) ) {
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $og_desc ) );
	}
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $url ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:locale" content="%s" />' . "\n", esc_attr( get_locale() ) );
	if ( ! raz_is_empty( $image ) ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image ) );
	}

	printf( '<meta name="twitter:card" content="%s" />' . "\n", esc_attr( raz_is_empty( $image ) ? 'summary' : 'summary_large_image' ) );
	if ( ! raz_is_empty( $twitter ) ) {
		printf( '<meta name="twitter:site" content="%s" />' . "\n", esc_attr( '@' . ltrim( $twitter, '@' ) ) );
	}
	printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $og_title ) );
	if ( ! raz_is_empty( $og_desc ) ) {
		printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $og_desc ) );
	}
	if ( ! raz_is_empty( $image ) ) {
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $image ) );
	}
	echo "<!-- /Raz SEO -->\n";
}
