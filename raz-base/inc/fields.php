<?php
/**
 * Camada de acesso a campos (contrato §5).
 *
 * UMA porta de acesso ao conteúdo editável. ACF é OPCIONAL: se existir, lê via ACF;
 * senão, cai para meta nativa; default é apenas rede de segurança, nunca a fonte.
 * Nunca usar get_field()/get_post_meta() crus nos templates — sempre por aqui.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lê um campo do post (ACF → meta nativa → default).
 *
 * @param string   $name    Nome do campo/meta (com prefixo, ex.: 'raz_hero_titulo').
 * @param mixed    $default Valor padrão (rede de segurança).
 * @param int|null $post_id ID do post; usa o atual se null.
 * @return mixed
 */
function raz_field( $name, $default = '', $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	if ( ! $post_id ) {
		return $default;
	}

	if ( function_exists( 'get_field' ) ) {           // ACF é opcional
		$v = get_field( $name, $post_id );
		if ( null !== $v && '' !== $v && array() !== $v ) {
			return $v;
		}
	}

	$v = get_post_meta( $post_id, $name, true );
	return ( '' === $v || null === $v ) ? $default : $v;
}

/**
 * Lê um campo localizado no MESMO post (§5-bis): tenta `{name}__{lang}`,
 * cai para o idioma padrão e por fim para o campo sem sufixo.
 *
 * @param string   $name    Nome base do campo (sem sufixo de idioma).
 * @param mixed    $default Valor padrão.
 * @param int|null $post_id ID do post.
 * @param string   $lang    Idioma forçado; usa o atual se vazio.
 * @return mixed
 */
function raz_lang_field( $name, $default = '', $post_id = null, $lang = '' ) {
	$lang    = $lang ?: raz_current_lang();
	$default_lang = raz_default_lang();

	$v = raz_field( $name . '__' . $lang, '', $post_id );
	if ( ! raz_is_empty( $v ) ) {
		return $v;
	}
	if ( $lang !== $default_lang ) {
		$v = raz_field( $name . '__' . $default_lang, '', $post_id );
		if ( ! raz_is_empty( $v ) ) {
			return $v;
		}
	}
	$v = raz_field( $name, '', $post_id );
	return raz_is_empty( $v ) ? $default : $v;
}

/**
 * Lê uma opção global do tema (Settings API — array salvo em option `raz_options`).
 * Suporta sufixo de idioma para opções localizadas: raz_option('footer_texto', '', true).
 *
 * @param string $key     Chave dentro de raz_options.
 * @param mixed  $default Valor padrão.
 * @param bool   $i18n    Se true, tenta `{key}__{lang}` antes de `{key}`.
 * @return mixed
 */
function raz_option( $key, $default = '', $i18n = false ) {
	$opts = get_option( 'raz_options', array() );
	if ( ! is_array( $opts ) ) {
		$opts = array();
	}

	if ( $i18n ) {
		$lang = raz_current_lang();
		foreach ( array( $key . '__' . $lang, $key . '__' . raz_default_lang(), $key ) as $candidate ) {
			if ( isset( $opts[ $candidate ] ) && ! raz_is_empty( $opts[ $candidate ] ) ) {
				return $opts[ $candidate ];
			}
		}
		return $default;
	}

	return ( isset( $opts[ $key ] ) && ! raz_is_empty( $opts[ $key ] ) ) ? $opts[ $key ] : $default;
}
