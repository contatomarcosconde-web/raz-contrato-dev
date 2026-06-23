# Raz Base — Contrato de Desenvolvimento + Tema-base WordPress (guiado por IA)

> **Documento vinculante e operacional (POP) da Raz Consulting.** Este repositório reúne **o contrato de
> desenvolvimento** (este README) e o **tema-base modular pronto** (`raz-base/`). A ideia: **passe o link
> deste repositório a um agente/IA ou dev** — ele **lê este contrato, segue o que ele pede** (intake →
> plano → execução) e, **para sites novos, usa o tema `raz-base`**, que já entrega grande parte das regras
> abaixo prontas. Vale para **criar, converter, editar e evoluir** sites (WordPress, tema custom/child).

---

## 🔒 Governança — repositório de desenvolvimento GUIADO (somente leitura)

> Este repositório é **referência compartilhada por todos os devs/agentes da agência**. Ele é para ser
> **LIDO e SEGUIDO — nunca editado por quem o consome.**

🔴 **Para qualquer dev ou IA que chegar por aqui:**
- **NÃO** faça commit, push, branch ou Pull Request **neste repositório**. Você o **lê**.
- Aplique o tema e o contrato **no repositório/site do cliente** (ou em `wp-content/themes/` do projeto), **não aqui**.
- Trate este README como **contrato vinculante**: o que está como 🔴 reprova a entrega; 🟢 é parte do "pronto".

🟢 **Quem pode alterar este repositório:** **somente Marcos (dono do repositório)** ou uma sessão autorizada por ele.
Sugerido no GitHub: proteção de branch em `main` (revisão obrigatória + restrição de quem dá push).

> Em resumo: **a agência consome; o dono mantém.** Assim o contrato e o tema permanecem como **fonte única de verdade**.

---

## ▶ Início — para a IA / dev (faça NESTA ordem, antes de qualquer código)

1. **Leia este contrato por completo.** Respeite a governança acima (você lê, não edita este repo).
2. **Identifique o cenário** (modo de trabalho — §0.1) e **faça o intake (§0)** com o cliente. Se faltar
   resposta crítica, **pergunte — não presuma.**
   - **🆕 Site NOVO (Modo A):** **use o tema `raz-base`** deste repositório — copie a pasta `raz-base/` para
     `wp-content/themes/` do WordPress, **ative**, e leia **[`raz-base/README.md`](raz-base/README.md)**
     (guia operacional). Boa parte do contrato (modular, i18n, SEO, painel, forms, popups, manutenção) **já
     vem pronta** — veja **"O que o tema já oferece"** abaixo e **componha, não reconstrua.**
   - **🔧 Converter / Editar / Nova página (Modos B/C/D):** **leia o estado do projeto** em
     `wp-content/uploads/_pop/ESTADO.md` (no servidor do site — **não vive neste repositório**) e a pasta
     `sessoes/`. **Continue de onde parou**, sem refazer nem sobrescrever.
3. **Abra uma sessão** (lock leve) no `_pop/sessoes/` do site dizendo a área/arquivos que vai tocar.
   **Pergunte o nome do dev** para nomear a sessão; toda evolução muda a sessão para `editando`; `validando`
   conta como `editando`; `concluido` = ninguém editando. Se houver sessão aberta de outra pessoa na mesma
   área, **alerte e coordene** antes de continuar (evita sobrescrever o trabalho do outro).
4. **Emita o Plano de Execução (§0.9)** antes de codar.
5. Trabalhe pela **Trilha do modo (§3)**, respeitando os **princípios (§1)** e a **definição de pronto (§12)**.
   **Versione o `ESTADO.md` a cada aprovação** e **feche a sessão.**

> **Resumo:** ler antes · documentar durante · versionar a cada aprovação.

### Mensagem-modelo para acionar um agente
> *"Leia https://github.com/contatomarcosconde-web/raz-contrato-dev e siga o contrato (README). É um site
> **novo**: use o tema `raz-base`, me faça as perguntas do intake e devolva o Plano de Execução antes de codar.
> Não edite este repositório — ele é só leitura."*

---

## Metas inegociáveis (qualquer entrega)

1. **Modular** — isolamento por página e por seção (PHP e CSS espelhados).
2. **Responsivo** (mobile-first) e **acessível**; **menu mobile** obrigatório quando há menu.
3. **SEO e performance** desde o início; **controle de indexação** explícito.
4. **Edição total pelo cliente** — todo conteúdo é **editável no admin** (ACF *ou* meta box), nunca preso no código.
5. **Sem quebrar o que existe** — em sites de terceiros, conviver (strangler), nunca "apagar incêndio" no global.
6. **Uma fonte de verdade** — sem duplicar arquivos, lógica, cores ou strings.
7. **Estado documentado e versionado** — `_pop/ESTADO.md` é a verdade compartilhada do site.

| Selo | Categoria | Significado |
|---|---|---|
| 🔴 | **PROIBIDO** | Nunca fazer. Reprova a entrega. |
| 🟠 | **EVITAR** | Só com justificativa técnica escrita no PR. |
| 🟢 | **OBRIGATÓRIO** | Sempre fazer. Faz parte da definição de "pronto". |
| 🔵 | **RECOMENDADO** | Boa prática esperada; ausência precisa de motivo. |

---

## 0. FORMULÁRIO DE ENTRADA (intake) — preencher ANTES de qualquer código

🟢 **OBRIGATÓRIO.** O agente/dev faz estas perguntas (ou preenche com o que já sabe) e devolve um **Plano de
Execução** antes de tocar no código. Se faltar resposta crítica, **perguntar** — não presumir.

**0.1 Objetivo / Modo de trabalho** (escolha 1)
- **A) Criar** — tema/site novo do zero → **use o tema `raz-base`** deste repo.
- **B) Converter** — site existente → tema custom (ou child), declinando o que existe aos poucos.
- **C) Editar/evoluir** — mexer num site existente (tema possivelmente de terceiros).
- **D) Nova página** — criar página(s) nova(s) num site existente.

**0.2 Plataforma & acesso**
- WordPress? versão do WP/PHP? Hospedagem? **Tem staging?** Acesso (SSH/painel/Git)? Deploy como?
  *(Sem SSH? O tema traz `Raz → Manutenção`: editor de arquivos + API REST — veja §14.)*

**0.3 Tema & builder atuais** (modos B/C/D)
- Qual tema está ativo? É child? **Foi feito por este contrato?**
- **Usa page builder?** (Elementor / Divi / WPBakery / Bricks / Gutenberg) → **se sim, emitir o AVISO de page builder (§8) ANTES de prosseguir.**

**0.4 Modelo de design** (se houver)
- Tem modelo? **Em qual formato?** (Figma + Dev Mode / export 1920+mobile / página live p/ clonar / PDF / referência solta).
- → Orientar o cliente a passar o modelo da **melhor forma possível** (§2.3) para máxima fidelidade.

**0.5 Conteúdo & edição**
- Quem vai editar o site? → **Sempre prever painel de edição** (ACF ou meta box). Nada hardcoded.

**0.6 Multi-idioma** (opcional)
- Precisa? Quais idiomas? Idioma padrão? → se sim, ver §5-bis. *(O tema já traz PT/EN/ES liga/desliga.)*

**0.7 Indexação & descoberta** (§6)
- O site **deve ser indexado**? O que **não** deve? `robots.txt`, `sitemap.xml`, `llms.txt` (permitir/bloquear LLMs)?

**0.8 Integrações & componentes**
- **WhatsApp flutuante** (número/mensagem/horário)? Formulários/CRM? Analytics/Pixel? WooCommerce? Popups?

**0.9 Saída esperada (Plano de Execução)**
Após o intake, devolver: **modo escolhido**, **trilha**, **riscos/avisos** (ex.: page builder), **lista de
páginas/seções**, **campos editáveis previstos**, **estratégia de i18n/indexação**, **plano de deploy** e
**definição de pronto** aplicável.

---

## 0-bis. Estado do Projeto, Documentação & Trabalho Colaborativo (OBRIGATÓRIO)

> 🟢 Garante que **vários devs/agentes** trabalhem no **mesmo site** **sem se sobrescrever**. Todo projeto tem
> um **Diretório de Estado** — **lido SEMPRE antes** de trabalhar, **documentado durante**, **versionado a cada aprovação**.

🟢 **Local padrão: `wp-content/uploads/_pop/`** (a pasta `uploads/` **não é tocada** por updates de core/tema/plugin).
**Não vive neste repositório** — é estado/locks **por site**, criado no servidor de cada projeto. Se não existir,
o agente **cria e inicializa** na primeira vez. **Nunca apagar** (inclusive em deploy/atualização).

```text
wp-content/uploads/_pop/
├─ index.php            # vazio (proteção)
├─ .htaccess            # deny (proteção)
├─ ESTADO.md            # GLOBAL versionado — verdade compartilhada (lido por todos; topo = mais recente)
└─ sessoes/             # controle individual por dev/sessão
   └─ {status}-{AAAA-MM-DD-HHMM}-{responsavel}.md   # status ∈ criando | editando | validando | concluido
```

🟢 **`ESTADO.md`** = arquitetura do tema, **CPTs/campos**, **páginas/rotas**, componentes globais, integrações,
decisões e pendências — **o que foi criado, onde está e como está**. Versionar a cada **APROVAÇÃO** (SemVer +
responsável + escopo + o que/onde + próximo passo), entrada nova **no topo**.

🟢 **Sessões** (`sessoes/`): ao começar, criar `criando|editando-…` (lock) com a área/arquivos; em validação,
renomear para `validando-…`; ao aprovar, `concluido-…` **e** registrar a versão no `ESTADO.md`. Se houver sessão
aberta na mesma área, **não sobrescrever**: avisar e coordenar.

🔴 **PROIBIDO:** começar **sem ler** o estado · encerrar etapa **sem versionar** o `ESTADO.md`/fechar a sessão ·
apagar/zerar o Diretório de Estado.

---

## 1. Princípios inegociáveis (portáveis a qualquer stack)

1. **Resolva no módulo certo.** Bug numa seção se corrige na seção — nunca CSS/JS global para "apagar incêndio".
2. **Conteúdo é dado editável, não código.** Texto/imagem/link/telefone vêm de **campos**; fallback é rede de segurança, não a fonte.
3. **Tudo em código e versionado.** Campos, CPTs, idiomas, painéis e config nascem em PHP do tema, no Git.
4. **Degrade com elegância.** Funciona sem ACF e com campo vazio (seção vazia some, sem erro).
5. **Não quebre o existente.** Em site de terceiros, **conviver** (opt-in por rota/meta), nunca clobberar global.
6. **Uma fonte de verdade.** Sem duplicar arquivos/pastas/funções/cores/strings.
7. **Estado documentado e versionado** — §0-bis.

---

## 2. Descoberta & Intake de Design

**2.1** 🟢 Mapear: páginas/rotas, tipos de conteúdo, plugins, page builder, idiomas e **o que o cliente edita hoje**.
Em conversão/edição (B/C/D), inventariar **page builder vs. nativo**; definir o que será **declinado**.

**2.2** 🟢 Se o site usa Elementor/Divi/WPBakery/Bricks → **emitir o aviso do §8** e registrar a decisão antes de seguir.

**2.3 Como receber o MODELO (melhor → pior):**
1. **Figma com Dev Mode** — ideal (tokens, medidas, estados, assets). Pedir frame **desktop 1920 + mobile** + estados.
2. **Export Figma/PSD** — telas **1920 + 768 + 390** + assets em alta + fontes/cores.
3. **Página live (URL)** — boa p/ conteúdo/estrutura; visual **reconstruído** em nativo (não copiar HTML do builder).
4. **PDF/imagem** — medidas/tokens inferidos (menor precisão).
5. **Descrição/referências** — último caso; alinhar protótipo antes.

🟢 Em qualquer caso, exigir: paleta (tokens), tipografia, espaçamentos, **versão mobile**, estados e **assets exportáveis**.
🔵 Padrão é **grid responsivo fluido**; pixel-perfect (canvas escalado) só quando o cliente exigir.

---

## 3. TRILHAS por modo (A/B/C/D)

> Toda trilha termina na **Definição de Pronto (§12)** e respeita os **princípios (§1)**.

### Trilha A — Criar tema/site novo → **com o tema `raz-base`**
1. Intake (§0) + design (§2.3). **Copie `raz-base/` para `wp-content/themes/` e ative.**
2. Ajuste **tokens** (`theme.json` + `assets/css/base/tokens.css`) à marca do cliente.
3. **Não reconstrua** o que o tema já dá (loader glob, fallbacks, header/footer, menu mobile, painel, campos,
   i18n, SEO, forms, popups, manutenção) — **componha** (veja "O que o tema já oferece" e `raz-base/README.md`).
4. Página = lista de seções (PHP+CSS espelhados, early-return) — crie só as seções do projeto.
5. SEO/indexação (§6), performance (§9), i18n se pedido (§5-bis).
6. QA responsivo 360→1440 + checklist (§12).

### Trilha B — Converter site existente → tema (ou child)
1. **Nunca** ativar/trocar tema sem OK e, de preferência, **em staging**.
2. **Strangler, opt-in por rota/meta:** o tema assume **só** a rota marcada; o resto segue como está.
3. Migrar **conteúdo para campos editáveis** (seed a partir do conteúdo atual, inclusive **abas/acordeões ocultos**).
4. Header/footer/menus **dinâmicos** (menu do WP). Promover à produção só com aprovação; backup antes; rollback documentado.
5. **Child theme** quando o tema-pai é de terceiros e há atualizações.

### Trilha C — Editar/evoluir site existente (tema de terceiros)
1. Diagnóstico do que é seguro mexer. 2. Page builder no alvo → **aviso §8** (preferir reconstruir em nativo e declinar a rota → vira B/D).
3. Correções **no módulo certo**; sem CSS global de incêndio; sem editar core/plugins. 4. Tudo novo fica **editável** (§5/§7).

### Trilha D — Nova página em site existente
1. Builder + cliente quer no builder → **aviso §8**. 2. Recomendado: página **nativa** opt-in (meta `theme_template`),
declinando o builder só nela. 3. Página = seções + campos editáveis + enqueue condicional + i18n/indexação conforme o site.

---

## 3-bis. Conversão FIEL (detalhe da Trilha B)

🟢 **a) Extração COMPLETA — inclusive estados OCULTOS.** Conteúdo de abas/acordeões/carrosséis fica `display:none`.
Extrair com **`textContent`** (não `innerText`) abrindo cada estado; **diff contra a fonte** — nada pode sumir.
🟢 **b) Inventário + mapa de componentes ANTES de codar** (globais reutilizáveis nascem uma vez, são compostos).
🟢 **c) Aceite objetivo de "100% fiel":** paridade visual em **390/768/1440** (lado a lado), checklist de conteúdo,
todos os links/forms/CTAs/menus funcionando — evidência no `ESTADO.md`.
🟢 **d) Preservar SEO/URLs:** mesmos slugs (301 se mudar), title/meta migrados, hreflang, schema, sitemap.
🟢 **e) Escala consistente:** 🔴 não misturar canvas escalado com CSS fluido (gera "salto de zoom").
🟢 **f) Mídia portável** (no tema ou importada por seed idempotente — não referenciar attachment por ID).
🟢 **g) Conteúdo migrado vira DADO EDITÁVEL** (seed em ACF/meta, idempotente).
🟢 **h) Reconstruir, não copiar** (🔴 proibido colar div-soup do builder).
🟢 **i) Strangler + rollback** (remover a meta volta ao builder; backup antes; cada aprovação versionada).

---

## ⭐ O que o tema `raz-base` JÁ oferece (Modo A — componha, não reconstrua)

- **Arquitetura modular** — página = lista de seções; loader `glob()`; enqueue **condicional** com cache-busting por `filemtime`; PHP e CSS **espelhados**.
- **Camada de campos** (`raz_field`/`raz_lang_field`/`raz_option`) — ACF se existir → meta nativa → default.
- **Multi-idioma PT/EN/ES** — rota `/{lang}/`, `hreflang`, seletor reversível, campos por idioma (`{campo}__{lang}`); liga/desliga no painel.
- **Painel de Opções** (`Raz`) — identidade, **WhatsApp flutuante**, scripts (analytics/pixel), SEO/indexação, idiomas, manutenção.
- **Sistema de Popups** (CPT) — HTML por idioma, regras (gatilho/segmentação/frequência/agenda), **CSS escopado**.
- **Sistema de Formulários** (CPT) — template livre + **shortcode** `[raz_form id="X"]`, handler REST único (anti-cache, honeypot, time-trap, rate-limit, LGPD) e **registry de provedores** (e-mail nativo; RD/AC/Mailchimp via filtro).
- **SEO completo** — meta box por página (título/descrição/OG **por idioma**, noindex/canonical), `<head>` OG/Twitter, **JSON-LD** (Organization/WebSite/Article/BreadcrumbList), `robots.txt`/`sitemap`/`llms.txt`, bloqueio de IA opcional, breadcrumb.
- **Manutenção sem SSH** (desligada por padrão) — editor de arquivos + API REST por Application Password, com lint + backup + purge de cache; detecta host bloqueado (§14).
- **Home "Em construção"** minimalista pronta + identidade da marca.

> Detalhe técnico e receitas de cada item: **[`raz-base/README.md`](raz-base/README.md)**.

---

## 4. Estrutura & modularidade (WordPress)

```text
tema/
├─ style.css · theme.json · functions.php   # bootstrap: só constantes + loader glob()
├─ header.php / footer.php · index/404/singular/page.php   # fallbacks
├─ inc/  setup · enqueue · context · template-loader · fields
│  ├─ admin/ (opções + meta boxes + manutenção) · seo/ · i18n/ · cpt/ · forms/
├─ template-parts/ global/(header,footer,menu,form,popup,whatsapp) · page-{slug}/sections/{secao}.php
├─ assets/ css|js (base/ · global/ · page-{slug}/sections/) · img/
└─ woocommerce/   # só em loja (§10)
```

🟢 **OBRIGATÓRIO:** espelhamento `…/sections/hero.php` ⇄ `…/hero.css` (⇄ `.js`); loader por `glob()`; detecção de
contexto central; fallbacks; **escaping** na saída + **sanitização** na entrada; **prefixo único** (`raz_`/`.raz-`);
**menu mobile** acessível; SemVer em `style.css` + cache-busting.
**Anatomia de seção:** lê campos → calcula se há conteúdo → `return` cedo se vazio → renderiza com escaping.

🔴 **PROIBIDO:** markup de seção no template de página · mu-plugins p/ lógica do tema · editar core/plugins · duplicar
arquivos/funções (pasta "v2"/"cópia") · `eval`, SQL sem `$wpdb->prepare`, output sem escaping, debug em produção ·
hardcodar **domínio** (use `home_url()`) ou **conteúdo do cliente**.

---

## 5. Edição pelo cliente — ACF **ou** meta box (inegociável)

> **ACF é opcional.** O inegociável: **todo conteúdo editável no admin**, nascendo nos campos (seed), não só como fallback.

🟢 Acessar campos **só** pela camada (`raz_field()`), nunca `get_field()` cru no template · toda string/imagem/link tem
campo **preenchido** · `register_post_meta()` (`show_in_rest=true`), sanitização no save, **nonce** em meta box ·
conteúdo repetível → **CPT** ou repetidor.
🔴 Conteúdo só no código · tema que fataliza sem ACF · `get_field()` espalhado sem a camada.
🔵 ACF Pro p/ UX rica (lido por `raz_field()`); sem ACF, meta boxes nativas organizadas (nonce + sanitização).

---

## 5-bis. Multi-idioma — OPCIONAL (já implementado no tema)

🟢 Quando usado: **campos por idioma no MESMO post** (`{campo}__{lang}`) · **rota `/{lang}/{slug}`** · strings de UI
por dicionário (fallback no padrão) · **seletor sempre reversível** · `hreflang` + `canonical` + `<html lang>`.
🔴 Detecção por **cookie** (cookie+cache = idioma errado) · output-buffer reescrevendo links · prender num idioma.
🔵 Polylang aceitável se o cliente preferir UX de plugin (links via `home_url()`, seletor reversível).

---

## 6. SEO & Indexação (com controle do cliente)

🟢 **SEO base:** 1 `<h1>`/página; `<title>`/meta description **editáveis por página**; URLs estáveis (301 p/ slugs
antigos); `alt`; imagens com dimensão (anti-CLS); dados estruturados (Organization/Breadcrumb/Article/Product); OG/Twitter.
🟢 **Controle de indexação (painel):** chave global "indexar?" (noindex geral p/ staging) · **`noindex` por página** ·
**`robots.txt`** gerenciável · **`sitemap.xml`** (escolher post types/taxonomias) · **`llms.txt`** (permitir/bloquear LLMs).
🔵 Painel único de SEO/Indexação nas Opções — defaults sensatos, tudo editável. *(Tudo isso já existe no tema.)*

---

## 7. Configuração do site — tudo editável (painel/CPT)

🟢 Nada de header/footer/contato/scripts hardcodados. Editável no admin: **identidade** (logo claro/escuro, favicon,
tokens) · **header** (menu dinâmico, CTAs) · **footer** (endereço, telefone, e-mail, redes, copyright) · **WhatsApp
flutuante** (número, mensagem, horário, on/off, posição) · **multi-idioma** · **indexação/SEO** · **integrações/scripts**
(com sanitização + capability). 🔴 Nunca exigir editar arquivo para mudar conteúdo que o cliente deveria mudar sozinho.

---

## 7-bis. Formulários (componente do tema, sem depender de plugin)

🟢 Componente reutilizável configurável (campos, rótulos, origem, destino) · **validação** server-side + HTML5 +
estados sucesso/erro · **segurança** (nonce + capability, **anti-spam** honeypot/token, sanitização) · **LGPD**
(consentimento + link da política) · **a11y** (label/aria, foco, mensagens) · **origem do lead** marcada · integração
**server-side por hook** (`wp_mail` + ponto de extensão p/ CRM; segredos fora do front).
🔴 Form sem nonce/anti-spam/consentimento · chamar API de terceiros **direto do JS** (use proxy server-side com
allowlist) · destino/conteúdo hardcodado sem campo. 🔵 **handler único** variando por origem; rate-limit em forms expostos.
*(O tema entrega CPT `raz_form` + shortcode + handler REST + registry de provedores — veja `raz-base/README.md`.)*

---

## 8. Page builders & temas de terceiros — convivência + AVISO

🟢 Site com Elementor/Divi/WPBakery/Bricks → **avisar o cliente** e registrar a decisão:

> **Aviso padrão:** "Este site usa um **page builder**. Este fluxo **não edita o canvas visual do builder** e trabalha
> de forma **limitada** dentro dele. O melhor resultado (performance, SEO, manutenção) vem de **reconstruir a
> página/seção em tema nativo** e **declinar o builder apenas naquela rota** (strangler), mantendo o resto intacto.
> Precisamos da sua **aprovação** antes de declinar qualquer página."

🟢 Convivência: strangler opt-in por rota/meta · enqueue com early-return (zero asset do tema nas páginas do builder) ·
dequeue dos assets do builder **só** na rota nativa · **nunca** editar arquivos do builder/plugin.
🔴 Prometer "edição visual perfeita" dentro de builder de terceiros via este fluxo.

**8.1 Cache & builder (conferir SEMPRE em conversão/edição):** CSS Combine/minify congelado (limpar combinados +
purge; combine OFF no dev) · hardening removendo `?ver` (cache-busting por `filemtime`) · CSS do builder vazando
(dequeue só na rota) · cache de página (purge da rota pós-deploy) · CDN/proxy (versão no asset + purge).

---

## 9. Performance, CSS/A11y, Segurança (resumo obrigatório)

**Performance** 🟢 enqueue **condicional**; scripts no footer; imagens `srcset` + `loading="lazy"`; sem
`DONOTCACHEPAGE` global; cache-busting por versão/mtime; remover BOM. 🟠 evitar 2 libs p/ a mesma função.
**CSS/Responsivo/A11y** 🟢 **mobile-first**; **tokens** (sem cor mágica); CSS por seção (prefixo, sem vazar);
`aria-label`/landmarks; `alt`; foco visível; teclado; testar 360/390/414/768/1024/1440; **menu mobile** acessível.
🟠 evitar `px` fixo em título (`clamp()`), `!important`. 🔵 `prefers-reduced-motion`.
**Segurança** 🟢 **nonces** + `current_user_can` em forms/admin; **escaping** na saída + **sanitização** na entrada;
segredos fora do front; `defined('ABSPATH') || exit;`. 🔴 chave/API no JS; SQL sem `$wpdb->prepare`; HTML do usuário sem `wp_kses`.

---

## 10. WooCommerce (só em loja)

🟢 `add_theme_support('woocommerce')`; customizar **por hooks**; overrides **só** em `tema/woocommerce/` (mínimos);
catálogo/checkout responsivos. 🔴 editar o plugin Woo; sobrescrever template inteiro quando um hook resolve.
🔵 catálogo multilíngue pelo mesmo modelo de campos por idioma.

---

## 11. Garantias da entrega

Toda entrega é **garantidamente**: **Modular** · **SEO-correto** (h1 único, metas editáveis, schema, sitemap/robots/llms
controlados) · **Responsiva** (mobile-first, menu mobile, 360→1440) · **Rápida** (enqueue condicional, imagens otimizadas)
· **100% editável** (ACF ou meta box) · **Sem quebrar o existente** (strangler).

---

## 12. Definição de "Pronto" — checklist

**Comum (A/B/C/D)**
- [ ] Estado lido no início; sessão aberta; **`ESTADO.md` versionado** na aprovação; sessão fechada.
- [ ] Intake (§0) preenchido + Plano de Execução entregue.
- [ ] Modular espelhado; sem duplicação; `functions.php` enxuto (glob); fallbacks presentes.
- [ ] Toda seção: early-return + escaping; BEM; tokens; mobile-first; **menu mobile ok**.
- [ ] **Tudo editável** (ACF/meta box); conteúdo **seedado**, não só fallback.
- [ ] **Painel de config** (header/footer/WhatsApp/integrações).
- [ ] SEO base + **controles de indexação** (robots/sitemap/llms/noindex por página).
- [ ] Performance · Segurança (nonces/escaping/sanitização).
- [ ] `php -l` limpo; QA 360→1440; zero erro de console; sem debug em produção; a11y básica.
- [ ] Indexação ligada **só em produção** (vem desligada); conteúdo de teste/demo removido.

**B/C/D (site existente)** — [ ] Strangler confirmado · [ ] Aviso de page builder registrado · [ ] Backup + rollback documentado.
**B — Conversão fiel** — [ ] Extração completa (estados ocultos) + diff · [ ] Paridade visual 390/768/1440 · [ ] SEO/URLs preservados · [ ] Mídia portável + conteúdo seedado · [ ] Reconstruído semântico (sem HTML do builder), escala consistente.
**Se multi-idioma** — [ ] Campos por idioma; rota `/{lang}/`; seletor reversível; hreflang/canonical/`<html lang>`.
**Se loja** — [ ] Woo por hooks/overrides em `tema/woocommerce/`.

---

## 13. Procedimentos rápidos

- **Nova página:** intake → seções (PHP+CSS espelhados, early-return) → campos editáveis **preenchidos** → mapa de
  `raz_page_sections()` + enqueue condicional → indexação/SEO → i18n se ativo → checklist.
- **Nova seção:** PHP early-return + CSS espelhado (+JS) + campos + slug no array de seções + enqueue.
- **Novo CPT:** `inc/cpt/cpt-x.php` (auto-registra no `init`) + campos + admin UI.
- **Novo popup / formulário:** `Popups`/`Formulários → Adicionar` (HTML por idioma) → shortcode/atributo.
- **Novo provedor de form (RD/AC/Mailchimp):** implemente `Raz_Form_Provider` e registre via `add_filter('raz_form_providers', …)` — **sem tocar no núcleo**.
- **Ativar multi-idioma:** painel `Raz → Idiomas` (liga + escolhe idiomas).

---

## 14. Acesso sem SSH (hospedagens travadas)

`Raz → Manutenção` (ative em *Opções → Manutenção*) edita arquivos pelo admin **e** via REST
(`raz/v1/fs/list|read|write|purge`) autenticada por **Application Password**, com lint + backup + purge de cache.
O painel traz tutorial e mensagem pronta para o agente. **Limite:** fatal em `functions.php`/`inc/` derruba a API
junto → recuperação por **Recovery Mode** do WP ou File Manager da hospedagem.

---

## Estrutura do repositório & requisitos

```text
raz-contrato-dev/
├─ README.md        ← ESTE contrato (o que a IA lê e segue primeiro)
└─ raz-base/        ← o tema-base pronto (copie para wp-content/themes/)
   └─ README.md     ← guia operacional do tema (capacidades, receitas, "pronto")
```

- **Requisitos:** WordPress 6.x+ · PHP 7.4+ · permalinks "pós-nome" (rotas de idioma e REST). ACF **opcional**.
- **`_pop/` não vive aqui** — é estado/locks **por site**, em `wp-content/uploads/_pop/` no servidor de cada projeto.

---

*Contrato vivo da Raz Consulting. Comece por este README; depois `raz-base/README.md`. Leia antes · documente durante
· versione a cada aprovação. **Este repositório é somente leitura — só o dono o mantém.***
