<?php
/**
 * Footer global: contato + menu de rodapé + copyright. Tudo via painel (§7).
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

$empresa   = raz_option( 'empresa', get_bloginfo( 'name' ) );
$telefone  = raz_option( 'telefone' );
$email     = raz_option( 'email' );
$endereco  = raz_option( 'endereco' );
$copyright = raz_option( 'copyright' );
?>
<footer class="raz-footer" role="contentinfo">
	<div class="raz-container raz-footer__inner">

		<div class="raz-footer__brand">
			<span class="raz-footer__name"><?php echo esc_html( $empresa ); ?></span>
			<?php if ( ! raz_is_empty( $endereco ) ) : ?>
				<address class="raz-footer__address"><?php echo nl2br( esc_html( $endereco ) ); ?></address>
			<?php endif; ?>
		</div>

		<div class="raz-footer__contact">
			<?php if ( ! raz_is_empty( $telefone ) ) : ?>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $telefone ) ); ?>"><?php echo esc_html( $telefone ); ?></a>
			<?php endif; ?>
			<?php if ( ! raz_is_empty( $email ) ) : ?>
				<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
			<?php endif; ?>
		</div>

		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="raz-footer__nav" aria-label="<?php esc_attr_e( 'Menu do rodapé', 'raz' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'raz-menu raz-menu--footer',
					'depth'          => 1,
					'fallback_cb'    => false,
				) );
				?>
			</nav>
		<?php endif; ?>

	</div>

	<div class="raz-footer__bar">
		<div class="raz-container">
			<?php
			if ( ! raz_is_empty( $copyright ) ) {
				echo esc_html( $copyright );
			} else {
				printf(
					/* translators: 1: ano, 2: nome do site */
					esc_html__( '© %1$s %2$s. Todos os direitos reservados.', 'raz' ),
					esc_html( gmdate( 'Y' ) ),
					esc_html( $empresa )
				);
			}
			?>
		</div>
	</div>
</footer>
