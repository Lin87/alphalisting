# AGENTS.md — AlphaListing (WordPress plugin)

This file tells AI coding agents how to work safely and effectively in this repository.

---

## TL;DR for agents
- **What this is:** A WordPress plugin that displays posts/pages/terms alphabetically (A–Z index), with a Gutenberg block and a shortcode.
- **Don’t break:** The `[alphalisting]` shortcode args and behavior, the block’s user-facing options, translations (`textdomain: alphalisting`), and public PHP hooks/filters.
- **Runtime reqs:** PHP **≥ 8.0** with `mbstring` enabled. WordPress core up to the version listed in `readme.txt`.

---

## Project overview
- **Goal:** Provide an A–Z index/rolodex-style list of content. Includes both a Gutenberg block and a shortcode for backward compatibility.
- **Languages/tools:** PHP (plugin code), JavaScript (block/asset build), CSS, gettext translations.
- **Notable dirs/files:**
  - `alphalisting.php` — plugin bootstrap & headers.
  - `src/`, `templates/`, `widgets/` — main code.
  - `languages/` — translation files.
  - `functions/`, `test/`, `css/`, `scripts/` — helpers, tests, styles, build.
  - `Gruntfile.js`, `package.json`, `composer.json` — build manifests.
  - `readme.txt` — WordPress.org readme (canonical changelog lives here).
  - `.wordpress-org/` — **do not touch** (assets for WP.org distribution).

---

## Dev environment setup
1. **PHP & extensions**
   - PHP ≥ 8.0 with `mbstring` enabled.
   - Composer only needed for `symfony/polyfill-mbstring` and autoloading.
2. **Node.js & npm**
   - Use Node ≥ 18 and npm.
3. **Composer**
   ```bash
   composer install --no-interaction
   ```

**Initial install**
```bash
npm ci || npm install
composer install --no-interaction
```

---

## Build, watch, and assets
- **Canonical flow:** Run `@wordpress/scripts` first, then Grunt.

```bash
# JS build via @wordpress/scripts
npm run build     # production build
npm run start     # dev watch

# Then Grunt tasks (e.g. for styles/templates)
npx grunt build
npx grunt         # watch
```

**Notes for agents**
- Do **not** commit built JS/CSS assets (`dist/`, `build/`).
- If in doubt, prefer `npm run build` before `grunt`.

---

## Tests
- No PHPCS/ESLint/PHPUnit setup.
- There is a `test/` directory with some PHP tests (custom/manual).
- Agents may:
  - Run PHP linting for sanity:  
    ```bash
    find . -name "*.php" -not -path "./vendor/*" -print0 | xargs -0 -n1 php -l
    ```
  - Run or extend tests in `test/`.

---

## Backward compatibility
- **Shortcode `[alphalisting]`:** Must remain backward compatible. Arguments include:
  - `display`, `post-type`, `taxonomy`, `terms`, `parent-post`, `parent-term`, `get-all-children`, `alphabet`, `numbers`, `grouping`.
- **Block attributes:** Treat as public API. Avoid breaking changes; default new attributes safely.
- **Filters/actions:** If changing, deprecate gracefully and document.

---

## Internationalization (i18n)
- Text domain: `alphalisting`.
- Strings must be wrapped in WordPress i18n functions.
- Update `languages/` POT/PO/MO files if strings change.

---

## Commit & PR guidelines
- Use clear, atomic commits (Conventional Commits style preferred).
- For PRs, include:
  - What changed and why
  - Screenshots/GIFs for UI
  - Manual shortcode/block test notes
- **Version bump flow:**
  - Update version in `alphalisting.php`.
  - Update `Stable tag` and `Changelog` in `readme.txt`.
  - **Do not update `changelog.md`.**

---

## Packaging & release
- Builds are manual, no release script.
- Manual steps before release:
  1. `npm run build`
  2. Ensure `readme.txt` “**Tested up to**” and “**Requires PHP**” are accurate.
  3. Ensure `Stable tag` in `readme.txt` matches `alphalisting.php` version.
  4. Zip excluding dev/test files.  
     Example:  
     ```bash
     git archive -o alphalisting.zip HEAD
     ```
  5. Upload zip manually to WordPress.org SVN.

---

## Coding standards
- Follow **WordPress Coding Standards (WPCS)** as much as possible.
- Strict enforcement is not required, but consistency is preferred.

---

## Safe vs risky areas
- **Safe:** Internal helpers, new block controls (with safe defaults), new shortcode args (default off), added i18n, docs, tests.
- **Risky:** Changing shortcode default behavior, renaming args, altering template markup IDs/classes, removing public hooks, or changing alphabet/numbering logic.
- **Forbidden for agents:** Do **not** modify `.wordpress-org/` contents.

---

## Test & non-production files
- Put all experimental, debug, or test-only files inside the `test/` directory.
- These must not be included in distributable zips.

---

## Agent navigation tips
- Start at `alphalisting.php` for plugin entry.
- Use `src/`, `templates/`, `widgets/` for feature code.
- Consult `readme.txt` for shortcode usage and options.
