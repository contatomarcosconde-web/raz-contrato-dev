<?php
/**
 * Manutenção — gerenciador/editor de arquivos no admin (sem SSH).
 *
 * SEGURANÇA (decisões registradas no ESTADO):
 * - Apenas administradores (manage_options) ou Application Password (no REST).
 * - DESLIGADO por padrão (opção fs_enable); respeita DISALLOW_FILE_MODS.
 * - Allowlist de IP opcional. Lint (token_get_all/TOKEN_PARSE) antes de salvar PHP →
 *   recusa sintaxe quebrada. Backup .bak + revert. Log em uploads/_pop/maintenance.log.
 * - NÃO é web shell: só edição de arquivos confinada à raiz do site (ABSPATH).
 * Recuperação de "tema morto": Recovery Mode nativo do WP + File Manager do hPanel.
 *
 * @package Raz
 */

defined( 'ABSPATH' ) || exit;

/* ============================ Helpers de segurança ======================= */

/**
 * Bloqueios "duros" da hospedagem que ANULAM a ferramenta (editor + API).
 *
 * @return string[] Motivos (vazio = disponível).
 */
function raz_fs_hard_blockers() {
	$b = array();
	if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
		$b[] = __( 'Edição de arquivos desativada pela hospedagem (DISALLOW_FILE_MODS).', 'raz' );
	}
	if ( ! wp_is_writable( WP_CONTENT_DIR ) && ! wp_is_writable( get_theme_root() ) ) {
		$b[] = __( 'Sistema de arquivos somente leitura (sem permissão de escrita).', 'raz' );
	}
	/** Permite a um host/projeto declarar indisponibilidade. */
	return apply_filters( 'raz_fs_hard_blockers', $b );
}

/**
 * Bloqueios que afetam SÓ a API REST (acesso remoto/agente). O editor no admin
 * continua funcionando.
 *
 * @return string[]
 */
function raz_fs_rest_blockers() {
	$b = array();
	$https = is_ssl() || 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME );
	if ( ! $https ) {
		$b[] = __( 'Site sem HTTPS (necessário para a API e as Senhas de aplicativo).', 'raz' );
	}
	if ( function_exists( 'wp_is_application_passwords_available' ) ) {
		if ( ! wp_is_application_passwords_available() ) {
			$b[] = __( 'Senhas de aplicativo desativadas (necessárias para o acesso remoto).', 'raz' );
		}
	} else {
		$b[] = __( 'Versão do WordPress sem Senhas de aplicativo (requer 5.6+).', 'raz' );
	}
	return apply_filters( 'raz_fs_rest_blockers', $b );
}

/**
 * A ferramenta está disponível neste ambiente (sem bloqueio duro)?
 *
 * @return bool
 */
function raz_fs_available() {
	return array() === raz_fs_hard_blockers();
}

/**
 * O gerenciador de arquivos está habilitado (disponível + ligado no painel)?
 *
 * @return bool
 */
function raz_fs_enabled() {
	if ( ! raz_fs_available() ) {
		return false; // hospedagem bloqueia → ferramenta anulada
	}
	return (bool) raz_option( 'fs_enable' );
}

/**
 * Marca a requisição atual como NÃO-CACHEÁVEL (crítico p/ os endpoints de arquivos).
 * Sem isso, caches de página (LiteSpeed/CDN) podem servir resposta autenticada a terceiros.
 */
function raz_fs_nocache() {
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
	nocache_headers();
	if ( ! headers_sent() ) {
		// Cabeçalho específico que o servidor LiteSpeed respeita para não cachear.
		header( 'X-LiteSpeed-Cache-Control: no-cache, no-store' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private' );
	}
	// API do LiteSpeed Cache (plugin), se presente.
	do_action( 'litespeed_control_set_nocache', 'raz file manager (sensível)' );
}

/**
 * IP do cliente (best-effort).
 *
 * @return string
 */
function raz_fs_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '0.0.0.0';
	return sanitize_text_field( $ip );
}

/**
 * IP atual está na allowlist? (vazio = libera qualquer IP).
 *
 * @return bool
 */
function raz_fs_ip_allowed() {
	$allow = trim( (string) raz_option( 'fs_ip_allow' ) );
	if ( '' === $allow ) {
		return true;
	}
	$ip = raz_fs_client_ip();
	foreach ( explode( ',', $allow ) as $entry ) {
		if ( trim( $entry ) === $ip ) {
			return true;
		}
	}
	return false;
}

/**
 * Raiz permitida (raiz do site).
 *
 * @return string
 */
function raz_fs_root() {
	return wp_normalize_path( realpath( ABSPATH ) );
}

/**
 * Resolve um caminho relativo à raiz, garantindo que fica DENTRO da raiz.
 *
 * @param string $rel
 * @return string|false Caminho absoluto normalizado ou false se inválido/fora da raiz.
 */
function raz_fs_resolve( $rel ) {
	$root = raz_fs_root();
	$rel  = ltrim( str_replace( '\\', '/', (string) $rel ), '/' );
	$full = $root . ( '' !== $rel ? '/' . $rel : '' );
	$real = realpath( $full );
	if ( false === $real ) {
		return false;
	}
	$real = wp_normalize_path( $real );
	if ( $real !== $root && 0 !== strpos( $real, $root . '/' ) ) {
		return false; // path traversal / fora da raiz
	}
	return $real;
}

/**
 * Caminho relativo (a partir da raiz) de um caminho absoluto.
 *
 * @param string $abs
 * @return string
 */
function raz_fs_rel( $abs ) {
	$root = raz_fs_root();
	$abs  = wp_normalize_path( $abs );
	return ltrim( substr( $abs, strlen( $root ) ), '/' );
}

/**
 * Lint de PHP sem depender de shell: token_get_all com TOKEN_PARSE.
 *
 * @param string $code
 * @return string Mensagem de erro, ou '' se ok.
 */
function raz_fs_lint_php( $code ) {
	try {
		token_get_all( $code, TOKEN_PARSE );
		return '';
	} catch ( \Throwable $e ) {
		return $e->getMessage();
	}
}

/**
 * Escreve conteúdo num arquivo com lint (PHP) + backup .bak.
 *
 * @param string $abs     Caminho absoluto (deve existir).
 * @param string $content Novo conteúdo.
 * @return true|WP_Error
 */
function raz_fs_write( $abs, $content ) {
	if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
		return new WP_Error( 'disabled', __( 'Edição desativada (DISALLOW_FILE_MODS).', 'raz' ) );
	}
	if ( ! is_file( $abs ) ) {
		return new WP_Error( 'notfound', __( 'Arquivo não encontrado.', 'raz' ) );
	}
	$ext = strtolower( pathinfo( $abs, PATHINFO_EXTENSION ) );
	if ( 'php' === $ext ) {
		$err = raz_fs_lint_php( $content );
		if ( '' !== $err ) {
			return new WP_Error( 'lint', sprintf( __( 'Sintaxe PHP inválida — NÃO salvo: %s', 'raz' ), $err ) );
		}
	}
	if ( ! is_writable( $abs ) ) {
		return new WP_Error( 'perm', __( 'Sem permissão de escrita neste arquivo.', 'raz' ) );
	}
	@copy( $abs, $abs . '.bak' ); // backup da versão anterior
	$bytes = file_put_contents( $abs, $content );
	if ( false === $bytes ) {
		return new WP_Error( 'write', __( 'Falha ao escrever o arquivo.', 'raz' ) );
	}
	return true;
}

/**
 * Registra uma ação no log de manutenção.
 *
 * @param string $action
 * @param string $target
 */
function raz_fs_log( $action, $target ) {
	$dir = WP_CONTENT_DIR . '/uploads/_pop';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	$user = function_exists( 'wp_get_current_user' ) ? wp_get_current_user()->user_login : 'rest';
	$line = sprintf( "[%s] user=%s ip=%s %s %s\n", current_time( 'mysql' ), $user, raz_fs_client_ip(), $action, $target );
	@file_put_contents( $dir . '/maintenance.log', $line, FILE_APPEND | LOCK_EX );
}

/**
 * Bloco de status + tutorial exibido no painel (seção Manutenção).
 * Mostra "Indisponível / bloqueado pela hospedagem" quando for o caso e, quando
 * disponível, ensina a usar e dá uma mensagem pronta para enviar ao agente.
 */
function raz_fs_help_html() {
	$hard = raz_fs_hard_blockers();
	$rest = raz_fs_rest_blockers();
	$home = trailingslashit( home_url( '/' ) );

	// Bloqueio duro → indisponível.
	if ( $hard ) {
		echo '<div style="border-left:4px solid #d63638;background:#fcf0f1;padding:10px 12px;margin:0 0 8px">';
		echo '<strong style="color:#d63638">' . esc_html__( 'Indisponível — bloqueado pela hospedagem', 'raz' ) . '</strong>';
		echo '<ul style="margin:6px 0 0 18px;list-style:disc">';
		foreach ( $hard as $r ) {
			echo '<li>' . esc_html( $r ) . '</li>';
		}
		echo '</ul></div>';
		echo '<p class="description">' . esc_html__( 'Neste servidor a edição de arquivos não é permitida. Use o Gerenciador de Arquivos do painel da hospedagem (ex.: hPanel) quando precisar.', 'raz' ) . '</p>';
		return;
	}

	echo '<div style="border-left:4px solid #00a32a;background:#edfaef;padding:8px 12px;margin:0 0 8px"><strong style="color:#00a32a">' . esc_html__( 'Disponível neste site', 'raz' ) . '</strong></div>';

	if ( $rest ) {
		echo '<div style="border-left:4px solid #dba617;background:#fcf9e8;padding:8px 12px;margin:0 0 8px"><strong>' . esc_html__( 'Atenção:', 'raz' ) . '</strong> ' . esc_html__( 'o editor no admin funciona, mas o acesso remoto (API/agente) está indisponível:', 'raz' ) . '<ul style="margin:6px 0 0 18px;list-style:disc">';
		foreach ( $rest as $r ) {
			echo '<li>' . esc_html( $r ) . '</li>';
		}
		echo '</ul></div>';
	}

	echo '<div style="background:#f6f7f7;border:1px solid #dcdcde;padding:12px 14px;border-radius:6px;max-width:780px">';
	echo '<h3 style="margin-top:0">' . esc_html__( 'Como usar (editar sem SSH)', 'raz' ) . '</h3>';
	echo '<ol style="margin:0 0 10px 18px">';
	echo '<li>' . esc_html__( 'Marque "Ativar gerenciador de arquivos + API REST" abaixo e salve.', 'raz' ) . '</li>';
	echo '<li>' . wp_kses_post( __( 'Edição manual: abra <em>Raz → Manutenção</em>, navegue e edite os arquivos.', 'raz' ) ) . '</li>';
	echo '<li>' . wp_kses_post( __( 'Acesso remoto (agente/Claude): crie uma <strong>Senha de aplicativo</strong> em <em>Usuários → Perfil → Senhas de aplicativo</em>, dê um nome (ex.: "Claude Manutenção") e copie a senha (aparece só uma vez).', 'raz' ) ) . '</li>';
	echo '<li>' . esc_html__( 'Envie ao agente: a URL do site, seu usuário e essa Senha de aplicativo (modelo abaixo).', 'raz' ) . '</li>';
	echo '</ol>';

	echo '<p style="margin:.6em 0 .3em"><strong>' . esc_html__( 'Mensagem pronta para enviar ao agente:', 'raz' ) . '</strong></p>';
	$msg  = "Conecte no meu site pela API de manutenção do tema Raz (sem SSH).\n";
	$msg .= "Isso te permite LER e EDITAR os arquivos do site via REST, com verificação de sintaxe e backup automático.\n\n";
	$msg .= "Acesso:\n";
	$msg .= "- URL: {$home}\n";
	$msg .= "- Usuário: SEU_LOGIN\n";
	$msg .= "- Application Password: COLE_A_SENHA_AQUI\n\n";
	$msg .= "Como conectar (Basic Auth: usuário + Application Password):\n";
	$msg .= "- Listar: GET {$home}wp-json/raz/v1/fs/list?path=wp-content/themes\n";
	$msg .= "- Ler:    GET {$home}wp-json/raz/v1/fs/read?path=CAMINHO/DO/ARQUIVO\n";
	$msg .= "- Gravar: POST {$home}wp-json/raz/v1/fs/write  (JSON: path, content)\n\n";
	$msg .= "Tarefa: edite o arquivo X e me avise quando terminar.";
	echo '<textarea readonly onclick="this.select()" style="width:100%;height:210px;font-family:Menlo,Consolas,monospace;font-size:12px">' . esc_textarea( $msg ) . '</textarea>';
	echo '<p class="description">' . esc_html__( 'Segurança: revogue a Senha de aplicativo ao terminar (mesma tela). Restrinja por IP no campo abaixo. Desligue o recurso quando não estiver usando.', 'raz' ) . '</p>';
	echo '</div>';
}

/**
 * Purga os caches conhecidos (página/objeto). Útil após editar arquivos em hosts
 * com cache (LiteSpeed/CDN), onde a correção só aparece após limpar o cache.
 *
 * @return string[] Sistemas de cache purgados.
 */
function raz_fs_purge_cache() {
	$done = array();

	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
		$done[] = 'object-cache';
	}
	if ( defined( 'LSCWP_V' ) || has_action( 'litespeed_purge_all' ) ) {
		do_action( 'litespeed_purge_all' );
		$done[] = 'litespeed';
	}
	if ( function_exists( 'rocket_clean_domain' ) ) {
		rocket_clean_domain();
		$done[] = 'wp-rocket';
	}
	if ( function_exists( 'w3tc_flush_all' ) ) {
		w3tc_flush_all();
		$done[] = 'w3-total-cache';
	}
	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache();
		$done[] = 'wp-super-cache';
	}
	if ( has_action( 'cache_enabler_clear_complete_cache' ) ) {
		do_action( 'cache_enabler_clear_complete_cache' );
		$done[] = 'cache-enabler';
	}

	/** Ponto de extensão para purgar caches adicionais (CDN, etc.). */
	do_action( 'raz_fs_purge_cache', $done );

	return $done;
}

/* ================================ Admin UI =============================== */

add_action( 'admin_menu', 'raz_fs_menu', 11 );
/**
 * Submenu "Manutenção" sob o menu Raz.
 */
function raz_fs_menu() {
	add_submenu_page( 'raz-options', __( 'Manutenção', 'raz' ), __( 'Manutenção', 'raz' ), 'manage_options', 'raz-maintenance', 'raz_fs_render' );
}

/**
 * Página principal do gerenciador de arquivos.
 */
function raz_fs_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sem permissão.', 'raz' ) );
	}

	echo '<div class="wrap"><h1>' . esc_html__( 'Manutenção — Arquivos', 'raz' ) . '</h1>';

	if ( ! raz_fs_enabled() ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'O gerenciador está desligado. Ative em Raz → Opções do tema → Manutenção (avançado).', 'raz' ) . '</p></div></div>';
		return;
	}
	if ( ! raz_fs_ip_allowed() ) {
		echo '<div class="notice notice-error"><p>' . sprintf( esc_html__( 'Seu IP (%s) não está na allowlist.', 'raz' ), esc_html( raz_fs_client_ip() ) ) . '</p></div></div>';
		return;
	}

	// Ações (POST).
	if ( ! empty( $_POST['raz_fs_action'] ) ) {
		check_admin_referer( 'raz_fs', 'raz_fs_nonce' );
		$action = sanitize_key( $_POST['raz_fs_action'] );
		$rel    = isset( $_POST['file'] ) ? wp_unslash( $_POST['file'] ) : '';
		$abs    = raz_fs_resolve( $rel );

		if ( 'save' === $action && $abs && is_file( $abs ) ) {
			$content = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : '';
			$res     = raz_fs_write( $abs, $content );
			if ( is_wp_error( $res ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $res->get_error_message() ) . '</p></div>';
			} else {
				raz_fs_log( 'save', raz_fs_rel( $abs ) );
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Salvo. Backup criado em .bak.', 'raz' ) . '</p></div>';
			}
		} elseif ( 'revert' === $action && $abs ) {
			if ( is_file( $abs . '.bak' ) && @copy( $abs . '.bak', $abs ) ) {
				raz_fs_log( 'revert', raz_fs_rel( $abs ) );
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Revertido a partir do .bak.', 'raz' ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Não há .bak para reverter.', 'raz' ) . '</p></div>';
			}
		} elseif ( 'purge' === $action ) {
			$done = raz_fs_purge_cache();
			raz_fs_log( 'purge', implode( ',', $done ) );
			echo '<div class="notice notice-success"><p>' . esc_html( $done
				? sprintf( __( 'Cache purgado: %s', 'raz' ), implode( ', ', $done ) )
				: __( 'Purga solicitada (nenhum cache conhecido detectado).', 'raz' ) ) . '</p></div>';
		}
	}

	// Barra de ações (sempre visível com a ferramenta ligada).
	echo '<form method="post" style="margin:0 0 14px">';
	wp_nonce_field( 'raz_fs', 'raz_fs_nonce' );
	echo '<input type="hidden" name="raz_fs_action" value="purge" />';
	echo '<button type="submit" class="button">' . esc_html__( 'Purgar cache', 'raz' ) . '</button> ';
	echo '<span class="description">' . esc_html__( 'Limpe o cache após editar arquivos para ver as mudanças no site.', 'raz' ) . '</span>';
	echo '</form>';

	$file = isset( $_GET['file'] ) ? wp_unslash( $_GET['file'] ) : '';
	if ( '' !== $file ) {
		raz_fs_render_editor( $file );
	} else {
		$path = isset( $_GET['path'] ) ? wp_unslash( $_GET['path'] ) : '';
		raz_fs_render_browser( $path );
	}

	echo '</div>';
}

/**
 * Lista um diretório.
 *
 * @param string $rel
 */
function raz_fs_render_browser( $rel ) {
	$abs = raz_fs_resolve( $rel );
	if ( ! $abs || ! is_dir( $abs ) ) {
		$abs = raz_fs_root();
	}
	$rel  = raz_fs_rel( $abs );
	$base = admin_url( 'admin.php?page=raz-maintenance' );

	// Breadcrumb.
	echo '<p><strong>' . esc_html__( 'Caminho:', 'raz' ) . '</strong> ';
	printf( '<a href="%s">[raiz]</a>', esc_url( $base ) );
	$acc = '';
	foreach ( array_filter( explode( '/', $rel ) ) as $seg ) {
		$acc .= ( '' !== $acc ? '/' : '' ) . $seg;
		printf( ' / <a href="%s">%s</a>', esc_url( add_query_arg( 'path', $acc, $base ) ), esc_html( $seg ) );
	}
	echo '</p>';

	$entries = @scandir( $abs );
	if ( false === $entries ) {
		echo '<p>' . esc_html__( 'Não foi possível ler o diretório.', 'raz' ) . '</p>';
		return;
	}

	echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Nome', 'raz' ) . '</th><th>' . esc_html__( 'Tipo', 'raz' ) . '</th><th>' . esc_html__( 'Tamanho', 'raz' ) . '</th></tr></thead><tbody>';

	if ( '' !== $rel ) {
		$parent = trim( dirname( $rel ), '.' );
		printf( '<tr><td><a href="%s">⬆ ..</a></td><td>dir</td><td></td></tr>', esc_url( add_query_arg( 'path', $parent, $base ) ) );
	}

	foreach ( $entries as $entry ) {
		if ( '.' === $entry || '..' === $entry ) {
			continue;
		}
		$child_rel = ( '' !== $rel ? $rel . '/' : '' ) . $entry;
		$child_abs = $abs . '/' . $entry;
		if ( is_dir( $child_abs ) ) {
			printf( '<tr><td>📁 <a href="%s">%s</a></td><td>dir</td><td></td></tr>', esc_url( add_query_arg( 'path', $child_rel, $base ) ), esc_html( $entry ) );
		} else {
			printf(
				'<tr><td>📄 <a href="%s">%s</a></td><td>%s</td><td>%s</td></tr>',
				esc_url( add_query_arg( 'file', $child_rel, $base ) ),
				esc_html( $entry ),
				esc_html( strtolower( pathinfo( $entry, PATHINFO_EXTENSION ) ) ),
				esc_html( size_format( (int) @filesize( $child_abs ) ) )
			);
		}
	}
	echo '</tbody></table>';
}

/**
 * Editor de um arquivo.
 *
 * @param string $rel
 */
function raz_fs_render_editor( $rel ) {
	$abs = raz_fs_resolve( $rel );
	$base = admin_url( 'admin.php?page=raz-maintenance' );

	if ( ! $abs || ! is_file( $abs ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Arquivo inválido.', 'raz' ) . '</p></div>';
		return;
	}

	$rel     = raz_fs_rel( $abs );
	$content = (string) file_get_contents( $abs );
	$dir     = trim( dirname( $rel ), '.' );

	printf( '<p><a href="%s">← %s</a></p>', esc_url( add_query_arg( 'path', $dir, $base ) ), esc_html__( 'voltar', 'raz' ) );
	echo '<h2><code>' . esc_html( $rel ) . '</code></h2>';
	if ( ! is_writable( $abs ) ) {
		echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'Atenção: arquivo sem permissão de escrita (somente leitura).', 'raz' ) . '</p></div>';
	}

	echo '<form method="post" action="' . esc_url( add_query_arg( 'file', $rel, $base ) ) . '">';
	wp_nonce_field( 'raz_fs', 'raz_fs_nonce' );
	printf( '<input type="hidden" name="file" value="%s" />', esc_attr( $rel ) );
	printf( '<textarea name="content" spellcheck="false" style="width:100%%;height:60vh;font-family:Menlo,Consolas,monospace;font-size:13px;white-space:pre;overflow:auto;" wrap="off">%s</textarea>', esc_textarea( $content ) );
	echo '<p>';
	echo '<button type="submit" name="raz_fs_action" value="save" class="button button-primary">' . esc_html__( 'Salvar (com lint + backup)', 'raz' ) . '</button> ';
	echo '<button type="submit" name="raz_fs_action" value="revert" class="button" onclick="return confirm(\'' . esc_js( __( 'Reverter para o último .bak?', 'raz' ) ) . '\')">' . esc_html__( 'Reverter (.bak)', 'raz' ) . '</button>';
	echo '</p>';
	echo '<p class="description">' . esc_html__( 'Arquivos .php passam por verificação de sintaxe antes de salvar — código quebrado é recusado. Cada save gera um .bak.', 'raz' ) . '</p>';
	echo '</form>';
}
