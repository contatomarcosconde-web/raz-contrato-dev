<?php
/**
 * Helpers genéricos do tema (escaping, sanitização, utilitários).
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache-busting por mtime do arquivo (sobrevive a hardening que remove ?ver).
 * §8.1 / §9 — usa filemtime quando o arquivo existe no tema.
 *
 * @param string $rel_path Caminho relativo à raiz do tema (ex.: 'assets/css/base/tokens.css').
 * @return string Versão para enqueue.
 */
function raz_asset_ver( $rel_path ) {
	$abs = RAZ_DIR . '/' . ltrim( $rel_path, '/' );
	if ( file_exists( $abs ) ) {
		return (string) filemtime( $abs );
	}
	return RAZ_VERSION;
}

/**
 * URL absoluta de um asset do tema.
 *
 * @param string $rel_path Caminho relativo à raiz do tema.
 * @return string
 */
function raz_asset_uri( $rel_path ) {
	return RAZ_URI . '/' . ltrim( $rel_path, '/' );
}

/**
 * Sanitiza HTML rico permitido (conteúdo do cliente). Base segura, extensível
 * pelo filtro `raz_kses_allowed`. Usada onde o cliente fornece HTML (ex.: popups).
 *
 * @param string $html HTML bruto.
 * @return string HTML sanitizado.
 */
function raz_kses( $html ) {
	$allowed = wp_kses_allowed_html( 'post' );
	// Estende com elementos comuns em blocos de conteúdo customizado.
	$allowed['iframe'] = array(
		'src'             => true,
		'width'           => true,
		'height'          => true,
		'frameborder'     => true,
		'allow'           => true,
		'allowfullscreen' => true,
		'loading'         => true,
		'title'           => true,
		'style'           => true,
	);
	$allowed['svg']  = array( 'xmlns' => true, 'viewbox' => true, 'fill' => true, 'width' => true, 'height' => true, 'class' => true, 'aria-hidden' => true, 'role' => true );
	$allowed['path'] = array( 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true );
	$allowed['g']    = array( 'fill' => true, 'transform' => true );

	/** Permite que o projeto ajuste a allowlist por contexto. */
	$allowed = apply_filters( 'raz_kses_allowed', $allowed, $html );

	return wp_kses( $html, $allowed );
}

/**
 * Sanitiza um valor de cor CSS (hex, rgb/rgba, hsl, var(), nome). Vazio se inválido.
 *
 * @param string $v
 * @return string
 */
function raz_sanitize_css_color( $v ) {
	$v = trim( (string) $v );
	if ( '' === $v ) {
		return '';
	}
	return preg_match( '/^[a-zA-Z0-9#(),.%\s-]+$/', $v ) ? $v : '';
}

/**
 * Sanitiza um bloco de CSS para uso em <style> (remove tags e comentários perigosos).
 *
 * @param string $css
 * @return string
 */
function raz_sanitize_css( $css ) {
	$css = (string) $css;
	// Impede fechar o <style> ou injetar tags.
	$css = str_replace( array( '<', '>' ), '', $css );
	return trim( $css );
}

/**
 * Escopa um bloco de CSS a um seletor raiz, prefixando cada regra (sem vazar global).
 * Trata @media/@supports recursivamente; @keyframes/@font-face ficam intactos.
 *
 * @param string $css   CSS de entrada.
 * @param string $scope Seletor raiz (ex.: '#raz-popup-5').
 * @return string CSS escopado.
 */
function raz_scope_css( $css, $scope ) {
	$css = preg_replace( '!/\*.*?\*/!s', '', (string) $css ); // remove comentários
	$css = trim( (string) $css );
	if ( '' === $css ) {
		return '';
	}

	$out = '';
	$len = strlen( $css );
	$buf = '';
	$i   = 0;

	while ( $i < $len ) {
		$ch = $css[ $i ];

		if ( '{' === $ch ) {
			$selector = trim( $buf );
			$buf      = '';

			// Captura o corpo do bloco respeitando aninhamento.
			$depth = 1;
			$i++;
			$body = '';
			while ( $i < $len && $depth > 0 ) {
				$c = $css[ $i ];
				if ( '{' === $c ) {
					$depth++;
				} elseif ( '}' === $c ) {
					$depth--;
					if ( 0 === $depth ) {
						break;
					}
				}
				$body .= $c;
				$i++;
			}

			if ( '' !== $selector && '@' === $selector[0] ) {
				if ( preg_match( '/^@(media|supports|document)/i', $selector ) ) {
					$out .= $selector . '{' . raz_scope_css( $body, $scope ) . '}';
				} else {
					$out .= $selector . '{' . $body . '}'; // @keyframes/@font-face etc.
				}
			} else {
				$prefixed = array();
				foreach ( explode( ',', $selector ) as $sel ) {
					$sel = trim( $sel );
					if ( '' !== $sel ) {
						$prefixed[] = $scope . ' ' . $sel;
					}
				}
				$out .= implode( ',', $prefixed ) . '{' . trim( $body ) . '}';
			}
			$i++; // pula o '}'
		} else {
			$buf .= $ch;
			$i++;
		}
	}

	return $out;
}

/**
 * Allowlist de HTML para FORMULÁRIOS (mantém form/input/select/textarea/button).
 * Usada para sanitizar HTML de form de usuários sem `unfiltered_html`.
 *
 * @param string $html
 * @return string
 */
function raz_kses_form( $html ) {
	$allowed = wp_kses_allowed_html( 'post' );

	$common = array( 'id' => true, 'class' => true, 'style' => true, 'name' => true, 'data-*' => true, 'aria-*' => true, 'role' => true, 'title' => true, 'hidden' => true );

	$allowed['form']     = array_merge( $common, array( 'action' => true, 'method' => true, 'enctype' => true, 'novalidate' => true, 'target' => true, 'accept-charset' => true ) );
	$allowed['input']    = array_merge( $common, array( 'type' => true, 'value' => true, 'placeholder' => true, 'required' => true, 'checked' => true, 'min' => true, 'max' => true, 'step' => true, 'pattern' => true, 'minlength' => true, 'maxlength' => true, 'autocomplete' => true, 'readonly' => true, 'disabled' => true, 'list' => true, 'accept' => true, 'multiple' => true ) );
	$allowed['textarea'] = array_merge( $common, array( 'rows' => true, 'cols' => true, 'placeholder' => true, 'required' => true, 'maxlength' => true, 'minlength' => true, 'readonly' => true, 'disabled' => true ) );
	$allowed['select']   = array_merge( $common, array( 'required' => true, 'multiple' => true, 'size' => true, 'disabled' => true ) );
	$allowed['option']   = array_merge( $common, array( 'value' => true, 'selected' => true, 'disabled' => true ) );
	$allowed['optgroup'] = array_merge( $common, array( 'label' => true, 'disabled' => true ) );
	$allowed['button']   = array_merge( $common, array( 'type' => true, 'value' => true, 'disabled' => true ) );
	$allowed['label']    = array_merge( $common, array( 'for' => true ) );
	$allowed['fieldset'] = $common;
	$allowed['legend']   = $common;
	$allowed['datalist'] = $common;

	/** Permite ajustar a allowlist de formulários por projeto. */
	$allowed = apply_filters( 'raz_kses_form_allowed', $allowed, $html );

	return wp_kses( $html, $allowed );
}

/**
 * Verdadeiro se uma string/valor está "vazia" para fins de renderização.
 * Trata '', null, array vazio e strings só com espaços.
 *
 * @param mixed $value Valor.
 * @return bool
 */
function raz_is_empty( $value ) {
	if ( is_array( $value ) ) {
		return array() === $value;
	}
	return ( null === $value || '' === trim( (string) $value ) );
}
