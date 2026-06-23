<?php
/**
 * Multi-idioma (contrato §5-bis) — OPCIONAL, aqui ATIVO (PT/EN/ES).
 *
 * Estratégia: campos por idioma no MESMO post (ver raz_lang_field em fields.php) +
 * rota por idioma /{lang}/{slug}. Idioma detectado pela URL (nunca por cookie).
 * Seletor sempre reversível. Sem output-buffer reescrevendo links.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * Idiomas SUPORTADOS pelo tema (catálogo): slug => rótulo nativo.
 * O primeiro é o idioma padrão (servido na raiz, sem prefixo).
 * Quais ficam ATIVOS no site é decidido no painel (ver raz_languages()).
 *
 * @return array<string,string>
 */
function raz_supported_languages() {
	/** Permite o projeto ajustar o catálogo (sem mudar a engine). */
	return apply_filters( 'raz_supported_languages', array(
		'pt' => 'Português',
		'en' => 'English',
		'es' => 'Español',
	) );
}

/**
 * Idioma padrão (primeiro do catálogo). Não depende dos ativos (evita recursão).
 *
 * @return string
 */
function raz_default_lang() {
	$langs = array_keys( raz_supported_languages() );
	return $langs ? $langs[0] : 'pt';
}

/**
 * A opção de multi-idioma está ligada? (interruptor mestre do painel.)
 * Enquanto NUNCA foi configurada, assume LIGADA (preserva o multi-idioma do projeto).
 *
 * @return bool
 */
function raz_i18n_enabled() {
	$opts = get_option( 'raz_options', array() );
	if ( ! is_array( $opts ) || ! array_key_exists( 'i18n_on', $opts ) ) {
		return true; // ainda não configurado no painel
	}
	return '1' === (string) $opts['i18n_on'];
}

/**
 * Idiomas ATIVOS no site (o que o seletor mostra), conforme o painel:
 * - se o interruptor mestre estiver desligado → só o idioma padrão (monolíngue);
 * - senão → idioma padrão + cada idioma marcado (lang_{slug}).
 * Enquanto o painel nunca foi salvo, todos os suportados ficam ativos.
 *
 * @return array<string,string>
 */
function raz_languages() {
	$all     = raz_supported_languages();
	$default = raz_default_lang();
	$opts    = get_option( 'raz_options', array() );
	$configurado = is_array( $opts ) && array_key_exists( 'i18n_on', $opts );

	// Multi-idioma desligado: site monolíngue (só o padrão).
	if ( ! raz_i18n_enabled() ) {
		$active = array( $default => isset( $all[ $default ] ) ? $all[ $default ] : strtoupper( $default ) );
		return apply_filters( 'raz_languages', $active );
	}

	$active = array();
	foreach ( $all as $slug => $label ) {
		if ( $slug === $default ) {
			$active[ $slug ] = $label; // padrão sempre ativo
		} elseif ( ! $configurado || raz_option( 'lang_' . $slug ) ) {
			$active[ $slug ] = $label; // nunca configurado = todos; depois, só os marcados
		}
	}

	if ( empty( $active ) ) {
		$active[ $default ] = isset( $all[ $default ] ) ? $all[ $default ] : strtoupper( $default );
	}

	/** Permite o projeto ajustar a lista final de ativos. */
	return apply_filters( 'raz_languages', $active );
}

/**
 * Idioma atual da requisição (detectado da URL).
 *
 * @return string
 */
function raz_current_lang() {
	if ( ! empty( $GLOBALS['raz_lang'] ) && isset( raz_languages()[ $GLOBALS['raz_lang'] ] ) ) {
		return $GLOBALS['raz_lang'];
	}
	return raz_default_lang();
}

/**
 * Caminho-base da instalação (suporta WP em subdiretório).
 *
 * @return string Sempre com barra inicial e final, ex.: '/' ou '/site/'.
 */
function raz_home_path() {
	$path = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	return $path ? trailingslashit( $path ) : '/';
}

/**
 * Detecta o idioma na URL e remove o prefixo de REQUEST_URI para o WP resolver
 * o mesmo conteúdo. Executa cedo (no carregamento do tema), antes do parse_request.
 */
function raz_i18n_boot() {
	if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		return;
	}
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	$uri  = wp_unslash( $_SERVER['REQUEST_URI'] );
	$home = raz_home_path();

	// Trabalha apenas sobre o caminho relativo à instalação.
	if ( 0 !== strpos( $uri, $home ) ) {
		return;
	}
	$rel = substr( $uri, strlen( $home ) ); // ex.: 'en/sobre/?x=1'

	if ( preg_match( '#^([a-z]{2})(/|\?|$)#', $rel, $m ) ) {
		$lang = $m[1];
		if ( $lang !== raz_default_lang() && isset( raz_languages()[ $lang ] ) ) {
			$GLOBALS['raz_lang'] = $lang;
			// Remove só o primeiro segmento de idioma; mantém o resto intacto.
			$new_rel = preg_replace( '#^[a-z]{2}(/|$)#', '', $rel );
			$_SERVER['REQUEST_URI'] = $home . ltrim( $new_rel, '/' );
		}
	}
}
raz_i18n_boot(); // roda no include do tema (antes do parse_request)

/**
 * Versão localizada de uma URL: prefixa o caminho com /{lang}/ (exceto padrão).
 *
 * @param string $lang Idioma alvo.
 * @param string $url  URL base; usa a URL atual (limpa) se vazio.
 * @return string
 */
function raz_localize_url( $lang, $url = '' ) {
	if ( '' === $url ) {
		// REQUEST_URI já vem limpo (sem prefixo de idioma) pelo raz_i18n_boot().
		$base  = wp_parse_url( home_url( '/' ) );
		$sch   = isset( $base['scheme'] ) ? $base['scheme'] : ( is_ssl() ? 'https' : 'http' );
		$host  = isset( $base['host'] ) ? $base['host'] : '';
		$req   = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : raz_home_path();
		$url   = $sch . '://' . $host . $req;
	}
	$parts = wp_parse_url( $url );
	$home  = raz_home_path();
	$path  = isset( $parts['path'] ) ? $parts['path'] : $home;

	// Remove qualquer prefixo de idioma existente para recompor de forma limpa.
	$rel = ( 0 === strpos( $path, $home ) ) ? substr( $path, strlen( $home ) ) : ltrim( $path, '/' );
	$rel = preg_replace( '#^[a-z]{2}(/|$)#', '', $rel );

	$prefix   = ( $lang === raz_default_lang() ) ? '' : $lang . '/';
	$new_path = $home . $prefix . ltrim( $rel, '/' );

	$query = isset( $parts['query'] ) ? '?' . $parts['query'] : '';
	$scheme_host = ( isset( $parts['scheme'], $parts['host'] ) ) ? $parts['scheme'] . '://' . $parts['host'] : '';
	return $scheme_host . $new_path . $query;
}

add_filter( 'language_attributes', 'raz_language_attributes' );
/**
 * Corrige <html lang> conforme o idioma atual.
 *
 * @param string $output Atributos.
 * @return string
 */
function raz_language_attributes( $output ) {
	$lang = raz_current_lang();
	return preg_replace( '/lang="[^"]*"/', 'lang="' . esc_attr( $lang ) . '"', $output ) ?: $output;
}

add_action( 'wp_head', 'raz_hreflang_tags', 1 );
/**
 * Emite tags hreflang + canonical por idioma (§5-bis / §6).
 */
function raz_hreflang_tags() {
	if ( is_404() ) {
		return;
	}
	foreach ( array_keys( raz_languages() ) as $lang ) {
		printf(
			'<link rel="alternate" hreflang="%1$s" href="%2$s" />' . "\n",
			esc_attr( $lang ),
			esc_url( raz_localize_url( $lang ) )
		);
	}
	printf(
		'<link rel="alternate" hreflang="x-default" href="%s" />' . "\n",
		esc_url( raz_localize_url( raz_default_lang() ) )
	);
}

/**
 * Renderiza o seletor de idioma (sempre reversível — links explícitos por idioma).
 *
 * @param array $args ['class' => string]
 */
function raz_lang_switcher( $args = array() ) {
	$langs = raz_languages();
	if ( count( $langs ) < 2 ) {
		return;
	}
	$current = raz_current_lang();
	$class   = isset( $args['class'] ) ? $args['class'] : 'raz-langs';

	echo '<nav class="' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Selecionar idioma', 'raz' ) . '">';
	foreach ( $langs as $slug => $label ) {
		$is_current = ( $slug === $current );
		printf(
			'<a class="%1$s__item%2$s" hreflang="%3$s" lang="%3$s" href="%4$s"%5$s>%6$s</a>',
			esc_attr( $class ),
			$is_current ? ' is-current' : '',
			esc_attr( $slug ),
			esc_url( raz_localize_url( $slug ) ),
			$is_current ? ' aria-current="true"' : '',
			esc_html( strtoupper( $slug ) )
		);
	}
	echo '</nav>';
}
