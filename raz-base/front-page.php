<?php
/**
 * Página inicial.
 *
 * Enquanto a home for apenas a seção "coming-soon", renderiza em PÁGINA CHEIA
 * (sem header/footer globais) para a estética minimalista da marca. Quando o site
 * definitivo ganhar seções reais, cai no fluxo normal (header → seções → footer).
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

$raz_slug     = raz_view_slug();
$raz_sections = raz_page_sections( $raz_slug );

if ( array( 'coming-soon' ) === $raz_sections ) :
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'raz-front raz-front--coming' ); ?>>
	<?php
	wp_body_open();
	raz_section( $raz_slug, 'coming-soon' );
	wp_footer();
	?>
</body>
</html>
	<?php
else :
	get_header();
	raz_render_sections( $raz_slug );
	get_footer();
endif;
