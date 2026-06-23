<?php
/**
 * Fallback universal (listagens: blog, arquivos, busca).
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="raz-container">
	<?php if ( is_search() ) : ?>
		<h1 class="raz-archive__title">
			<?php
			/* translators: %s: termo buscado */
			printf( esc_html__( 'Resultados para: %s', 'raz' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
			?>
		</h1>
	<?php elseif ( is_archive() ) : ?>
		<h1 class="raz-archive__title"><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<div class="raz-archive__desc">', '</div>' ); ?>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<div class="raz-archive__list">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'raz-card' ); ?>>
					<h2 class="raz-card__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<div class="raz-card__excerpt"><?php the_excerpt(); ?></div>
				</article>
				<?php
			endwhile;
			?>
		</div>
		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	<?php else : ?>
		<p class="raz-empty"><?php esc_html_e( 'Nenhum conteúdo encontrado.', 'raz' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
