<?php
/**
 * SEO por página/post — meta box estilo Rank Math, COM SUPORTE A IDIOMAS (contrato §6/§5-bis).
 *
 * Campos de texto por idioma (raz_seo_title__{lang}, raz_seo_desc__{lang}, raz_og_title__{lang},
 * raz_og_desc__{lang}); compartilhados entre idiomas: noindex/nofollow/canonical/og_image.
 * Saída no <head> em inc/seo/head.php (lida via raz_lang_field).
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes', 'raz_seo_add_meta_box' );
/**
 * Registra a meta box de SEO nos tipos públicos (exceto anexos).
 */
function raz_seo_add_meta_box() {
	foreach ( get_post_types( array( 'public' => true ), 'names' ) as $pt ) {
		if ( 'attachment' === $pt ) {
			continue;
		}
		add_meta_box( 'raz_seo_box', __( 'SEO (Raz)', 'raz' ), 'raz_seo_render_meta_box', $pt, 'normal', 'high' );
	}
}

add_action( 'admin_enqueue_scripts', 'raz_seo_meta_assets' );
/**
 * Carrega o seletor de mídia do WP nas telas de edição de post.
 *
 * @param string $hook
 */
function raz_seo_meta_assets( $hook ) {
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		wp_enqueue_media();
	}
}

/**
 * Idiomas a exibir na meta box (ativos no painel; ao menos o padrão).
 *
 * @return array<string,string>
 */
function raz_seo_box_langs() {
	$langs = function_exists( 'raz_languages' ) ? raz_languages() : array( 'pt' => 'Português' );
	return $langs ? $langs : array( 'pt' => 'Português' );
}

/**
 * Renderiza a meta box de SEO.
 *
 * @param WP_Post $post
 */
function raz_seo_render_meta_box( $post ) {
	wp_nonce_field( 'raz_seo_meta_save', 'raz_seo_meta_nonce' );

	$g = function ( $key ) use ( $post ) {
		return (string) get_post_meta( $post->ID, $key, true );
	};

	$langs   = raz_seo_box_langs();
	$multi   = count( $langs ) > 1;
	$default = function_exists( 'raz_default_lang' ) ? raz_default_lang() : 'pt';

	$noindex   = $g( 'raz_noindex' );
	$nofollow  = $g( 'raz_nofollow' );
	$canonical = $g( 'raz_canonical' );
	$og_image  = $g( 'raz_og_image' );

	$fallback_title = wp_strip_all_tags( get_the_title( $post ) );
	$fallback_desc  = wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 );
	$permalink      = get_permalink( $post );

	// Valor de um campo localizado, com fallback ao campo sem sufixo (compatibilidade).
	$lf = function ( $base, $lang ) use ( $g, $default ) {
		$v = $g( $base . '__' . $lang );
		if ( '' === $v && $lang === $default ) {
			$v = $g( $base ); // valor legado sem sufixo de idioma
		}
		return $v;
	};
	?>
	<style>
		.raz-seo-field{margin:0 0 14px}
		.raz-seo-field label{font-weight:600;display:block;margin-bottom:4px}
		.raz-snippet{border:1px solid #dcdcde;border-radius:6px;padding:12px 14px;background:#fff;max-width:640px;margin:0 0 16px}
		.raz-snippet__title{color:#1a0dab;font-size:18px;line-height:1.3}
		.raz-snippet__url{color:#006621;font-size:13px;margin:2px 0}
		.raz-snippet__desc{color:#4d5156;font-size:13px;line-height:1.5}
		.raz-seo-count{color:#888;font-weight:400;font-size:11px}
		.raz-seo-lang{border-left:3px solid #2271b1;padding:2px 0 2px 12px;margin:0 0 16px}
		.raz-seo-lang__h{font-weight:700;text-transform:uppercase;font-size:11px;color:#2271b1;letter-spacing:.04em;margin:0 0 8px}
	</style>

	<div class="raz-snippet">
		<div class="raz-snippet__title" id="raz-snippet-title" data-fallback="<?php echo esc_attr( $fallback_title ); ?>"></div>
		<div class="raz-snippet__url"><?php echo esc_html( $permalink ); ?></div>
		<div class="raz-snippet__desc" id="raz-snippet-desc" data-fallback="<?php echo esc_attr( $fallback_desc ); ?>"></div>
	</div>

	<p><strong><?php esc_html_e( 'Busca (título e descrição)', 'raz' ); ?></strong>
		<?php echo $multi ? '<span class="description"> — ' . esc_html__( 'por idioma', 'raz' ) . '</span>' : ''; ?></p>

	<?php foreach ( $langs as $slug => $label ) : ?>
		<div class="raz-seo-lang">
			<?php if ( $multi ) : ?><div class="raz-seo-lang__h"><?php echo esc_html( $label . ' (' . strtoupper( $slug ) . ')' ); ?></div><?php endif; ?>
			<div class="raz-seo-field">
				<label for="raz_seo_title__<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Título SEO', 'raz' ); ?> <span class="raz-seo-count" data-count-for="raz_seo_title__<?php echo esc_attr( $slug ); ?>" data-max="60"></span></label>
				<input type="text" id="raz_seo_title__<?php echo esc_attr( $slug ); ?>" name="raz_seo_title__<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $lf( 'raz_seo_title', $slug ) ); ?>" class="widefat" placeholder="<?php echo esc_attr( $fallback_title ); ?>" />
			</div>
			<div class="raz-seo-field">
				<label for="raz_seo_desc__<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Meta description', 'raz' ); ?> <span class="raz-seo-count" data-count-for="raz_seo_desc__<?php echo esc_attr( $slug ); ?>" data-max="160"></span></label>
				<textarea id="raz_seo_desc__<?php echo esc_attr( $slug ); ?>" name="raz_seo_desc__<?php echo esc_attr( $slug ); ?>" rows="3" class="widefat"><?php echo esc_textarea( $lf( 'raz_seo_desc', $slug ) ); ?></textarea>
			</div>
		</div>
	<?php endforeach; ?>

	<div class="raz-seo-field">
		<label><?php esc_html_e( 'Robôs de busca', 'raz' ); ?></label>
		<label style="font-weight:400;display:inline-block;margin-right:18px"><input type="checkbox" name="raz_noindex" value="1" <?php checked( '1', $noindex ); ?> /> <?php esc_html_e( 'Não indexar (noindex)', 'raz' ); ?></label>
		<label style="font-weight:400;display:inline-block"><input type="checkbox" name="raz_nofollow" value="1" <?php checked( '1', $nofollow ); ?> /> <?php esc_html_e( 'Não seguir links (nofollow)', 'raz' ); ?></label>
		<p class="description"><?php esc_html_e( 'noindex também remove a página do sitemap. A indexação global do site fica em Raz → Opções → SEO.', 'raz' ); ?></p>
	</div>

	<div class="raz-seo-field">
		<label for="raz_canonical"><?php esc_html_e( 'URL canônica (opcional)', 'raz' ); ?></label>
		<input type="url" id="raz_canonical" name="raz_canonical" value="<?php echo esc_attr( $canonical ); ?>" class="widefat" placeholder="<?php echo esc_attr( $permalink ); ?>" />
	</div>

	<hr />
	<p><strong><?php esc_html_e( 'Redes sociais (Open Graph)', 'raz' ); ?></strong>
		<?php echo $multi ? '<span class="description"> — ' . esc_html__( 'por idioma', 'raz' ) . '</span>' : ''; ?></p>

	<?php foreach ( $langs as $slug => $label ) : ?>
		<div class="raz-seo-lang">
			<?php if ( $multi ) : ?><div class="raz-seo-lang__h"><?php echo esc_html( $label . ' (' . strtoupper( $slug ) . ')' ); ?></div><?php endif; ?>
			<div class="raz-seo-field">
				<label for="raz_og_title__<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Título para redes', 'raz' ); ?></label>
				<input type="text" id="raz_og_title__<?php echo esc_attr( $slug ); ?>" name="raz_og_title__<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $lf( 'raz_og_title', $slug ) ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'usa o Título SEO se vazio', 'raz' ); ?>" />
			</div>
			<div class="raz-seo-field">
				<label for="raz_og_desc__<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Descrição para redes', 'raz' ); ?></label>
				<textarea id="raz_og_desc__<?php echo esc_attr( $slug ); ?>" name="raz_og_desc__<?php echo esc_attr( $slug ); ?>" rows="2" class="widefat" placeholder="<?php esc_attr_e( 'usa a meta description se vazio', 'raz' ); ?>"><?php echo esc_textarea( $lf( 'raz_og_desc', $slug ) ); ?></textarea>
			</div>
		</div>
	<?php endforeach; ?>

	<div class="raz-seo-field">
		<label for="raz_og_image"><?php esc_html_e( 'Imagem para redes (todos os idiomas)', 'raz' ); ?></label>
		<input type="url" id="raz_og_image" name="raz_og_image" value="<?php echo esc_attr( $og_image ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'usa a imagem destacada / padrão se vazio', 'raz' ); ?>" />
		<p style="margin:6px 0">
			<button type="button" class="button" id="raz_og_image_btn"><?php esc_html_e( 'Selecionar imagem', 'raz' ); ?></button>
			<button type="button" class="button-link" id="raz_og_image_clear"><?php esc_html_e( 'remover', 'raz' ); ?></button>
		</p>
		<img id="raz_og_image_prev" src="<?php echo esc_url( $og_image ); ?>" alt="" style="max-width:300px;height:auto;<?php echo '' === $og_image ? 'display:none' : ''; ?>" />
	</div>

	<script>
	( function () {
		var def = <?php echo wp_json_encode( $default ); ?>;
		var t = document.getElementById( 'raz_seo_title__' + def ), d = document.getElementById( 'raz_seo_desc__' + def );
		var pt = document.getElementById( 'raz-snippet-title' ), pd = document.getElementById( 'raz-snippet-desc' );
		function upd() {
			if ( pt ) { pt.textContent = ( t && t.value ) || pt.getAttribute( 'data-fallback' ); }
			if ( pd ) { pd.textContent = ( d && d.value ) || pd.getAttribute( 'data-fallback' ); }
		}
		if ( t ) { t.addEventListener( 'input', upd ); }
		if ( d ) { d.addEventListener( 'input', upd ); }
		upd();

		// Contadores de caracteres.
		document.querySelectorAll( '.raz-seo-count[data-count-for]' ).forEach( function ( c ) {
			var el = document.getElementById( c.getAttribute( 'data-count-for' ) );
			var max = c.getAttribute( 'data-max' );
			if ( ! el ) { return; }
			var f = function () { c.textContent = el.value.length + '/' + max; };
			el.addEventListener( 'input', f ); f();
		} );

		// Seletor de mídia (imagem OG).
		var btn = document.getElementById( 'raz_og_image_btn' );
		var inp = document.getElementById( 'raz_og_image' );
		var img = document.getElementById( 'raz_og_image_prev' );
		var clr = document.getElementById( 'raz_og_image_clear' );
		if ( btn && window.wp && wp.media ) {
			btn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var frame = wp.media( { title: 'Imagem', multiple: false, library: { type: 'image' } } );
				frame.on( 'select', function () {
					var a = frame.state().get( 'selection' ).first().toJSON();
					if ( inp ) { inp.value = a.url; }
					if ( img ) { img.src = a.url; img.style.display = ''; }
				} );
				frame.open();
			} );
		}
		if ( clr ) {
			clr.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				if ( inp ) { inp.value = ''; }
				if ( img ) { img.style.display = 'none'; }
			} );
		}
	}() );
	</script>
	<?php
}

add_action( 'save_post', 'raz_seo_save_meta_box' );
/**
 * Salva os campos de SEO (nonce + capability + sanitização). Campos de texto por idioma.
 *
 * @param int $post_id
 */
function raz_seo_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['raz_seo_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['raz_seo_meta_nonce'] ), 'raz_seo_meta_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$langs = array_keys( raz_seo_box_langs() );
	foreach ( $langs as $slug ) {
		foreach ( array( 'raz_seo_title', 'raz_og_title' ) as $base ) {
			$key = $base . '__' . $slug;
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
			}
		}
		foreach ( array( 'raz_seo_desc', 'raz_og_desc' ) as $base ) {
			$key = $base . '__' . $slug;
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
			}
		}
	}

	foreach ( array( 'raz_canonical', 'raz_og_image' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, esc_url_raw( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	update_post_meta( $post_id, 'raz_noindex', empty( $_POST['raz_noindex'] ) ? '0' : '1' );
	update_post_meta( $post_id, 'raz_nofollow', empty( $_POST['raz_nofollow'] ) ? '0' : '1' );
}
