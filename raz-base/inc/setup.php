<?php
/**
 * Setup do tema: supports, menus, image sizes, text domain (contrato §4).
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'raz_setup' );
/**
 * Registra os suportes do tema.
 */
function raz_setup() {
	load_theme_textdomain( 'raz', RAZ_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 240, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	register_nav_menus( array(
		'primary' => __( 'Menu principal', 'raz' ),
		'footer'  => __( 'Menu do rodapé', 'raz' ),
	) );

	// Tamanho de imagem responsiva padrão para heros.
	add_image_size( 'raz-hero', 1920, 960, true );
}

add_action( 'init', 'raz_content_width' );
/**
 * Define a largura de conteúdo (compatibilidade com embeds).
 */
function raz_content_width() {
	if ( ! isset( $GLOBALS['content_width'] ) ) {
		$GLOBALS['content_width'] = 1200;
	}
}
