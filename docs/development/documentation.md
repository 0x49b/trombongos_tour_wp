# Documentation

This documentation is built with [MkDocs](https://www.mkdocs.org/) using the
[GitBook theme](https://gitlab.com/lramage/mkdocs-gitbook-theme)
(`mkdocs-gitbook`).

## Layout

- `mkdocs.yml` — MkDocs configuration, including the navigation and the theme
  selection.
- `docs/` — the Markdown source files for every page.
- `docs/requirements.txt` — the Python packages needed to build the docs.

The theme is configured in `mkdocs.yml`:

```yaml
theme:
  name: gitbook
```

## Building locally

Install the documentation dependencies (a virtual environment is recommended):

```bash
pip install -r docs/requirements.txt
```

Serve the docs with live reload while editing:

```bash
mkdocs serve
```

Then open <http://127.0.0.1:8000> in your browser.

Build the static site into the `site/` directory:

```bash
mkdocs build
```

## Publishing to GitHub Pages

A GitHub Actions workflow (`.github/workflows/deploy-docs.yml`) builds the docs
and deploys them to GitHub Pages on every push to `master` that touches the
documentation. You can also build and deploy manually:

```bash
mkdocs gh-deploy
```

!!! note
    `mkdocs gh-deploy` pushes the built site to the `gh-pages` branch. Make sure
    GitHub Pages is configured to serve from that branch (or from the GitHub
    Actions source when using the workflow).

## Adding a page

1. Create a new Markdown file under `docs/` (in an appropriate subfolder).
2. Add it to the `nav:` section of `mkdocs.yml` so it appears in the sidebar.
3. Preview with `mkdocs serve` and commit both files.
