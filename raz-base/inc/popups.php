<?php
/**
 * Sistema de Popups — seleção server-side e montagem de config (contrato §1/§9).
 *
 * Decide no servidor QUAIS popups entram na página atual (agendamento + segmentação),
 * para enviar ao front só o necessário. A engine JS cuida de gatilho e frequência.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * Converte CSV de IDs em array de inteiros.
 *
 * @param string $csv
 * @return int[]
 */
function raz_popup_csv_ids( $csv ) {
	$out = array();
	foreach ( explode( ',', (string) $csv ) as $piece ) {
		$n = absint( trim( $piece ) );
		if ( $n ) {
			$out[] = $n;
		}
	}
	return $out;
}

/**
 * Converte CSV de slugs em array (ex.: tipos de conteúdo).
 *
 * @param string $csv
 * @return string[]
 */
function raz_popup_csv_slugs( $csv ) {
	$out = array();
	foreach ( explode( ',', (string) $csv ) as $piece ) {
		$s = sanitize_key( trim( $piece ) );
		if ( $s ) {
			$out[] = $s;
		}
	}
	return $out;
}

/**
 * O popup está ativo e dentro da janela de agendamento?
 *
 * @param int $id
 * @return bool
 */
function raz_popup_is_scheduled( $id ) {
	if ( '1' !== (string) get_post_meta( $id, 'raz_popup_active', true ) ) {
		return false;
	}
	$today = current_time( 'Y-m-d' );
	$start = get_post_meta( $id, 'raz_popup_start', true );
	$end   = get_post_meta( $id, 'raz_popup_end', true );

	if ( $start && $today < $start ) {
		return false;
	}
	if ( $end && $today > $end ) {
		return false;
	}
	return true;
}

/**
 * O popup deve aparecer no contexto (página) atual?
 *
 * @param int $id
 * @return bool
 */
function raz_popup_matches_location( $id ) {
	$current_id = (int) get_queried_object_id();

	// Exclusão tem prioridade.
	$exclude = raz_popup_csv_ids( get_post_meta( $id, 'raz_popup_exclude', true ) );
	if ( $current_id && in_array( $current_id, $exclude, true ) ) {
		return false;
	}

	$loc = get_post_meta( $id, 'raz_popup_loc', true ) ?: 'all';

	switch ( $loc ) {
		case 'front':
			return is_front_page();

		case 'specific':
			$include = raz_popup_csv_ids( get_post_meta( $id, 'raz_popup_include', true ) );
			return ( $current_id && in_array( $current_id, $include, true ) );

		case 'posttype':
			$types = raz_popup_csv_slugs( get_post_meta( $id, 'raz_popup_posttypes', true ) );
			$pt    = is_singular() ? get_post_type() : ( is_post_type_archive() ? get_query_var( 'post_type' ) : '' );
			return ( $pt && in_array( $pt, $types, true ) );

		case 'all':
		default:
			return true;
	}
}

/**
 * Monta a config de um popup para o idioma atual. Null se sem conteúdo no idioma.
 *
 * @param int $id
 * @return array|null
 */
function raz_popup_config( $id ) {
	$html = function_exists( 'raz_lang_field' )
		? raz_lang_field( 'raz_popup_html', '', $id )
		: get_post_meta( $id, 'raz_popup_html__pt', true );

	if ( function_exists( 'raz_is_empty' ) ? raz_is_empty( $html ) : ( '' === trim( (string) $html ) ) ) {
		return null; // degrade elegante: sem conteúdo no idioma → não renderiza
	}

	return array(
		'id'         => $id,
		'trigger'    => get_post_meta( $id, 'raz_popup_trigger', true ) ?: 'load',
		'delay'      => absint( get_post_meta( $id, 'raz_popup_delay', true ) ),
		'scroll'     => absint( get_post_meta( $id, 'raz_popup_scroll', true ) ) ?: 50,
		'freq'       => get_post_meta( $id, 'raz_popup_freq', true ) ?: 'session',
		'days'       => absint( get_post_meta( $id, 'raz_popup_freq_days', true ) ) ?: 7,
		'show_close' => ( '0' !== (string) get_post_meta( $id, 'raz_popup_show_close', true ) ),
		'style'      => raz_popup_build_css( $id ),
		'html'       => $html,
	);
}

/**
 * Monta o CSS escopado do popup (aparência + CSS customizado), pronto p/ <style>.
 *
 * @param int $id
 * @return string
 */
function raz_popup_build_css( $id ) {
	$color = function ( $v ) {
		return function_exists( 'raz_sanitize_css_color' ) ? raz_sanitize_css_color( $v ) : preg_replace( '/[^a-zA-Z0-9#(),.%\s-]/', '', (string) $v );
	};

	$bg          = $color( get_post_meta( $id, 'raz_popup_bg', true ) );
	$close_color = $color( get_post_meta( $id, 'raz_popup_close_color', true ) );
	$close_bg    = $color( get_post_meta( $id, 'raz_popup_close_bg', true ) );
	$width       = $color( get_post_meta( $id, 'raz_popup_width', true ) );

	$rules = '';
	if ( $bg ) {
		$rules .= '.raz-popup__panel{background:' . $bg . ';}';
	}
	if ( $close_color ) {
		$rules .= '.raz-popup__close{color:' . $close_color . ';}';
	}
	if ( $close_bg ) {
		$rules .= '.raz-popup__close{background:' . $close_bg . ';}';
	}
	if ( $width ) {
		$rules .= '.raz-popup__content{max-width:' . $width . ';margin-inline:auto;}';
	}

	$custom = get_post_meta( $id, 'raz_popup_css', true );
	if ( function_exists( 'raz_sanitize_css' ) ) {
		$custom = raz_sanitize_css( $custom );
	}

	$all = $rules . $custom;
	if ( '' === trim( $all ) ) {
		return '';
	}

	return function_exists( 'raz_scope_css' ) ? raz_scope_css( $all, '#raz-popup-' . $id ) : $all;
}

/**
 * Lista os popups ativos para a página atual (config pronta). Resultado memoizado.
 *
 * @return array[] Lista de configs (ver raz_popup_config).
 */
function raz_get_active_popups() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = array();

	if ( is_admin() ) {
		return $cache;
	}

	$ids = get_posts( array(
		'post_type'        => 'raz_popup',
		'post_status'      => 'publish',
		'numberposts'      => 20,
		'orderby'          => 'menu_order date',
		'order'            => 'ASC',
		'fields'           => 'ids',
		'suppress_filters' => false,
		'no_found_rows'    => true,
	) );

	foreach ( $ids as $id ) {
		if ( ! raz_popup_is_scheduled( $id ) ) {
			continue;
		}
		if ( ! raz_popup_matches_location( $id ) ) {
			continue;
		}
		$cfg = raz_popup_config( $id );
		if ( $cfg ) {
			$cache[] = $cfg;
		}
	}

	return $cache;
}

/**
 * Há popups ativos na página atual?
 *
 * @return bool
 */
function raz_has_active_popups() {
	return ! empty( raz_get_active_popups() );
}
