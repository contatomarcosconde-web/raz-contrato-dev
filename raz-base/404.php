<?php
/**
 * Página 404 — não encontrado.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="raz-container raz-404">
	<h1 class="raz-404__title"><?php esc_html_e( 'Página não encontrada', 'raz' ); ?></h1>
	<p class="raz-404__text"><?php esc_html_e( 'O endereço acessado não existe ou foi movido.', 'raz' ); ?></p>
	<p>
		<a class="raz-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Voltar ao início', 'raz' ); ?>
		</a>
	</p>
	<?php get_search_form(); ?>
</div>
<?php
get_footer();
