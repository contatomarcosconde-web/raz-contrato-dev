<?php
/**
 * Formulários — registry de provedores (contrato §7-bis).
 *
 * Cada integração de destino (e-mail, RD Station, ActiveCampaign, Mailchimp, webhook…)
 * é um ADAPTADOR que implementa Raz_Form_Provider e se registra via filtro. O handler
 * não conhece os serviços: percorre os provedores do formulário e chama send().
 * Adicionar um provedor novo = registrar um adaptador, sem tocar no núcleo.
 *
 * v1 traz só o adaptador de e-mail (wp_mail) como base/fallback.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * Contrato de um provedor de destino de formulário.
 */
interface Raz_Form_Provider {
	/** @return string Identificador único (ex.: 'email', 'rdstation'). */
	public function id();

	/** @return string Rótulo exibido no admin. */
	public function label();

	/** @return bool Tem o necessário para enviar (credenciais/config)? */
	public function is_configured( array $cfg );

	/**
	 * Envia o lead ao destino.
	 *
	 * @param array $lead Campos do lead (+ chaves internas com prefixo "_").
	 * @param array $cfg  Config do formulário (destino, assunto, etc.).
	 * @return bool Sucesso.
	 */
	public function send( array $lead, array $cfg );
}

/**
 * Adaptador de E-MAIL (wp_mail) — base/fallback. Sempre disponível.
 */
class Raz_Provider_Email implements Raz_Form_Provider {

	public function id() {
		return 'email';
	}

	public function label() {
		return __( 'E-mail (wp_mail)', 'raz' );
	}

	public function is_configured( array $cfg ) {
		return true; // wp_mail sempre disponível; destino cai para admin_email.
	}

	public function send( array $lead, array $cfg ) {
		$to = ! empty( $cfg['email'] ) ? $cfg['email'] : get_option( 'admin_email' );

		$title   = isset( $lead['_form_title'] ) ? $lead['_form_title'] : '';
		$subject = ! empty( $cfg['subject'] )
			? $cfg['subject']
			/* translators: %s: título do formulário */
			: sprintf( __( 'Novo envio: %s', 'raz' ), $title );

		$lines = array();
		foreach ( $lead as $key => $value ) {
			if ( '' !== $key && '_' === $key[0] ) {
				continue; // pula chaves internas
			}
			$label   = ucfirst( str_replace( array( '_', '-' ), ' ', $key ) );
			$lines[] = $label . ': ' . ( is_array( $value ) ? implode( ', ', $value ) : $value );
		}
		$lines[] = '';
		$lines[] = __( 'Origem', 'raz' ) . ': ' . ( isset( $lead['_origin'] ) ? $lead['_origin'] : '' );

		$headers = array();
		$email   = isset( $lead['email'] ) ? $lead['email'] : ( isset( $lead['e_mail'] ) ? $lead['e_mail'] : '' );
		if ( is_email( $email ) ) {
			$headers[] = 'Reply-To: ' . $email;
		}

		return (bool) wp_mail( $to, $subject, implode( "\n", $lines ), $headers );
	}
}

/**
 * Provedores registrados, keyed por id. Extensível pelo filtro `raz_form_providers`.
 *
 * @return array<string,Raz_Form_Provider>
 */
function raz_form_providers() {
	$providers = array();
	$email     = new Raz_Provider_Email();
	$providers[ $email->id() ] = $email;

	/**
	 * Adicione adaptadores (RD Station, ActiveCampaign, Mailchimp…) aqui.
	 * Ex.: add_filter('raz_form_providers', fn($p) => $p + ['rdstation' => new Meu_RD()]);
	 */
	return apply_filters( 'raz_form_providers', $providers );
}

/**
 * Despacha o lead aos provedores habilitados no formulário.
 *
 * @param array $lead
 * @param array $cfg  Config do formulário (inclui 'providers' => array de ids).
 * @return array<string,bool> Resultado por provedor.
 */
function raz_form_dispatch( array $lead, array $cfg ) {
	$registry = raz_form_providers();
	$results  = array();

	$enabled = ! empty( $cfg['providers'] ) ? (array) $cfg['providers'] : array( 'email' );
	foreach ( $enabled as $pid ) {
		if ( isset( $registry[ $pid ] ) && $registry[ $pid ]->is_configured( $cfg ) ) {
			$results[ $pid ] = (bool) $registry[ $pid ]->send( $lead, $cfg );
		}
	}
	return $results;
}
