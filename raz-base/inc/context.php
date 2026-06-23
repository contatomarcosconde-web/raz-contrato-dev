<?php
/**
 * Detecção de contexto centralizada (contrato §4).
 *
 * Uma única função decide "onde estamos". enqueue.php e template-loader.php
 * consomem isto — nunca refazem a detecção espalhada.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * Contexto de alto nível da requisição atual.
 *
 * @return string Um de: front | home | page | single | archive | search | 404.
 */
function raz_context() {
	if ( is_404() ) {
		return '404';
	}
	if ( is_search() ) {
		return 'search';
	}
	if ( is_front_page() ) {
		return 'front';
	}
	if ( is_home() ) {
		return 'home'; // página de posts (blog)
	}
	if ( is_page() ) {
		return 'page';
	}
	if ( is_singular() ) {
		return 'single';
	}
	if ( is_archive() ) {
		return 'archive';
	}
	return 'index';
}

/**
 * Slug da "view" usado para localizar template-parts e assets:
 * template-parts/page-{slug}/  e  assets/css/page-{slug}/.
 *
 * @return string Ex.: 'home', 'sobre', 'single-post'.
 */
function raz_view_slug() {
	switch ( raz_context() ) {
		case 'front':
			$front = get_post();
			return $front ? sanitize_title( $front->post_name ) : 'home';
		case 'page':
			$post = get_post();
			return $post ? sanitize_title( $post->post_name ) : 'page';
		case 'single':
			return 'single-' . sanitize_title( get_post_type() );
		case 'archive':
			return 'archive-' . sanitize_title( get_post_type() );
		default:
			return raz_context();
	}
}

/**
 * Lista de seções a renderizar para uma view (página = lista de seções, §4).
 * Vazio por padrão; cada projeto/página declara suas seções via filtro.
 *
 * @param string $slug Slug da view (raz_view_slug()).
 * @return string[] Slugs de seção (ex.: ['hero','servicos']).
 */
function raz_page_sections( $slug ) {
	$map = array(
		// Home inicial: página "Em construção" da RAZ (trocar pelas seções reais no lançamento).
		'home' => array( 'coming-soon' ),
	);

	$sections = isset( $map[ $slug ] ) ? $map[ $slug ] : array();

	/** Permite que o projeto registre/modifique as seções por view. */
	return apply_filters( 'raz_page_sections', $sections, $slug );
}
