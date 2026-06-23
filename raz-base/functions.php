<?php
/**
 * Raz Base — bootstrap do tema.
 *
 * Responsabilidade ÚNICA: definir constantes e carregar a camada inc/ via glob().
 * Toda lógica vive em inc/* (contrato §4). Não colocar regra de negócio aqui.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Constantes do tema (uma fonte de verdade)
 * ---------------------------------------------------------------------- */
define( 'RAZ_VERSION', '1.0.0' );          // SemVer — espelha style.css
define( 'RAZ_PREFIX', 'raz' );             // prefixo único de campos/handles
define( 'RAZ_DIR', get_template_directory() );
define( 'RAZ_URI', get_template_directory_uri() );

/* -------------------------------------------------------------------------
 * Loader por glob() — carrega inc/ recursivamente, de forma determinística.
 * Os arquivos só registram hooks; a ordem de inclusão não cria dependência
 * de execução. Subpastas conhecidas são carregadas após o nível raiz.
 * ---------------------------------------------------------------------- */
if ( ! function_exists( 'raz_require_dir' ) ) {
	/**
	 * Inclui todos os .php de um diretório (ordenado), recursando em subpastas.
	 *
	 * @param string $dir Caminho absoluto.
	 */
	function raz_require_dir( $dir ) {
		foreach ( glob( trailingslashit( $dir ) . '*.php' ) ?: array() as $file ) {
			require_once $file;
		}
		foreach ( glob( trailingslashit( $dir ) . '*', GLOB_ONLYDIR ) ?: array() as $subdir ) {
			raz_require_dir( $subdir );
		}
	}
}

raz_require_dir( RAZ_DIR . '/inc' );
