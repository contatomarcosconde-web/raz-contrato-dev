<?php
/**
 * Template de página: página = lista de seções (contrato §4).
 *
 * Se a view tem seções declaradas (inc/context.php → raz_page_sections),
 * renderiza-as em ordem. Caso contrário, cai para o conteúdo editado no editor.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

get_header();

$slug     = raz_view_slug();
$sections = raz_page_sections( $slug );

if ( ! empty( $sections ) ) {
	raz_render_sections( $slug );
} else {
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'raz-page' ); ?>>
			<div class="raz-container">
				<h1 class="raz-page__title"><?php the_title(); ?></h1>
				<div class="raz-page__content"><?php the_content(); ?></div>
			</div>
		</article>
		<?php
	endwhile;
}

get_footer();
