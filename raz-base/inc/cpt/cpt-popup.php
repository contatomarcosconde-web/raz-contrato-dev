<?php
/**
 * CPT `raz_popup` — Sistema de Popups (contrato §5/§7).
 *
 * O cliente cria popups com HTML livre por idioma (PT/EN/ES) e define as regras de
 * exibição (gatilho, segmentação por página, frequência e agendamento). Layout sempre
 * "full" (overlay tela cheia); o miolo é o HTML do popup. Conteúdo é DADO editável.
 *
 * Meta keys (prefixo raz_popup_):
 *   conteúdo:   raz_popup_html__{lang}   (um por idioma do catálogo)
 *   gatilho:    raz_popup_trigger (load|scroll|exit), raz_popup_delay (s), raz_popup_scroll (%)
 *   onde:       raz_popup_loc (all|front|specific|posttype), raz_popup_include, raz_popup_exclude,
 *               raz_popup_posttypes (csv)
 *   frequência: raz_popup_freq (every|session|days), raz_popup_freq_days (int)
 *   agenda:     raz_popup_active (1/0), raz_popup_start (Y-m-d), raz_popup_end (Y-m-d)
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'raz_register_popup_cpt' );
/**
 * Registra o CPT de popups.
 */
function raz_register_popup_cpt() {
	register_post_type( 'raz_popup', array(
		'labels'              => array(
			'name'               => __( 'Popups', 'raz' ),
			'singular_name'      => __( 'Popup', 'raz' ),
			'add_new'            => __( 'Adicionar novo', 'raz' ),
			'add_new_item'       => __( 'Adicionar novo popup', 'raz' ),
			'edit_item'          => __( 'Editar popup', 'raz' ),
			'new_item'           => __( 'Novo popup', 'raz' ),
			'view_item'          => __( 'Ver popup', 'raz' ),
			'search_items'       => __( 'Buscar popups', 'raz' ),
			'not_found'          => __( 'Nenhum popup encontrado', 'raz' ),
			'menu_name'          => __( 'Popups', 'raz' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => false, // editor clássico (campos via meta box)
		'menu_icon'           => 'dashicons-welcome-view-site',
		'menu_position'       => 58,
		'supports'            => array( 'title' ),
		'has_archive'         => false,
		'rewrite'             => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
	) );
}

add_action( 'add_meta_boxes', 'raz_popup_meta_boxes' );
/**
 * Registra as meta boxes do popup.
 */
function raz_popup_meta_boxes() {
	add_meta_box( 'raz_popup_content', __( 'Conteúdo do popup (HTML por idioma)', 'raz' ), 'raz_popup_box_content', 'raz_popup', 'normal', 'high' );
	add_meta_box( 'raz_popup_rules', __( 'Regras de exibição', 'raz' ), 'raz_popup_box_rules', 'raz_popup', 'normal', 'default' );
	add_meta_box( 'raz_popup_style', __( 'Aparência & CSS', 'raz' ), 'raz_popup_box_style', 'raz_popup', 'normal', 'default' );
	add_meta_box( 'raz_popup_howto', __( 'Como abrir e fechar este popup', 'raz' ), 'raz_popup_box_howto', 'raz_popup', 'side', 'default' );
}

/**
 * Meta box: aparência (fundo, botão X, largura) + CSS customizado escopado.
 *
 * @param WP_Post $post
 */
function raz_popup_box_style( $post ) {
	$g = function ( $key, $default = '' ) use ( $post ) {
		$v = get_post_meta( $post->ID, $key, true );
		return ( '' === $v || null === $v ) ? $default : $v;
	};

	$bg          = $g( 'raz_popup_bg' );
	$show_close  = $g( 'raz_popup_show_close', '1' );
	$close_color = $g( 'raz_popup_close_color' );
	$close_bg    = $g( 'raz_popup_close_bg' );
	$width       = $g( 'raz_popup_width' );
	$css         = $g( 'raz_popup_css' );

	echo '<table class="form-table" role="presentation"><tbody>';

	printf(
		'<tr><th scope="row">%s</th><td><input type="text" name="raz_popup_bg" value="%s" class="regular-text" placeholder="#ffffff, rgba(0,0,0,.9), var(--raz-primary)" /></td></tr>',
		esc_html__( 'Cor de fundo', 'raz' ),
		esc_attr( $bg )
	);

	printf(
		'<tr><th scope="row">%s</th><td><label><input type="checkbox" name="raz_popup_show_close" value="1" %s /> %s</label></td></tr>',
		esc_html__( 'Botão X (fechar)', 'raz' ),
		checked( '1', $show_close, false ),
		esc_html__( 'Mostrar o botão X no canto', 'raz' )
	);

	printf(
		'<tr><th scope="row">%s</th><td><input type="text" name="raz_popup_close_color" value="%s" class="regular-text" placeholder="#111827" /> <span class="description">%s</span></td></tr>',
		esc_html__( 'Cor do botão X', 'raz' ),
		esc_attr( $close_color ),
		esc_html__( 'cor do ícone', 'raz' )
	);

	printf(
		'<tr><th scope="row">%s</th><td><input type="text" name="raz_popup_close_bg" value="%s" class="regular-text" placeholder="#f9fafb" /> <span class="description">%s</span></td></tr>',
		esc_html__( 'Fundo do botão X', 'raz' ),
		esc_attr( $close_bg ),
		esc_html__( 'fundo do círculo', 'raz' )
	);

	printf(
		'<tr><th scope="row">%s</th><td><input type="text" name="raz_popup_width" value="%s" class="regular-text" placeholder="600px" /> <span class="description">%s</span></td></tr>',
		esc_html__( 'Largura do conteúdo', 'raz' ),
		esc_attr( $width ),
		esc_html__( 'limita a caixa do conteúdo (vazio = livre). O overlay continua tela cheia.', 'raz' )
	);

	echo '</tbody></table>';

	echo '<p><strong>' . esc_html__( 'CSS customizado (aplicado só a este popup)', 'raz' ) . '</strong></p>';
	echo '<div style="background:#f6f7f7;border:1px solid #dcdcde;padding:8px 10px;margin:0 0 6px;font-size:12px;line-height:1.6">';
	echo '<strong>' . esc_html__( 'Classes disponíveis:', 'raz' ) . '</strong><br>';
	echo '<code>.raz-popup__panel</code> — ' . esc_html__( 'a área do popup (tela cheia / fundo).', 'raz' ) . '<br>';
	echo '<code>.raz-popup__content</code> — ' . esc_html__( 'a caixa central do conteúdo.', 'raz' ) . '<br>';
	echo '<code>.raz-popup__close</code> — ' . esc_html__( 'o botão X.', 'raz' ) . '<br>';
	echo esc_html__( 'Suas próprias classes do HTML também funcionam. As regras são escopadas automaticamente a este popup (não vazam para o site).', 'raz' );
	echo '<br><em>' . esc_html__( 'Ex.:', 'raz' ) . '</em> <code>.raz-popup__content{max-width:600px} .meu-btn{background:#1d4ed8;color:#fff;padding:.8rem 1.4rem;border-radius:8px}</code>';
	echo '</div>';
	printf(
		'<textarea id="raz_popup_css" name="raz_popup_css" class="widefat code" rows="8" placeholder="%s">%s</textarea>',
		esc_attr__( '.raz-popup__panel { background: #0b1020; color: #fff; }', 'raz' ),
		esc_textarea( (string) $css )
	);
}

/**
 * Meta box lateral: orientações de abrir/fechar (com o ID deste popup).
 *
 * @param WP_Post $post
 */
function raz_popup_box_howto( $post ) {
	$id   = (int) $post->ID;
	$home = trailingslashit( home_url( '/' ) );

	echo '<p><strong>' . esc_html__( 'ID deste popup:', 'raz' ) . '</strong> <code>' . esc_html( $id ) . '</code></p>';

	echo '<p><strong>' . esc_html__( 'Fechar', 'raz' ) . '</strong><br>';
	echo esc_html__( 'Qualquer elemento com o atributo data-raz-popup-close fecha o popup. O ESC também fecha.', 'raz' ) . '</p>';
	echo '<pre style="white-space:pre-wrap;background:#f6f7f7;border:1px solid #dcdcde;padding:8px">&lt;button data-raz-popup-close&gt;Fechar&lt;/button&gt;</pre>';

	echo '<p><strong>' . esc_html__( 'Abrir por botão/link', 'raz' ) . '</strong><br>';
	echo esc_html__( 'Use data-raz-popup-open com o ID em qualquer botão ou link do site.', 'raz' ) . '</p>';
	echo '<pre style="white-space:pre-wrap;background:#f6f7f7;border:1px solid #dcdcde;padding:8px">&lt;button data-raz-popup-open="' . esc_html( $id ) . '"&gt;Ver oferta&lt;/button&gt;</pre>';

	echo '<p><strong>' . esc_html__( 'Abrir por URL', 'raz' ) . '</strong><br>';
	echo esc_html__( 'Adicione ?raz_popup=ID ao endereço, ou use o hash:', 'raz' ) . '</p>';
	echo '<pre style="white-space:pre-wrap;background:#f6f7f7;border:1px solid #dcdcde;padding:8px">' . esc_html( $home ) . '?raz_popup=' . esc_html( $id ) . "\n" . esc_html( $home ) . '#raz-popup-' . esc_html( $id ) . '</pre>';
	echo '<p class="description">' . esc_html__( 'Para abrir por botão/URL, deixe o popup ativo e visível na página onde o link está (Onde exibir). O gatilho "manual" deixa o popup só nesse modo.', 'raz' ) . '</p>';
}

/**
 * Meta box: conteúdo HTML por idioma (um textarea por idioma do catálogo).
 *
 * @param WP_Post $post
 */
function raz_popup_box_content( $post ) {
	wp_nonce_field( 'raz_popup_save', 'raz_popup_nonce' );
	$langs = function_exists( 'raz_supported_languages' ) ? raz_supported_languages() : array( 'pt' => 'Português' );
	echo '<p class="description">' . esc_html__( 'Cole o HTML do popup para cada idioma. O layout é sempre tela cheia; este HTML é o conteúdo interno.', 'raz' ) . '</p>';
	foreach ( $langs as $slug => $label ) {
		$key = 'raz_popup_html__' . $slug;
		$val = get_post_meta( $post->ID, $key, true );
		printf( '<p><label for="%1$s"><strong>%2$s (%3$s)</strong></label></p>', esc_attr( $key ), esc_html( $label ), esc_html( strtoupper( $slug ) ) );
		printf(
			'<textarea id="%1$s" name="%1$s" class="widefat code" rows="8" placeholder="%3$s">%2$s</textarea>',
			esc_attr( $key ),
			esc_textarea( (string) $val ),
			esc_attr__( '<div class="meu-popup">…</div>', 'raz' )
		);
	}
}

/**
 * Meta box: regras de exibição (gatilho, onde, frequência, agendamento).
 *
 * @param WP_Post $post
 */
function raz_popup_box_rules( $post ) {
	$g = function ( $key, $default = '' ) use ( $post ) {
		$v = get_post_meta( $post->ID, $key, true );
		return ( '' === $v || null === $v ) ? $default : $v;
	};

	$trigger  = $g( 'raz_popup_trigger', 'load' );
	$delay    = $g( 'raz_popup_delay', '3' );
	$scroll   = $g( 'raz_popup_scroll', '50' );
	$loc      = $g( 'raz_popup_loc', 'all' );
	$include  = $g( 'raz_popup_include' );
	$exclude  = $g( 'raz_popup_exclude' );
	$ptypes   = $g( 'raz_popup_posttypes' );
	$freq     = $g( 'raz_popup_freq', 'session' );
	$fdays    = $g( 'raz_popup_freq_days', '7' );
	$active   = $g( 'raz_popup_active', '1' );
	$start    = $g( 'raz_popup_start' );
	$end      = $g( 'raz_popup_end' );

	$row = function ( $label, $html ) {
		printf( '<tr><th scope="row" style="text-align:left;width:200px;vertical-align:top;padding:8px 0;">%s</th><td style="padding:8px 0;">%s</td></tr>', wp_kses_post( $label ), $html );
	};

	echo '<table class="form-table" role="presentation"><tbody>';

	// Ativo
	$row(
		esc_html__( 'Ativo', 'raz' ),
		sprintf( '<label><input type="checkbox" name="raz_popup_active" value="1" %s /> %s</label>', checked( '1', $active, false ), esc_html__( 'Exibir este popup', 'raz' ) )
	);

	// Gatilho
	$trigger_opts = '';
	foreach ( array(
		'load'   => __( 'Ao carregar a página (com atraso)', 'raz' ),
		'scroll' => __( 'Ao rolar X% da página', 'raz' ),
		'exit'   => __( 'Ao tentar sair (exit-intent)', 'raz' ),
		'manual' => __( 'Somente via botão/link/URL (não abre sozinho)', 'raz' ),
	) as $k => $l ) {
		$trigger_opts .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $k, $trigger, false ), esc_html( $l ) );
	}
	$row(
		esc_html__( 'Gatilho', 'raz' ),
		'<select name="raz_popup_trigger">' . $trigger_opts . '</select>'
		. sprintf( '<p style="margin:6px 0 0">%s <input type="number" min="0" step="1" name="raz_popup_delay" value="%s" style="width:80px" /> %s</p>', esc_html__( 'Atraso (load):', 'raz' ), esc_attr( $delay ), esc_html__( 'segundos', 'raz' ) )
		. sprintf( '<p style="margin:6px 0 0">%s <input type="number" min="1" max="100" step="1" name="raz_popup_scroll" value="%s" style="width:80px" /> %%</p>', esc_html__( 'Rolagem (scroll):', 'raz' ), esc_attr( $scroll ) )
	);

	// Onde exibir
	$loc_opts = '';
	foreach ( array(
		'all'      => __( 'Todo o site', 'raz' ),
		'front'    => __( 'Apenas a página inicial', 'raz' ),
		'specific' => __( 'Páginas/posts específicos (lista de IDs)', 'raz' ),
		'posttype' => __( 'Tipos de conteúdo específicos', 'raz' ),
	) as $k => $l ) {
		$loc_opts .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $k, $loc, false ), esc_html( $l ) );
	}
	$row(
		esc_html__( 'Onde exibir', 'raz' ),
		'<select name="raz_popup_loc">' . $loc_opts . '</select>'
		. sprintf( '<p style="margin:6px 0 0">%s <input type="text" name="raz_popup_include" value="%s" class="regular-text" placeholder="12, 34, 56" /></p>', esc_html__( 'IDs incluídos (loc = específicos):', 'raz' ), esc_attr( $include ) )
		. sprintf( '<p style="margin:6px 0 0">%s <input type="text" name="raz_popup_posttypes" value="%s" class="regular-text" placeholder="post, page, product" /></p>', esc_html__( 'Tipos (loc = tipos), separados por vírgula:', 'raz' ), esc_attr( $ptypes ) )
		. sprintf( '<p style="margin:6px 0 0">%s <input type="text" name="raz_popup_exclude" value="%s" class="regular-text" placeholder="78, 90" /></p>', esc_html__( 'IDs excluídos (sempre vale):', 'raz' ), esc_attr( $exclude ) )
	);

	// Frequência
	$freq_opts = '';
	foreach ( array(
		'every'   => __( 'Toda vez', 'raz' ),
		'session' => __( '1x por sessão', 'raz' ),
		'days'    => __( '1x a cada N dias', 'raz' ),
	) as $k => $l ) {
		$freq_opts .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $k, $freq, false ), esc_html( $l ) );
	}
	$row(
		esc_html__( 'Frequência', 'raz' ),
		'<select name="raz_popup_freq">' . $freq_opts . '</select>'
		. sprintf( '<p style="margin:6px 0 0">%s <input type="number" min="1" step="1" name="raz_popup_freq_days" value="%s" style="width:80px" /> %s</p>', esc_html__( 'N (frequência = N dias):', 'raz' ), esc_attr( $fdays ), esc_html__( 'dias', 'raz' ) )
	);

	// Agendamento
	$row(
		esc_html__( 'Agendamento', 'raz' ),
		sprintf( '<label>%s <input type="date" name="raz_popup_start" value="%s" /></label> ', esc_html__( 'Início:', 'raz' ), esc_attr( $start ) )
		. sprintf( '<label>%s <input type="date" name="raz_popup_end" value="%s" /></label>', esc_html__( 'Fim:', 'raz' ), esc_attr( $end ) )
		. sprintf( '<p class="description" style="margin:6px 0 0">%s</p>', esc_html__( 'Deixe em branco para sem limite. As datas usam o fuso do site.', 'raz' ) )
	);

	echo '</tbody></table>';
}

add_action( 'save_post_raz_popup', 'raz_popup_save', 10, 2 );
/**
 * Salva os campos do popup (nonce + capability + sanitização por tipo).
 *
 * @param int     $post_id
 * @param WP_Post $post
 */
function raz_popup_save( $post_id, $post ) {
	if ( ! isset( $_POST['raz_popup_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['raz_popup_nonce'] ), 'raz_popup_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Conteúdo HTML por idioma. Admin (unfiltered_html) salva como veio; demais via raz_kses.
	$langs = function_exists( 'raz_supported_languages' ) ? array_keys( raz_supported_languages() ) : array( 'pt' );
	foreach ( $langs as $slug ) {
		$key = 'raz_popup_html__' . $slug;
		if ( isset( $_POST[ $key ] ) ) {
			$raw  = wp_unslash( $_POST[ $key ] );
			$html = current_user_can( 'unfiltered_html' ) ? $raw : ( function_exists( 'raz_kses' ) ? raz_kses( $raw ) : wp_kses_post( $raw ) );
			update_post_meta( $post_id, $key, $html );
		}
	}

	// Texto/CSV simples
	foreach ( array( 'raz_popup_trigger', 'raz_popup_loc', 'raz_popup_freq', 'raz_popup_include', 'raz_popup_exclude', 'raz_popup_posttypes', 'raz_popup_start', 'raz_popup_end' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	// Inteiros
	foreach ( array( 'raz_popup_delay', 'raz_popup_scroll', 'raz_popup_freq_days' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, absint( $_POST[ $key ] ) );
		}
	}

	// Cores (sanitização permissiva e segura)
	foreach ( array( 'raz_popup_bg', 'raz_popup_close_color', 'raz_popup_close_bg', 'raz_popup_width' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$val = wp_unslash( $_POST[ $key ] );
			$val = ( 'raz_popup_width' === $key ) ? sanitize_text_field( $val ) : ( function_exists( 'raz_sanitize_css_color' ) ? raz_sanitize_css_color( $val ) : sanitize_text_field( $val ) );
			update_post_meta( $post_id, $key, $val );
		}
	}

	// CSS customizado (escopado na renderização)
	if ( isset( $_POST['raz_popup_css'] ) ) {
		$css = wp_unslash( $_POST['raz_popup_css'] );
		update_post_meta( $post_id, 'raz_popup_css', function_exists( 'raz_sanitize_css' ) ? raz_sanitize_css( $css ) : wp_strip_all_tags( $css ) );
	}

	// Checkboxes
	update_post_meta( $post_id, 'raz_popup_active', empty( $_POST['raz_popup_active'] ) ? '0' : '1' );
	update_post_meta( $post_id, 'raz_popup_show_close', empty( $_POST['raz_popup_show_close'] ) ? '0' : '1' );
}
