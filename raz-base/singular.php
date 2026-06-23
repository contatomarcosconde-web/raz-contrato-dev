<?php
/**
 * Fallback para conteúdo singular (posts e CPTs sem template próprio).
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="raz-container">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'raz-single' ); ?>>
			<header class="raz-single__header">
				<h1 class="raz-single__title"><?php the_title(); ?></h1>
			</header>
			<div class="raz-single__content">
				<?php the_content(); ?>
				<?php
				wp_link_pages( array(
					'before' => '<nav class="raz-single__pages">' . esc_html__( 'Páginas:', 'raz' ),
					'after'  => '</nav>',
				) );
				?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</div>
<?php
get_footer();
