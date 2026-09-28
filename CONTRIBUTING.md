# Contributing to WordCamp Certificate Generator

Thank you for taking the time to contribute! 🎉  
Every bug report, feature suggestion, and pull request helps make this plugin better for the entire WordCamp community.

---

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Getting Started (Local Setup)](#getting-started-local-setup)
- [Branch Naming Convention](#branch-naming-convention)
- [Commit Message Guidelines](#commit-message-guidelines)
- [Pull Request Process](#pull-request-process)
- [Coding Standards](#coding-standards)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)

---

## Code of Conduct

This project follows the [WordPress Community Code of Conduct](https://make.wordpress.org/handbook/community-code-of-conduct/). By participating you agree to abide by its terms. Be kind, be inclusive, be constructive.

---

## How Can I Contribute?

| Type | How |
|---|---|
| 🐛 Bug report | [Open an issue](../../issues/new?template=bug_report.md) |
| 💡 Feature request | [Open an issue](../../issues/new?template=feature_request.md) |
| 📝 Improve docs | Edit `README.md` or `CHANGELOG.md` and open a PR |
| 🔧 Fix a bug | Fork → branch → PR (see below) |
| ✨ Add a feature | Open an issue first to discuss, then PR |

---

## Getting Started (Local Setup)

### Prerequisites

- PHP 7.4+
- WordPress 5.8+ (local install — [WordPress Studio](https://developer.wordpress.com/studio/) recommended)
- WP-CLI (optional but helpful)
- Node.js + npm (only if editing `assets/block.js` with a build step in future versions)

### Steps

```bash
# 1. Fork this repo on GitHub, then clone your fork
git clone https://github.com/YOUR_USERNAME/wordcamp-certificate.git

# 2. Copy the plugin folder into your local WordPress install
cp -r wordcamp-certificate /path/to/wordpress/wp-content/plugins/

# 3. Activate the plugin
wp plugin activate wordcamp-certificate

# 4. Navigate to WC Certificates → Settings to configure your event details

# 5. Add [wordcamp_certificate] to a page and test the form
```

---

## Branch Naming Convention

| Type | Pattern | Example |
|---|---|---|
| Bug fix | `fix/short-description` | `fix/duplicate-email-check` |
| New feature | `feature/short-description` | `feature/csv-export` |
| Documentation | `docs/short-description` | `docs/update-readme` |
| Refactor | `refactor/short-description` | `refactor/ajax-handler` |
| Chore / config | `chore/short-description` | `chore/add-gitignore` |

---

## Commit Message Guidelines

We follow the [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) specification:

```
<type>(<scope>): <short summary>

[optional body]

[optional footer]
```

### Types

| Type | When to use |
|---|---|
| `feat` | A new feature |
| `fix` | A bug fix |
| `docs` | Documentation changes only |
| `style` | Formatting, missing semicolons, etc. (no logic change) |
| `refactor` | Code change that is neither a fix nor a feature |
| `test` | Adding or updating tests |
| `chore` | Build process, dependency updates, config |

### Examples

```
feat(form): add real-time email validation on blur
fix(certificate): prevent duplicate token on concurrent submissions
docs(readme): add Gutenberg block usage section
chore: add .gitignore and GitHub issue templates
```

---

## Pull Request Process

1. **Open an issue first** for non-trivial changes so we can discuss the approach before you invest time coding.
2. Fork the repository and create a branch from `main` using the naming convention above.
3. Make your changes. Keep commits focused — one logical change per commit.
4. Update `CHANGELOG.md` under the `[Unreleased]` section with a brief description of your change.
5. Update `README.md` if your change affects usage, configuration, or the file structure.
6. Make sure the plugin activates cleanly and the certificate form works end-to-end.
7. Open a Pull Request against the `main` branch. Fill in the PR template completely.
8. A maintainer will review your PR. Be prepared to make revisions based on feedback.
9. Once approved and merged, your change will appear in the next release. 🎉

---

## Coding Standards

This plugin follows the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).

### PHP

- Use tabs for indentation (not spaces)
- All functions, hooks, and globals must be prefixed with `wcc_`
- Sanitize all input: `sanitize_text_field()`, `sanitize_email()`, `absint()`, etc.
- Escape all output: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`, etc.
- Use `$wpdb->prepare()` for every database query with user-supplied data
- Add a docblock to every function

```php
/**
 * Retrieve a certificate record by token.
 *
 * @param string $token The certificate token.
 * @return object|null Database row or null if not found.
 */
function wcc_get_certificate_by_token( $token ) { ... }
```

### JavaScript

- Use `'use strict'` inside all IIFE wrappers
- Prefer `const` / `let` over `var` for new code
- Keep jQuery dependency isolated inside `(function ($) { ... }(jQuery))`

### CSS

- Use CSS custom properties (`--wcc-*`) for all theme colours
- Include a `@media (prefers-reduced-motion: reduce)` block for any animation or transition
- Mobile breakpoint: `max-width: 480px` for the form, `max-width: 600px` for the certificate

---

## Reporting Bugs

Before filing a bug, please:

1. Search [existing issues](../../issues) to avoid duplicates.
2. Test with the default WordPress theme (Twenty Twenty-Five) to rule out theme conflicts.
3. Deactivate other plugins and test again to rule out plugin conflicts.

When opening a bug report, include:

- WordPress version
- PHP version
- Plugin version
- Steps to reproduce
- Expected behaviour vs. actual behaviour
- Any relevant error messages or screenshots

---

## Suggesting Features

We love ideas! Before opening a feature request:

1. Check the [Unreleased section of CHANGELOG.md](CHANGELOG.md#unreleased) — it might already be planned.
2. Search [existing issues](../../issues) for similar requests.

When opening a feature request, describe:

- The problem you're trying to solve
- Your proposed solution
- Any alternatives you've considered

---

Thank you for contributing! 🙌
