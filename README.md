# edgar-ferreira.com — theme

The WordPress theme for [edgar-ferreira.com](https://edgar-ferreira.com), built
on [Sage 11](https://roots.io/sage/) and [Acorn](https://roots.io/acorn/).

The site itself — the Bedrock install, the container image, the deployment —
lives in [`efsn88/bedrock-wordpress`](https://gitlab.com/efsn88/bedrock-wordpress).
This repository is only the theme, consumed from there as a Composer package.

## How it is consumed

`bedrock-wordpress` declares this repository as a Composer VCS source and
requires `edgar-ferreira/theme`. `extra.installer-name` in `composer.json` is
what puts it at `web/app/themes/edgar-ferreira` rather than the
`web/app/themes/theme` that the package name would otherwise produce.

Under Bedrock the theme has **no `vendor/` directory of its own**: Composer
resolves `roots/acorn` and `mpdf/mpdf` into the project's autoloader, which
also registers this theme's `App\` namespace. `functions.php` detects that and
skips loading a local autoloader — see the comment there before changing it.

Front-end assets are **not committed**. `public/` is built with Vite during the
site's container build, so a release is reproducible from source.

Releases are tagged; the site pins a version constraint rather than tracking
`main`.

## Local development

```bash
composer install   # only needed to work on the theme standalone
npm install
npm run dev        # Vite dev server with HMR
npm run build      # production assets into public/build
```

Other scripts:

| Command | Does |
|---|---|
| `npm run icons` | regenerates the Lucide icon set into the Blade components |
| `npm run translate` | refreshes the POT file and updates the PO catalogues |
| `npm run translate:compile` | compiles MO + JSON translation files |
| `composer exec pint` | formats PHP to the project's Pint ruleset |

## Layout

```
app/
  Console/           WP-CLI commands (markdown content import)
  Providers/         Acorn service provider
  View/Composers/    view models, one per section/template
  *.php              setup, filters, post types, fields, CV rendering
resources/
  views/             Blade templates
  css/  js/          Vite entry points
  lang/              translation catalogues
theme.json           WordPress global styles
```
