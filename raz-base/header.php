<?php
/**
 * Cabeçalho do documento + header do site.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="raz-skip-link screen-reader-text" href="#raz-content"><?php esc_html_e( 'Pular para o conteúdo', 'raz' ); ?></a>

<?php raz_global_part( 'site-header' ); ?>

<main id="raz-content" class="raz-content" role="main">
