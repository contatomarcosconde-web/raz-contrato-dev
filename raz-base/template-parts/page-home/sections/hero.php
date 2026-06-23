<?php
/**
 * Seção HERO da home — exemplo de referência da anatomia de seção (§4):
 * lê campos → calcula se há conteúdo → return cedo se vazio → renderiza com escaping.
 * Conteúdo localizado (§5-bis) via raz_lang_field(). CSS espelhado: hero.css.
 *
 * Campos editáveis (preencher por meta box / ACF, sufixados por idioma):
 *   raz_hero_titulo__{lang}, raz_hero_sub__{lang}, raz_hero_cta_label__{lang}, raz_hero_cta_url
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

$titulo  = raz_lang_field( 'raz_hero_titulo' );
$sub     = raz_lang_field( 'raz_hero_sub' );
$cta_txt = raz_lang_field( 'raz_hero_cta_label' );
$cta_url = raz_field( 'raz_hero_cta_url' );

// Early-return: seção sem conteúdo simplesmente não aparece (degrade elegante).
if ( raz_is_empty( $titulo ) && raz_is_empty( $sub ) ) {
	return;
}
?>
<section class="raz-hero">
	<div class="raz-container raz-hero__inner">
		<?php if ( ! raz_is_empty( $titulo ) ) : ?>
			<h1 class="raz-hero__title"><?php echo esc_html( $titulo ); ?></h1>
		<?php endif; ?>

		<?php if ( ! raz_is_empty( $sub ) ) : ?>
			<p class="raz-hero__sub"><?php echo esc_html( $sub ); ?></p>
		<?php endif; ?>

		<?php if ( ! raz_is_empty( $cta_txt ) && ! raz_is_empty( $cta_url ) ) : ?>
			<a class="raz-hero__cta raz-btn" href="<?php echo esc_url( $cta_url ); ?>">
				<?php echo esc_html( $cta_txt ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
