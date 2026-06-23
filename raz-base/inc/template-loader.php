<?php
/**
 * Carregamento de templates e seções (contrato §4).
 *
 * Um ponto único para renderizar partes globais e seções de página,
 * mantendo o mapa contexto→template fora dos templates de página.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renderiza uma parte global: template-parts/global/{name}.php.
 *
 * @param string $name Nome (ex.: 'site-header', 'whatsapp-float').
 * @param array  $args Dados passados à parte (disponíveis em $args).
 */
function raz_global_part( $name, $args = array() ) {
	get_template_part( 'template-parts/global/' . $name, null, $args );
}

/**
 * Renderiza uma seção de uma view: template-parts/page-{slug}/sections/{section}.php.
 *
 * @param string $slug    Slug da view (ex.: 'home').
 * @param string $section Slug da seção (ex.: 'hero').
 * @param array  $args    Dados extras para a seção.
 */
function raz_section( $slug, $section, $args = array() ) {
	$rel = 'template-parts/page-' . $slug . '/sections/' . $section;
	if ( locate_template( $rel . '.php' ) ) {
		get_template_part( $rel, null, $args );
	}
}

/**
 * Renderiza, em ordem, todas as seções declaradas para a view atual.
 *
 * @param string $slug Slug da view; usa raz_view_slug() se vazio.
 */
function raz_render_sections( $slug = '' ) {
	$slug = $slug ?: raz_view_slug();
	foreach ( raz_page_sections( $slug ) as $section ) {
		raz_section( $slug, $section );
	}
}
