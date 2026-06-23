<?php
/**
 * Trilha de migalhas (breadcrumbs) — itens + render visual (contrato §6).
 *
 * raz_breadcrumb_items() alimenta tanto o JSON-LD BreadcrumbList (inc/seo/schema.php)
 * quanto o componente visual raz_breadcrumb(). URLs localizadas ao idioma atual.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * Itens da trilha da página atual. Cada item: ['name'=>..., 'url'=>...] (url vazia = atual).
 *
 * @return array<int,array{name:string,url:string}>
 */
function raz_breadcrumb_items() {
	$loc   = function ( $url ) { return function_exists( 'raz_seo_localize' ) ? raz_seo_localize( $url ) : $url; };
	$items = array();
	$items[] = array( 'name' => __( 'Início', 'raz' ), 'url' => $loc( home_url( '/' ) ) );

	if ( is_front_page() ) {
		return array(); // sem migalhas na home
	}

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post && is_post_type_hierarchical( $post->post_type ) ) {
			foreach ( array_reverse( get_post_ancestors( $post ) ) as $aid ) {
				$items[] = array( 'name' => wp_strip_all_tags( get_the_title( $aid ) ), 'url' => $loc( get_permalink( $aid ) ) );
			}
		} elseif ( $post && 'post' === $post->post_type ) {
			$cats = get_the_category( $post->ID );
			if ( $cats ) {
				$items[] = array( 'name' => $cats[0]->name, 'url' => $loc( get_category_link( $cats[0]->term_id ) ) );
			}
		}
		if ( $post ) {
			$items[] = array( 'name' => wp_strip_all_tags( get_the_title( $post ) ), 'url' => '' );
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$items[] = array( 'name' => single_term_title( '', false ), 'url' => '' );
	} elseif ( is_post_type_archive() ) {
		$items[] = array( 'name' => post_type_archive_title( '', false ), 'url' => '' );
	} elseif ( is_search() ) {
		$items[] = array( 'name' => __( 'Resultados da busca', 'raz' ), 'url' => '' );
	} elseif ( is_archive() ) {
		$items[] = array( 'name' => wp_strip_all_tags( get_the_archive_title() ), 'url' => '' );
	} elseif ( is_404() ) {
		$items[] = array( 'name' => __( 'Página não encontrada', 'raz' ), 'url' => '' );
	}

	/** Permite o projeto ajustar a trilha. */
	return apply_filters( 'raz_breadcrumb_items', $items );
}

/**
 * Renderiza a trilha visual (use no template: <?php raz_breadcrumb(); ?>).
 *
 * @param array $args ['class'=>string, 'sep'=>string]
 */
function raz_breadcrumb( $args = array() ) {
	$items = raz_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}
	$class = isset( $args['class'] ) ? $args['class'] : 'raz-breadcrumb';
	$sep   = isset( $args['sep'] ) ? $args['sep'] : '›';

	echo '<nav class="' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Trilha de navegação', 'raz' ) . '"><ol>';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		echo '<li>';
		if ( '' !== $item['url'] && $i !== $last ) {
			printf( '<a href="%s">%s</a>', esc_url( $item['url'] ), esc_html( $item['name'] ) );
		} else {
			printf( '<span aria-current="page">%s</span>', esc_html( $item['name'] ) );
		}
		echo '</li>';
		if ( $i !== $last ) {
			echo '<li class="' . esc_attr( $class ) . '__sep" aria-hidden="true">' . esc_html( $sep ) . '</li>';
		}
	}
	echo '</ol></nav>';
}
