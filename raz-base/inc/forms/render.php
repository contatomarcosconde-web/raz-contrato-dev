<?php
/**
 * Formulários — shortcode [raz_form id="X"] e render (contrato §7-bis).
 *
 * Renderiza o HTML do form no idioma atual, com CSS escopado, JS do form e o wrapper
 * que a engine usa para injetar segurança e fazer o envio. Assets só quando há form.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

add_shortcode( 'raz_form', 'raz_form_shortcode' );
/**
 * Shortcode do formulário.
 *
 * @param array $atts
 * @return string
 */
function raz_form_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'raz_form' );
	$id   = absint( $atts['id'] );
	if ( ! $id ) {
		return '';
	}

	$post = get_post( $id );
	if ( ! $post || 'raz_form' !== $post->post_type || 'publish' !== $post->post_status ) {
		return '';
	}

	$html = function_exists( 'raz_lang_field' )
		? raz_lang_field( 'raz_form_html', '', $id )
		: get_post_meta( $id, 'raz_form_html__pt', true );

	if ( function_exists( 'raz_is_empty' ) ? raz_is_empty( $html ) : ( '' === trim( (string) $html ) ) ) {
		return '';
	}

	// Assets (registrados em inc/enqueue.php); carregam só quando o shortcode roda.
	wp_enqueue_style( 'raz-form' );
	wp_enqueue_script( 'raz-form-engine' );

	$out = '';

	$css = raz_form_build_css( $id );
	if ( '' !== $css ) {
		$out .= '<style id="raz-form-css-' . esc_attr( $id ) . '">' . $css . '</style>'; // CSS escopado/sanitizado
	}

	$out .= '<div class="raz-form" id="raz-form-' . esc_attr( $id ) . '" data-form-id="' . esc_attr( $id ) . '">';
	$out .= $html; // HTML do form, já sanitizado no save (ou cru de admin com unfiltered_html)
	$out .= '</div>';

	$js = get_post_meta( $id, 'raz_form_js', true );
	if ( ! ( function_exists( 'raz_is_empty' ) && raz_is_empty( $js ) ) && '' !== trim( (string) $js ) ) {
		$out .= '<script>' . $js . '</script>'; // JS do form (tags <script> removidas no save)
	}

	return $out;
}

/**
 * Monta o CSS escopado do formulário (#raz-form-{id}).
 *
 * @param int $id
 * @return string
 */
function raz_form_build_css( $id ) {
	$css = get_post_meta( $id, 'raz_form_css', true );
	if ( function_exists( 'raz_sanitize_css' ) ) {
		$css = raz_sanitize_css( $css );
	}
	if ( '' === trim( (string) $css ) ) {
		return '';
	}
	return function_exists( 'raz_scope_css' ) ? raz_scope_css( $css, '#raz-form-' . $id ) : $css;
}
