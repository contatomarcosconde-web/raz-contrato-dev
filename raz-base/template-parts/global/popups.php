<?php
/**
 * Render dos popups ativos na página (Sistema de Popups).
 * Layout sempre full (tela cheia). O conteúdo interno é o HTML do popup (por idioma).
 * Gatilho e frequência são tratados por assets/js/global/popup-engine.js (lê os data-*).
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

$raz_popups = raz_get_active_popups();
if ( empty( $raz_popups ) ) {
	return; // nada a exibir nesta página
}

foreach ( $raz_popups as $p ) :
	if ( ! empty( $p['style'] ) ) {
		// CSS já sanitizado e escopado a #raz-popup-{id} (não vaza global).
		echo '<style id="raz-popup-css-' . esc_attr( $p['id'] ) . '">' . $p['style'] . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	?>
	<div class="raz-popup" id="raz-popup-<?php echo esc_attr( $p['id'] ); ?>"
		role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Aviso', 'raz' ); ?>" hidden
		data-popup-id="<?php echo esc_attr( $p['id'] ); ?>"
		data-trigger="<?php echo esc_attr( $p['trigger'] ); ?>"
		data-delay="<?php echo esc_attr( $p['delay'] ); ?>"
		data-scroll="<?php echo esc_attr( $p['scroll'] ); ?>"
		data-freq="<?php echo esc_attr( $p['freq'] ); ?>"
		data-days="<?php echo esc_attr( $p['days'] ); ?>">
		<div class="raz-popup__panel">
			<?php if ( ! empty( $p['show_close'] ) ) : ?>
				<button type="button" class="raz-popup__close" data-raz-popup-close aria-label="<?php esc_attr_e( 'Fechar', 'raz' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			<?php endif; ?>
			<div class="raz-popup__content">
				<?php
				// Conteúdo já sanitizado no save (raz_kses) ou definido por admin com unfiltered_html.
				echo $p['html']; // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</div>
		</div>
	</div>
	<?php
endforeach;
