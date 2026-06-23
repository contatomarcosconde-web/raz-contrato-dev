<?php
/**
 * Menu mobile (drawer acessível) — OBRIGATÓRIO (contrato §4/§9).
 *
 * Abre/fecha pelo .raz-burger; fecha ao clicar fora, no overlay ou com ESC
 * (lógica em assets/js/global/menu-mobile.js).
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="raz-drawer" id="raz-drawer" hidden>
	<div class="raz-drawer__overlay" data-raz-drawer-close></div>

	<div class="raz-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'raz' ); ?>">
		<button type="button" class="raz-drawer__close" data-raz-drawer-close>
			<span aria-hidden="true">&times;</span>
			<span class="screen-reader-text"><?php esc_html_e( 'Fechar menu', 'raz' ); ?></span>
		</button>

		<nav class="raz-drawer__nav" aria-label="<?php esc_attr_e( 'Menu principal (mobile)', 'raz' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'raz-menu raz-menu--mobile',
					'depth'          => 2,
					'fallback_cb'    => false,
				) );
			}
			?>
		</nav>

		<div class="raz-drawer__langs">
			<?php raz_lang_switcher( array( 'class' => 'raz-langs raz-langs--mobile' ) ); ?>
		</div>
	</div>
</div>
