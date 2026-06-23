<?php
/**
 * Dados estruturados (JSON-LD) — contrato §6.
 *
 * Grafo sitewide: Organization + WebSite. Em posts: Article. Em páginas internas:
 * BreadcrumbList. Defaults globais (nome/logo da organização) em Raz → Opções.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'raz_seo_schema', 5 );
/**
 * Imprime o JSON-LD da página atual.
 */
function raz_seo_schema() {
	if ( is_404() ) {
		return;
	}

	$home     = home_url( '/' );
	$org_id   = $home . '#organization';
	$site_id  = $home . '#website';
	$org_name = raz_option( 'schema_org_name', get_bloginfo( 'name' ) );
	$org_logo = raz_option( 'schema_org_logo' );

	$graph = array();

	$org = array(
		'@type' => 'Organization',
		'@id'   => $org_id,
		'name'  => $org_name,
		'url'   => $home,
	);
	if ( ! raz_is_empty( $org_logo ) ) {
		$org['logo'] = array( '@type' => 'ImageObject', 'url' => $org_logo );
	}
	$graph[] = $org;

	$graph[] = array(
		'@type'     => 'WebSite',
		'@id'       => $site_id,
		'url'       => $home,
		'name'      => get_bloginfo( 'name' ),
		'publisher' => array( '@id' => $org_id ),
	);

	if ( is_singular( 'post' ) ) {
		$post  = get_queried_object();
		$image = raz_seo_og_image();
		$article = array(
			'@type'            => 'Article',
			'headline'         => wp_strip_all_tags( get_the_title( $post ) ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'author'           => array( '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', $post->post_author ) ),
			'publisher'        => array( '@id' => $org_id ),
			'mainEntityOfPage' => get_permalink( $post ),
		);
		if ( ! raz_is_empty( $image ) ) {
			$article['image'] = $image;
		}
		$graph[] = $article;
	}

	// BreadcrumbList (em páginas internas).
	if ( function_exists( 'raz_breadcrumb_items' ) ) {
		$crumbs = raz_breadcrumb_items();
		if ( count( $crumbs ) > 1 ) {
			$elements = array();
			$pos      = 1;
			foreach ( $crumbs as $crumb ) {
				$entry = array(
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => $crumb['name'],
				);
				if ( ! empty( $crumb['url'] ) ) {
					$entry['item'] = $crumb['url'];
				}
				$elements[] = $entry;
			}
			$graph[] = array(
				'@type'           => 'BreadcrumbList',
				'@id'             => raz_seo_current_url() . '#breadcrumb',
				'itemListElement' => $elements,
			);
		}
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
