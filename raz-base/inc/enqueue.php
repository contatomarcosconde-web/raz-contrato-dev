<?php
/**
 * Enqueue condicional de assets (contrato §4 / §9).
 *
 * Cada página carrega só os seus assets. Seção = CSS/JS espelhado, enfileirado
 * apenas quando a seção entra na view. Cache-busting por filemtime.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enfileira um CSS do tema apenas se o arquivo existir (evita 404).
 *
 * @param string   $handle Handle (sem prefixo).
 * @param string   $rel    Caminho relativo à raiz do tema.
 * @param string[] $deps   Dependências.
 */
function raz_enqueue_style( $handle, $rel, $deps = array() ) {
	if ( file_exists( RAZ_DIR . '/' . $rel ) ) {
		wp_enqueue_style( 'raz-' . $handle, raz_asset_uri( $rel ), $deps, raz_asset_ver( $rel ) );
	}
}

/**
 * Enfileira um JS do tema (no footer) apenas se o arquivo existir.
 *
 * @param string   $handle Handle (sem prefixo).
 * @param string   $rel    Caminho relativo à raiz do tema.
 * @param string[] $deps   Dependências.
 */
function raz_enqueue_script( $handle, $rel, $deps = array() ) {
	if ( file_exists( RAZ_DIR . '/' . $rel ) ) {
		wp_enqueue_script( 'raz-' . $handle, raz_asset_uri( $rel ), $deps, raz_asset_ver( $rel ), true );
	}
}

add_action( 'wp_enqueue_scripts', 'raz_register_form_assets', 5 );
/**
 * Registra (sem enfileirar) os assets de formulário. O shortcode [raz_form]
 * enfileira sob demanda. razForm leva a URL REST e textos de status.
 */
function raz_register_form_assets() {
	$js = 'assets/js/global/form-engine.js';
	if ( file_exists( RAZ_DIR . '/' . $js ) ) {
		wp_register_script( 'raz-form-engine', raz_asset_uri( $js ), array(), raz_asset_ver( $js ), true );
		wp_localize_script( 'raz-form-engine', 'razForm', array(
			'rest' => esc_url_raw( rest_url( 'raz/v1/' ) ),
			'i18n' => array(
				'sending' => __( 'Enviando…', 'raz' ),
				'success' => __( 'Mensagem enviada com sucesso!', 'raz' ),
				'error'   => __( 'Não foi possível enviar agora.', 'raz' ),
				'network' => __( 'Erro de conexão. Tente novamente.', 'raz' ),
			),
		) );
	}
	$css = 'assets/css/global/form.css';
	if ( file_exists( RAZ_DIR . '/' . $css ) ) {
		wp_register_style( 'raz-form', raz_asset_uri( $css ), array(), raz_asset_ver( $css ) );
	}
}

add_action( 'wp_enqueue_scripts', 'raz_enqueue_assets' );
/**
 * Orquestra o enqueue conforme o contexto centralizado (inc/context.php).
 */
function raz_enqueue_assets() {
	// 0) Tipografia da marca: Playfair Display (logo/títulos). Corpo usa monoespaçada do sistema.
	wp_enqueue_style( 'raz-fonts', 'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap', array(), null );

	// 1) Base global: tokens + reset + base. Tudo herda os tokens.
	raz_enqueue_style( 'tokens', 'assets/css/base/tokens.css' );
	raz_enqueue_style( 'reset', 'assets/css/base/reset.css', array( 'raz-tokens' ) );
	raz_enqueue_style( 'base', 'assets/css/base/base.css', array( 'raz-tokens', 'raz-reset' ) );

	// 2) Componentes globais (header/footer/menu) presentes em toda página.
	raz_enqueue_style( 'header', 'assets/css/global/header.css', array( 'raz-base' ) );
	raz_enqueue_style( 'footer', 'assets/css/global/footer.css', array( 'raz-base' ) );
	raz_enqueue_style( 'menu-mobile', 'assets/css/global/menu-mobile.css', array( 'raz-base' ) );
	raz_enqueue_script( 'menu-mobile', 'assets/js/global/menu-mobile.js' );

	// WhatsApp flutuante só quando ligado no painel.
	if ( raz_option( 'whatsapp_on' ) ) {
		raz_enqueue_style( 'whatsapp-float', 'assets/css/global/whatsapp-float.css', array( 'raz-base' ) );
	}

	// Sistema de Popups: assets só quando há popup ativo nesta página.
	if ( function_exists( 'raz_has_active_popups' ) && raz_has_active_popups() ) {
		raz_enqueue_style( 'popup', 'assets/css/global/popup.css', array( 'raz-base' ) );
		raz_enqueue_script( 'popup-engine', 'assets/js/global/popup-engine.js' );
	}

	// 3) Assets por view + por seção (espelhados, só os que existem).
	$slug = raz_view_slug();
	raz_enqueue_style( 'page-' . $slug, 'assets/css/page-' . $slug . '/page.css', array( 'raz-base' ) );

	foreach ( raz_page_sections( $slug ) as $section ) {
		$css = 'assets/css/page-' . $slug . '/sections/' . $section . '.css';
		$js  = 'assets/js/page-' . $slug . '/sections/' . $section . '.js';
		raz_enqueue_style( 'sec-' . $slug . '-' . $section, $css, array( 'raz-base' ) );
		raz_enqueue_script( 'sec-' . $slug . '-' . $section, $js );
	}
}
