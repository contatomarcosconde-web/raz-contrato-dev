<?php
/**
 * Header global do site: logo + menu principal + seletor de idioma + toggle mobile.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="raz-header" role="banner">
	<div class="raz-container raz-header__inner">

		<div class="raz-header__brand">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				$empresa = raz_option( 'empresa', get_bloginfo( 'name' ) );
				printf(
					'<a class="raz-header__title" href="%1$s" rel="home">%2$s</a>',
					esc_url( home_url( '/' ) ),
					esc_html( $empresa )
				);
			}
			?>
		</div>

		<nav class="raz-header__nav" aria-label="<?php esc_attr_e( 'Menu principal', 'raz' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'raz-menu',
					'depth'          => 2,
					'fallback_cb'    => false,
				) );
			}
			?>
		</nav>

		<div class="raz-header__aux">
			<?php raz_lang_switcher( array( 'class' => 'raz-langs' ) ); ?>

			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<button type="button" class="raz-burger" aria-controls="raz-drawer" aria-expanded="false">
					<span class="raz-burger__box" aria-hidden="true"><span class="raz-burger__line"></span></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Abrir menu', 'raz' ); ?></span>
				</button>
			<?php endif; ?>
		</div>

	</div>

	<?php
	if ( has_nav_menu( 'primary' ) ) {
		raz_global_part( 'menu-mobile' );
	}
	?>
</header>
