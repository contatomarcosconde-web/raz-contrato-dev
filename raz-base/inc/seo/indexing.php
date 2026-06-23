<?php
/**
 * Controle de indexação (contrato §6) — base.
 *
 * Chave global "indexar este site?" (default seguro: NÃO indexar até produção) +
 * noindex por página (campo `raz_noindex`). robots.txt/sitemap/llms completos
 * entram no painel da iteração 2.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * O site (globalmente) pode ser indexado? Chave do painel (default: NÃO até produção).
 *
 * @return bool
 */
function raz_seo_indexable() {
	return '1' === (string) raz_option( 'indexar', '0' );
}

/**
 * O sitemap nativo deve ficar ativo? Ligado por padrão; desligado se o site é noindex
 * ou se o cliente marcou "Desativar o sitemap.xml".
 *
 * @return bool
 */
function raz_seo_sitemap_enabled() {
	if ( ! raz_seo_indexable() ) {
		return false;
	}
	return '1' !== (string) raz_option( 'sitemap_off', '0' );
}

/**
 * Deve a requisição atual ser noindex?
 *
 * @return bool
 */
function raz_is_noindex() {
	// Default seguro: se a opção global ainda não foi ligada, NÃO indexa.
	$index_site = raz_option( 'indexar', '0' );
	if ( '1' !== (string) $index_site ) {
		return true;
	}

	// noindex por página/post (meta box / ACF).
	if ( is_singular() ) {
		$flag = raz_field( 'raz_noindex', '', get_the_ID() );
		if ( '1' === (string) $flag || true === $flag ) {
			return true;
		}
	}

	// Resultados de busca nunca indexam.
	if ( is_search() ) {
		return true;
	}

	return false;
}

add_filter( 'wp_robots', 'raz_wp_robots' );
/**
 * Ajusta a diretiva de robots conforme a escolha de indexação.
 *
 * @param array $robots
 * @return array
 */
function raz_wp_robots( $robots ) {
	if ( raz_is_noindex() ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
		unset( $robots['max-image-preview'] );
	} else {
		$robots['index']             = true;
		$robots['follow']            = true;
		$robots['max-image-preview'] = 'large';

		// nofollow por página (campo da meta box de SEO).
		if ( is_singular() ) {
			$flag = get_post_meta( get_queried_object_id(), 'raz_nofollow', true );
			if ( '1' === (string) $flag ) {
				$robots['nofollow'] = true;
				unset( $robots['follow'] );
			}
		}
	}
	return $robots;
}
