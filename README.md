# Raz Base — Contrato de Desenvolvimento + Tema-base WordPress (para evoluir com IA)

> **Repositório oficial da Raz Consulting.** Contém um **tema-base modular pronto** (`raz-base/`) e
> este **contrato de desenvolvimento**. A ideia é simples: **passe o link deste repositório a um agente/IA**
> e ele lê este contrato, entende o que o tema já oferece e **começa a desenvolver daqui** — sem reinventar.

---

## ▶ Início — para a IA / dev (leia e siga NESTA ordem)

Você recebeu o link deste repositório. Faça assim:

1. **Leia este README por completo.** Ele é o contrato: regras inegociáveis + o que o tema entrega.
2. **Identifique o cenário:**
   - **🆕 Site novo (do zero):** copie a pasta **`raz-base/`** para `wp-content/themes/` do WordPress,
     **ative o tema** e leia **[`raz-base/README.md`](raz-base/README.md)** (guia operacional do tema).
     O tema já vem com tudo da seção **"O que o tema já oferece"** abaixo — **componha, não reconstrua.**
   - **🔧 Evoluir um projeto existente:** leia o **estado do projeto** em
     `wp-content/uploads/_pop/ESTADO.md` (vive **no servidor de cada site**, _não_ neste repositório) e a
     pasta `sessoes/`. **Abra uma sessão** (lock leve) antes de tocar em qualquer arquivo.
3. **Pergunte o nome do desenvolvedor** que vai evoluir o projeto (para nomear a sessão). Toda evolução muda a
   sessão para `editando`; `validando` conta como `editando`; `concluido` = ninguém editando. Se houver sessão
   aberta de outra pessoa, **alerte e coordene** antes de continuar (evita sobrescrever o trabalho do outro).
4. **Faça o intake** com o cliente (objetivo, páginas, conteúdo, idiomas, indexação, integrações) e
   **emita um Plano de Execução** antes de codar.
5. Trabalhe seguindo as **regras** abaixo e as **receitas** de [`raz-base/README.md`](raz-base/README.md).
   **Versione o estado a cada aprovação.**

> **Resumo:** ler antes · documentar durante · versionar a cada aprovação.

---

## 🧩 O que o tema JÁ oferece (não reinvente — componha)

- **Arquitetura modular** — página = lista de seções; loader `glob()`; enqueue **condicional** com
  cache-busting por `filemtime`; PHP e CSS **espelhados** por seção.
- **Camada de campos** (`raz_field`/`raz_lang_field`/`raz_option`) — ACF se existir → meta nativa → default.
- **Multi-idioma PT/EN/ES** — rota `/{lang}/`, `hreflang`, seletor reversível, campos por idioma
  (`{campo}__{lang}`). Liga/desliga e escolhe os idiomas ativos no painel.
- **Painel de Opções** (`Raz` no admin) — identidade, **WhatsApp flutuante**, scripts (analytics/pixel),
  SEO/indexação, idiomas, manutenção. Renderizador por esquema.
- **Sistema de Popups** (CPT) — HTML por idioma, regras (gatilho/segmentação/frequência/agenda),
  **CSS escopado**, abrir/fechar por atributo, URL ou ESC.
- **Sistema de Formulários** (CPT) — template livre + **shortcode** `[raz_form id="X"]`, handler REST único
  (anti-cache, honeypot, time-trap, rate-limit, LGPD) e **registry de provedores** (e-mail nativo;
  RD/ActiveCampaign/Mailchimp entram por filtro, sem tocar no núcleo).
- **SEO completo** — meta box por página (título/descrição/OG **por idioma**, noindex/canonical),
  `<head>` (title/description/OG/Twitter), **JSON-LD** (Organization/WebSite/Article/BreadcrumbList),
  `robots.txt`/`sitemap`/`llms.txt` gerenciáveis, bloqueio de IA opcional, breadcrumb.
- **Manutenção sem SSH** (desligada por padrão) — editor de arquivos no admin **e** API REST por
  Application Password, com lint + backup + purge de cache. Detecta host bloqueado e se auto-desabilita.
- **Home "Em construção"** minimalista pronta + identidade da marca (monocromática, Playfair + monoespaçada).

> Detalhe técnico de cada item em **[`raz-base/README.md`](raz-base/README.md) §1**.

---

## 🎯 Regras inegociáveis (toda entrega)

1. **Modular** — bug numa seção se resolve na seção; nunca CSS/JS global de "incêndio".
2. **Conteúdo é dado editável, não código** — texto/imagem/link/telefone vêm de campos; fallback é só rede de segurança.
3. **Tudo em código e versionado** — campos, CPTs, idiomas e config nascem no PHP do tema.
4. **Degrade com elegância** — funciona sem ACF e com campo vazio (seção vazia some, sem erro).
5. **Não quebre o existente** — em site de terceiros, opt-in por rota/meta (strangler).
6. **Uma fonte de verdade** — sem duplicar arquivos, lógica, cores ou strings; sem pasta "v2"/"cópia".
7. **Responsivo + acessível + SEO/performance** desde o início; indexação **explícita** (vem desligada).
8. **Segurança** — `defined('ABSPATH')||exit;`, nonce + capability, escaping na saída, sanitização na entrada.

Definição completa de "pronto", convenções e receitas: **[`raz-base/README.md`](raz-base/README.md)**.

---

## 📁 Estrutura do repositório

```text
raz-contrato-dev/
├─ README.md        ← este contrato (o que a IA lê primeiro)
└─ raz-base/        ← o tema-base pronto (copie para wp-content/themes/)
   └─ README.md     ← guia operacional do tema (capacidades, receitas, "pronto")
```

**O que NÃO vive aqui (de propósito):**
- **`_pop/` (estado + sessões)** — é **por site** e mora em `wp-content/uploads/_pop/` no servidor de cada
  projeto (sobrevive a updates). Não entra no repositório porque é estado/locks específicos de cada cliente.
  Ao começar num site, **crie/leia** o `_pop` lá.

---

## ✅ Requisitos

- WordPress 6.x+ · PHP 7.4+ · permalinks "pós-nome" (para rotas de idioma e REST).
- ACF é **opcional** (o tema funciona com meta box nativa).

---

*Raz Base — contrato vivo. Comece por este README; depois `raz-base/README.md`. Documente desde o início; versione a cada aprovação.*
