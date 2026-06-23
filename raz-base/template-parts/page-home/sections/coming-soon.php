<?php
/**
 * Seção "Em construção" da home — RAZ Consulting.
 * Estética minimalista da marca: fundo branco, logo serifada, corpo monoespaçado,
 * rodapé com contato. Renderizada em página cheia por front-page.php.
 * Conteúdo editável (por idioma) com fallbacks. CSS espelhado: coming-soon.css.
 *
 * Campos: raz_cs_body__{lang}. Contato via opções (endereco/email/telefone/copyright).
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

$empresa = raz_option( 'empresa', get_bloginfo( 'name' ) );
$empresa = raz_is_empty( $empresa ) ? 'RAZ Consulting' : $empresa;

$body = raz_lang_field( 'raz_cs_body', __( 'A estratégia não precisa ser ruidosa. Ela precisa ser exata. Atuamos nas entrelinhas de grandes negócios e trajetórias públicas, desenhando caminhos que unem marca, mercado e expansão. Nos posicionamos ao lado de quem decide, transformando visão em legado.', 'raz' ) );

// Contato (com fallback aos dados oficiais da RAZ).
$endereco  = raz_option( 'endereco', 'Alameda dos Aicás, 1306 — Moema, SP' );
$email     = raz_option( 'email', 'contato@razconsulting.com.br' );
$telefone  = raz_option( 'telefone', '+55 11 98947-8926' );
$copyright = raz_option( 'copyright' );
if ( raz_is_empty( $copyright ) ) {
	/* translators: 1: ano, 2: empresa */
	$copyright = sprintf( __( '© %1$s %2$s', 'raz' ), gmdate( 'Y' ), $empresa );
}

$logo_url = raz_asset_uri( 'logo.png' );
$has_logo_file = file_exists( RAZ_DIR . '/logo.png' );
?>
<section class="raz-coming">

	<header class="raz-coming__top">
		<a class="raz-coming__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} elseif ( $has_logo_file ) {
				printf( '<img src="%s" alt="%s" />', esc_url( $logo_url ), esc_attr( $empresa ) );
			} else {
				echo '<span class="raz-coming__wordmark">raz</span>';
			}
			?>
		</a>
	</header>

	<div class="raz-coming__main">
		<?php if ( ! raz_is_empty( $body ) ) : ?>
			<p class="raz-coming__body"><?php echo esc_html( $body ); ?></p>
		<?php endif; ?>
	</div>

	<footer class="raz-coming__footer">
		<?php if ( ! raz_is_empty( $endereco ) ) : ?>
			<p class="raz-coming__addr"><?php echo esc_html( $endereco ); ?></p>
		<?php endif; ?>
		<p class="raz-coming__contact">
			<?php if ( ! raz_is_empty( $email ) ) : ?>
				<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
			<?php endif; ?>
			<?php if ( ! raz_is_empty( $telefone ) ) : ?>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $telefone ) ); ?>"><?php echo esc_html( $telefone ); ?></a>
			<?php endif; ?>
		</p>
		<p class="raz-coming__copy"><?php echo esc_html( $copyright ); ?></p>
	</footer>

</section>
