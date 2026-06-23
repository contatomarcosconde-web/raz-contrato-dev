<?php
/**
 * Painel de Opções do tema (contrato §7) — Settings API nativa, sem plugin.
 *
 * Tudo que é config do site é editável aqui: identidade, contato/rodapé,
 * WhatsApp flutuante, scripts (analytics/pixel) e indexação. Salvo em `raz_options`.
 * Camada de leitura: raz_option() (inc/fields.php).
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/**
 * Esquema dos campos do painel: define seções e campos de forma declarativa.
 * type ∈ text | tel | email | url | textarea | code | checkbox
 *
 * @return array
 */
function raz_options_schema() {
	return array(
		'identidade' => array(
			'title'  => __( 'Identidade & Contato', 'raz' ),
			'fields' => array(
				'empresa'   => array( 'label' => __( 'Nome da empresa', 'raz' ), 'type' => 'text' ),
				'telefone'  => array( 'label' => __( 'Telefone', 'raz' ), 'type' => 'tel' ),
				'email'     => array( 'label' => __( 'E-mail de contato', 'raz' ), 'type' => 'email' ),
				'endereco'  => array( 'label' => __( 'Endereço', 'raz' ), 'type' => 'textarea' ),
				'copyright' => array( 'label' => __( 'Texto de copyright', 'raz' ), 'type' => 'text' ),
			),
		),
		'idiomas' => array(
			'title'  => __( 'Idiomas', 'raz' ),
			'fields' => raz_lang_panel_fields(),
		),
		'whatsapp' => array(
			'title'  => __( 'WhatsApp flutuante', 'raz' ),
			'fields' => array(
				'whatsapp_on'      => array( 'label' => __( 'Ativar botão flutuante', 'raz' ), 'type' => 'checkbox' ),
				'whatsapp_numero'  => array( 'label' => __( 'Número (com DDI/DDD, só dígitos)', 'raz' ), 'type' => 'tel' ),
				'whatsapp_mensagem'=> array( 'label' => __( 'Mensagem pré-preenchida', 'raz' ), 'type' => 'text' ),
			),
		),
		'integracoes' => array(
			'title'  => __( 'Integrações & Scripts', 'raz' ),
			'fields' => array(
				'script_head' => array( 'label' => __( 'Scripts no <head> (analytics/pixel)', 'raz' ), 'type' => 'code' ),
				'script_body' => array( 'label' => __( 'Scripts no início do <body>', 'raz' ), 'type' => 'code' ),
				'rd_token'    => array( 'label' => __( 'Token RD Station / CRM (server-side)', 'raz' ), 'type' => 'text' ),
			),
		),
		'seo' => array(
			'title'  => __( 'SEO & Indexação', 'raz' ),
			'fields' => array(
				'indexar'       => array( 'label' => __( 'Permitir indexação deste site (ligar só em produção)', 'raz' ), 'type' => 'checkbox', 'note' => __( 'Desligado: robots bloqueia tudo e o sitemap é desativado (ideal em staging).', 'raz' ) ),
				'robots_rules'  => array( 'label' => __( 'Regras extras do robots.txt', 'raz' ), 'type' => 'code', 'note' => __( 'Uma diretiva por linha (ex.: Disallow: /privado/). Acrescentadas às regras padrão.', 'raz' ) ),
				'sitemap_off'   => array( 'label' => __( 'Desativar o sitemap.xml', 'raz' ), 'type' => 'checkbox', 'note' => __( 'Por padrão o sitemap nativo do WordPress fica ligado em /wp-sitemap.xml.', 'raz' ) ),
				'sitemap_exclude' => array( 'label' => __( 'Tipos de conteúdo a excluir do sitemap', 'raz' ), 'type' => 'text', 'note' => __( 'Separados por vírgula (ex.: post, product). Páginas com noindex já saem automaticamente.', 'raz' ) ),
				'llms_on'       => array( 'label' => __( 'Servir /llms.txt (postura para IA/LLMs)', 'raz' ), 'type' => 'checkbox' ),
				'llms_block'    => array( 'label' => __( 'Declarar bloqueio de IA/LLMs', 'raz' ), 'type' => 'checkbox', 'note' => __( 'Marca o site como "não treinar/rastrear" no llms.txt e bloqueia os robôs de IA no robots.txt.', 'raz' ) ),
				'llms_content'  => array( 'label' => __( 'Conteúdo do /llms.txt', 'raz' ), 'type' => 'code', 'note' => __( 'Vazio = gerado automaticamente (nome do site, descrição, política e sitemap).', 'raz' ) ),
			),
		),
		'seo_social' => array(
			'title'  => __( 'SEO — Redes & Dados estruturados', 'raz' ),
			'fields' => array(
				'og_default_image' => array( 'label' => __( 'Imagem padrão (Open Graph)', 'raz' ), 'type' => 'url', 'note' => __( 'URL da imagem usada no compartilhamento quando a página não tem imagem própria/destacada.', 'raz' ) ),
				'twitter_site'     => array( 'label' => __( 'Usuário do X/Twitter', 'raz' ), 'type' => 'text', 'note' => __( 'Ex.: razconsulting (sem @). Usado no Twitter Card.', 'raz' ) ),
				'schema_org_name'  => array( 'label' => __( 'Nome da organização (Schema)', 'raz' ), 'type' => 'text', 'note' => __( 'Vazio = nome do site. Usado nos dados estruturados (JSON-LD).', 'raz' ) ),
				'schema_org_logo'  => array( 'label' => __( 'Logo da organização (Schema)', 'raz' ), 'type' => 'url', 'note' => __( 'URL do logo para os dados estruturados.', 'raz' ) ),
			),
		),
		'manutencao' => array(
			'title'  => __( 'Manutenção (avançado)', 'raz' ),
			'fields' => array(
				'fs_help'     => array( 'type' => 'html', 'render' => 'raz_fs_help_html' ),
				'fs_enable'   => array(
					'label'    => __( 'Ativar gerenciador de arquivos + API REST', 'raz' ),
					'type'     => 'checkbox',
					'disabled' => ( function_exists( 'raz_fs_available' ) && ! raz_fs_available() ),
					'note'     => ( function_exists( 'raz_fs_available' ) && ! raz_fs_available() )
						? __( 'Indisponível — bloqueado pela hospedagem (veja os motivos acima).', 'raz' )
						: __( 'DESLIGADO por padrão. Editor no admin (Raz → Manutenção) e API REST (Application Password). Respeita DISALLOW_FILE_MODS.', 'raz' ),
				),
				'fs_ip_allow' => array( 'label' => __( 'IPs permitidos (allowlist)', 'raz' ), 'type' => 'text', 'note' => __( 'CSV de IPs (ex.: 203.0.113.4, 198.51.100.7). Vazio = qualquer IP, desde que admin logado/autenticado. Restringe o gerenciador e a API.', 'raz' ) ),
			),
		),
	);
}

/**
 * Campos da seção "Idiomas": interruptor mestre + um checkbox por idioma do catálogo.
 * O idioma padrão aparece marcado e desabilitado (está sempre ativo).
 *
 * @return array
 */
function raz_lang_panel_fields() {
	$fields = array(
		'i18n_on' => array(
			'label' => __( 'Ativar opção de idiomas (multi-idioma e seletor)', 'raz' ),
			'type'  => 'checkbox',
			'note'  => __( 'Desligado: o site fica em um único idioma e o seletor não aparece.', 'raz' ),
		),
	);

	$default = function_exists( 'raz_default_lang' ) ? raz_default_lang() : 'pt';
	$catalogo = function_exists( 'raz_supported_languages' ) ? raz_supported_languages() : array();

	foreach ( $catalogo as $slug => $label ) {
		$is_default = ( $slug === $default );
		$fields[ 'lang_' . $slug ] = array(
			/* translators: 1: rótulo do idioma, 2: sigla */
			'label'    => sprintf( '%1$s (%2$s)', $label, strtoupper( $slug ) ),
			'type'     => 'checkbox',
			'disabled' => $is_default,                 // padrão não pode ser desativado
			'force'    => $is_default,                 // sempre exibido como marcado
			'note'     => $is_default ? __( 'Idioma padrão — sempre ativo.', 'raz' ) : '',
		);
	}

	return $fields;
}

add_action( 'admin_menu', 'raz_options_menu' );
/**
 * Registra a página de opções (menu de topo "Raz").
 */
function raz_options_menu() {
	add_menu_page(
		__( 'Opções do tema', 'raz' ),
		'Raz',
		'manage_options',
		'raz-options',
		'raz_options_render',
		'dashicons-admin-customizer',
		59
	);
}

add_action( 'admin_init', 'raz_options_register' );
/**
 * Registra a setting e sua sanitização.
 */
function raz_options_register() {
	register_setting( 'raz_options_group', 'raz_options', array(
		'type'              => 'array',
		'sanitize_callback' => 'raz_options_sanitize',
		'default'           => array(),
	) );
}

/**
 * Sanitiza a entrada conforme o tipo de cada campo.
 *
 * @param mixed $input
 * @return array
 */
function raz_options_sanitize( $input ) {
	$out    = array();
	$schema = raz_options_schema();
	$input  = is_array( $input ) ? $input : array();

	foreach ( $schema as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			if ( 'html' === $field['type'] ) {
				continue; // bloco informativo, sem valor a salvar
			}
			$raw = isset( $input[ $key ] ) ? $input[ $key ] : '';
			switch ( $field['type'] ) {
				case 'checkbox':
					$out[ $key ] = empty( $raw ) ? '0' : '1';
					break;
				case 'email':
					$out[ $key ] = sanitize_email( $raw );
					break;
				case 'url':
					$out[ $key ] = esc_url_raw( $raw );
					break;
				case 'tel':
					$out[ $key ] = preg_replace( '/[^0-9+\-() ]/', '', (string) $raw );
					break;
				case 'textarea':
					$out[ $key ] = sanitize_textarea_field( $raw );
					break;
				case 'code':
					// Scripts de terceiros: só admins com unfiltered_html podem salvar como veio.
					$out[ $key ] = current_user_can( 'unfiltered_html' ) ? (string) $raw : wp_kses_post( $raw );
					break;
				default:
					$out[ $key ] = sanitize_text_field( $raw );
			}
		}
	}
	return $out;
}

/**
 * Renderiza a página de opções.
 */
function raz_options_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$opts = get_option( 'raz_options', array() );
	$opts = is_array( $opts ) ? $opts : array();
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Opções do tema — Raz', 'raz' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'raz_options_group' ); ?>
			<?php foreach ( raz_options_schema() as $section ) : ?>
				<h2><?php echo esc_html( $section['title'] ); ?></h2>
				<table class="form-table" role="presentation"><tbody>
				<?php foreach ( $section['fields'] as $key => $field ) :
					if ( 'html' === $field['type'] ) {
						echo '<tr><td colspan="2" style="padding-left:0">';
						if ( ! empty( $field['render'] ) && is_callable( $field['render'] ) ) {
							call_user_func( $field['render'] );
						}
						echo '</td></tr>';
						continue;
					}
					$val  = isset( $opts[ $key ] ) ? $opts[ $key ] : '';
					$name = 'raz_options[' . esc_attr( $key ) . ']';
					$id   = 'raz_field_' . esc_attr( $key );
					?>
					<tr>
						<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
						<td>
							<?php if ( 'checkbox' === $field['type'] ) :
								$is_disabled = ! empty( $field['disabled'] );
								$is_checked  = ! empty( $field['force'] ) || '1' === (string) $val;
								?>
								<label>
									<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( true, $is_checked ); ?> <?php disabled( true, $is_disabled ); ?> />
									<?php echo esc_html__( 'Ativo', 'raz' ); ?>
								</label>
							<?php elseif ( 'textarea' === $field['type'] || 'code' === $field['type'] ) : ?>
								<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" class="large-text code" rows="<?php echo 'code' === $field['type'] ? 5 : 3; ?>"><?php echo esc_textarea( (string) $val ); ?></textarea>
							<?php else : ?>
								<input type="<?php echo esc_attr( 'tel' === $field['type'] ? 'text' : $field['type'] ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $val ); ?>" class="regular-text" />
							<?php endif; ?>
							<?php if ( ! empty( $field['note'] ) ) : ?>
								<p class="description"><?php echo esc_html( $field['note'] ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endforeach; ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

add_action( 'wp_head', 'raz_print_head_scripts', 99 );
/**
 * Imprime scripts de head configurados no painel (analytics/pixel).
 */
function raz_print_head_scripts() {
	$code = raz_option( 'script_head' );
	if ( ! raz_is_empty( $code ) ) {
		echo "\n<!-- raz: head scripts -->\n" . $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

add_action( 'wp_body_open', 'raz_print_body_scripts' );
/**
 * Imprime scripts logo após a abertura do <body>.
 */
function raz_print_body_scripts() {
	$code = raz_option( 'script_body' );
	if ( ! raz_is_empty( $code ) ) {
		echo "\n<!-- raz: body scripts -->\n" . $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
