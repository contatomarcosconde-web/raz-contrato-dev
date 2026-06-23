# Raz Base — Contrato & Guia de Desenvolvimento (tema WordPress)

> **Tema-base modular da Raz Consulting, feito para evoluir com IA.**
> Instale, ative e **diga ao agente: "leia o README do tema (raz-base) para começar"**.
> Este documento é o **contrato operacional**: define o que o tema oferece, as regras inegociáveis
> e o processo a seguir. Não é um relatório de status — é o **como** desenvolver daqui pra frente.

---

## ▶ Início (para a IA / dev) — faça nesta ordem

> **Chegou pelo repositório `raz-contrato-dev`?** Leia antes o contrato na **raiz do repo** (`README.md`).
> **Site novo:** copie esta pasta (`raz-base/`) para `wp-content/themes/`, ative o tema e siga abaixo.

1. **Leia este README por completo** (capacidades, regras e receitas abaixo).
2. **Leia o Diretório de Estado** do projeto: `wp-content/uploads/_pop/ESTADO.md` (arquitetura, o que existe,
   decisões) e a pasta `sessoes/` (quem está mexendo em quê). Se não existir, **crie e inicialize**. Sempre pergunte o nome do desenvolvedor que esta evoluindo o projeto para dar esse nome a seção, é obrigatorio em qualquer evolução mudar a seção para editando, validando deve ser considerado como editando, conluido deve ser considerado que ninguem editando, mas sempre alerte, Fulando estava editando, posso continuar ou deseja validar com ele, isso evita erros de sobreesecrever um o projeto do outro!
3. **Abra uma sessão** em `_pop/sessoes/` (lock leve) dizendo a área/arquivos que vai tocar.
4. **Preencha o intake** com o cliente (objetivo, páginas, conteúdo, idiomas, indexação, integrações) e
   **emita um Plano de Execução** antes de codar.
5. Trabalhe seguindo as **convenções (§4)** e as **receitas (§5)**. **Versione o `ESTADO.md` a cada
   aprovação** e **feche a sessão**.

> **Resumo:** ler sempre antes · documentar durante · versionar a cada aprovação. Mesma filosofia do `_pop`.

---

## Metas inegociáveis (toda entrega)

1. **Modular** — isolamento por página e por seção (PHP e CSS espelhados).
2. **Responsivo** (mobile-first) e **acessível**; **menu mobile** quando há menu.
3. **SEO e performance** desde o início; **controle de indexação** explícito.
4. **Edição total pelo cliente** — todo conteúdo é **editável no admin** (meta box ou ACF), nunca preso no código.
5. **Não quebrar o existente** — conviver (strangler) em sites de terceiros.
6. **Uma fonte de verdade** — sem duplicar arquivos, lógica, cores ou strings.
7. **Estado documentado e versionado** — `_pop/ESTADO.md` é a verdade compartilhada.

| Selo                     | Significado                          |
| ------------------------ | ------------------------------------ |
| 🔴**PROIBIDO**     | Nunca fazer. Reprova a entrega.      |
| 🟢**OBRIGATÓRIO** | Sempre fazer. Faz parte do "pronto". |
| 🔵**RECOMENDADO**  | Boa prática esperada.               |

---

## 1. O que o tema JÁ oferece (blocos prontos para compor)

Não reinvente — **componha** com o que existe:

- **Arquitetura modular** — página = lista de seções; loader `glob()`; detecção de contexto central;
  enqueue **condicional** com cache-busting por `filemtime`.
- **Camada de campos** (`inc/fields.php`) — `raz_field()`, `raz_lang_field()`, `raz_option()`:
  ACF se existir → meta nativa → default. **Nunca** ler `get_post_meta`/`get_field` cru no template.
- **Multi-idioma PT/EN/ES** (`inc/i18n/`) — rota `/{lang}/`, `hreflang`, seletor reversível, campos por
  idioma no mesmo post (`{campo}__{lang}`). Liga/desliga e idiomas ativos no painel.
- **Painel de Opções** (`Raz` no admin) — identidade, **WhatsApp flutuante**, scripts (analytics/pixel),
  SEO/indexação, idiomas, manutenção. Renderizador por esquema (tipos: text/tel/email/url/textarea/code/checkbox/html).
- **Sistema de Popups** (CPT `raz_popup`) — HTML por idioma, regras (gatilho/segmentação/frequência/agenda),
  aparência + **CSS escopado**, abrir por `data-raz-popup-open`/URL, fechar por `data-raz-popup-close`/ESC.
- **Sistema de Formulários** (CPT `raz_form`) — template livre + **shortcode** `[raz_form id="X"]`;
  handler REST único (nonce fresco anti-cache, honeypot, time-trap, rate-limit, LGPD); **registry de provedores**
  (`Raz_Form_Provider`) — v1 e-mail; RD/ActiveCampaign/Mailchimp entram via filtro `raz_form_providers`.
- **SEO completo** — meta box por página (título/descrição/OG **por idioma**, noindex/nofollow, canonical),
  `<head>` (title/description/canonical/OG/Twitter), **JSON-LD** (Organization/WebSite/Article/BreadcrumbList),
  `robots.txt`/`sitemap`/`llms.txt` gerenciáveis, bloqueio de IA opcional. Breadcrumb: `raz_breadcrumb()`.
- **Manutenção sem SSH** (`Raz → Manutenção`, desligado por padrão) — editor de arquivos + API REST
  (`raz/v1/fs/*`) por Application Password, com lint+backup+purge de cache. Detecta host bloqueado.
- **Marca RAZ** — identidade minimalista monocromática (branco/preto/cinza), corpo **monoespaçado** +
  **Playfair Display** (logo/títulos). Logo em `raz-base/logo.png`; `screenshot.png` de preview do tema.
- **Home "Em construção"** — `front-page.php` renderiza a seção `coming-soon` em página cheia (logo + texto
  da marca + contato), sem chrome global. Quando o site real ganhar seções, cai no fluxo header→seções→footer.

---

## 2. Princípios inegociáveis

1. **Resolva no módulo certo.** Bug numa seção se corrige na seção — nunca CSS/JS global de "incêndio".
2. **Conteúdo é dado editável, não código.** Texto/imagem/link/telefone vêm de **campos**; fallback é rede de segurança.
3. **Tudo em código e versionado.** Campos, CPTs, idiomas e config nascem em PHP do tema.
4. **Degrade com elegância.** Funciona sem ACF e com campo vazio (seção vazia some, sem erro).
5. **Não quebre o existente.** Em site de terceiros, opt-in por rota/meta (strangler).
6. **Uma fonte de verdade.** Sem duplicar arquivos/funções/cores/strings.
7. **Estado documentado** — leia e grave no `_pop` (§6).

---

## 3. Estrutura de pastas

```text
raz-base/
├─ style.css · theme.json · functions.php       # bootstrap: só constantes + loader glob()
├─ header.php / footer.php · index/404/singular/page.php
├─ inc/
│  ├─ helpers.php · fields.php · setup.php · context.php · enqueue.php · template-loader.php
│  ├─ i18n/        (multi-idioma)        · seo/   (head, schema, breadcrumb, robots, sitemap, llms, indexing, meta-box)
│  ├─ admin/       (options, maintenance, maintenance-rest)
│  ├─ cpt/         (cpt-popup, cpt-form) · popups.php · forms/ (providers, render, handler)
├─ template-parts/
│  ├─ global/      (site-header, site-footer, menu-mobile, whatsapp-float, popups, form*)
│  └─ page-{slug}/sections/{secao}.php   # uma seção por arquivo
└─ assets/ css/(base|global|page-{slug}/sections) · js/global · img/
```

**Espelhamento (inegociável):** `…/sections/hero.php` ⇄ `assets/css/page-{slug}/sections/hero.css` (⇄ `.js`).
O enqueue só carrega o asset **se o arquivo existir**.

---

## 4. Convenções (definição de "pronto")

🟢 **OBRIGATÓRIO**

- **Prefixo único** `raz_` em funções, handles, campos e classes (`.raz-*`, BEM por seção).
- **Escaping na saída** (`esc_html/esc_url/esc_attr`) e **sanitização na entrada** sempre.
- **Anatomia de seção:** lê campos → calcula se há conteúdo → `return` cedo se vazio → renderiza com escaping.
- **Mobile-first** (base + `min-width`); **tokens** (sem cor mágica); CSS por seção (prefixo, sem vazar).
- `defined( 'ABSPATH' ) || exit;` no topo de todo PHP; **nonce + capability** em forms/admin.
- **Conteúdo via campos** (`raz_field`/`raz_lang_field`/`raz_option`); nada hardcoded.
- `php -l` limpo; **QA responsivo 360→1440**; **zero erro de console**; a11y básica.

🔴 **PROIBIDO**

- Markup de seção dentro do template de página · duplicar arquivos/funções · pasta "v2"/"cópia".
- `get_field()`/`get_post_meta()` cru no template · hardcodar domínio (use `home_url()`) ou conteúdo do cliente.
- `eval`, SQL sem `$wpdb->prepare`, output sem escaping, debug em produção.

---

## 5. Receitas (como adicionar)

**Nova seção:** `template-parts/page-{slug}/sections/{secao}.php` (early-return + escaping) + CSS espelhado
(`.raz-{secao}`) + registrar no mapa de `raz_page_sections()` (`inc/context.php`) + campos editáveis.

**Nova página:** criar a página (definir slug) → `'{slug}' => array(...seções...)` no mapa → criar a pasta
`page-{slug}/sections/` com os pares PHP+CSS.

**Novo campo no painel:** editar o esquema em `inc/admin/options.php` (`raz_options_schema()`); ler com
`raz_option('chave')`. Para conteúdo por página: campo na meta box + `raz_field`/`raz_lang_field`.

**Novo popup:** `Popups → Adicionar` (HTML por idioma + regras + aparência) → shortcode/`data-raz-popup-open`.

**Novo formulário:** `Formulários → Adicionar` (HTML do `<form>` por idioma + envio) → cole `[raz_form id="X"]`.

**Novo provedor de form (RD/AC/Mailchimp):** implemente `Raz_Form_Provider` (`id/label/is_configured/send`)
e registre via `add_filter('raz_form_providers', …)` — **sem tocar no núcleo**.

**SEO de uma página:** meta box **SEO (Raz)** (título/descrição/OG por idioma, robots, canonical).
Indexação global e robots/sitemap/llms em `Raz → Opções → SEO`.

---

## 6. Trabalho colaborativo & Estado (`_pop`) — OBRIGATÓRIO

O acompanhamento vive em **`wp-content/uploads/_pop/`** (sobrevive a updates) e é espelhado no Git:

```text
_pop/
├─ index.php · .htaccess          # proteção (deny)
├─ ESTADO.md                      # verdade compartilhada — versionada a cada APROVAÇÃO (topo = mais recente)
└─ sessoes/{status}-{AAAA-MM-DD-HHMM}-{responsavel}.md   # status ∈ criando|editando|validando|concluido
```

🟢 **Antes:** ler `ESTADO.md` + `sessoes/`. **Durante:** documentar. **Ao aprovar:** registrar versão no
`ESTADO.md` (SemVer + responsável + escopo + o que/onde + próximo passo) e **fechar a sessão**.
🟢 Se já houver sessão aberta na mesma área, **não sobrescrever**: coordenar ou trabalhar em outra área.

---

## 7. Produção & Definição de "Pronto"

- [ ] Estado lido no início; sessão aberta; **`ESTADO.md` versionado** na aprovação; sessão fechada.
- [ ] Modular espelhado; sem duplicação; fallbacks presentes; **menu mobile** ok (quando há menu).
- [ ] Toda seção: early-return + escaping; BEM; tokens; mobile-first.
- [ ] **Tudo editável** (meta box/ACF); conteúdo **seedado**, não só fallback.
- [ ] SEO base + **controles de indexação** (robots/sitemap/llms/noindex por página).
- [ ] Performance (enqueue condicional, imagens, sem cache global off) · Segurança (nonces/escaping/sanitização).
- [ ] `php -l` limpo; QA 360→1440; zero erro de console; sem debug em produção.
- [ ] **Indexação:** ligar "Permitir indexação" só em produção (vem desligada).
- [ ] Remover conteúdo de teste/demo antes de publicar.

---

## 8. Acesso sem SSH (hospedagens travadas)

`Raz → Manutenção` (ative em *Opções → Manutenção*) edita arquivos pelo admin **e** via REST
(`raz/v1/fs/list|read|write|purge`) autenticada por **Application Password**. O painel traz um tutorial e a
mensagem pronta para enviar ao agente. **Limite:** fatal em `functions.php`/`inc/` derruba a API junto →
recuperação por **Recovery Mode** do WP ou File Manager da hospedagem.

---

*Raz Base — contrato vivo. Comece lendo este README e o `_pop/ESTADO.md`. Documente desde o início; versione a cada aprovação.*
