<?php
/**
 * Rodapé do site + fechamento do documento.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;
?>
</main><!-- #raz-content -->

<?php
raz_global_part( 'site-footer' );

if ( raz_option( 'whatsapp_on' ) && ! raz_is_empty( raz_option( 'whatsapp_numero' ) ) ) {
	raz_global_part( 'whatsapp-float' );
}

// Sistema de Popups (renderiza só os ativos para esta página; vazio = nada).
if ( function_exists( 'raz_has_active_popups' ) && raz_has_active_popups() ) {
	raz_global_part( 'popups' );
}

wp_footer();
?>
</body>
</html>
