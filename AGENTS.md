# AGENTS.md — AlphaListing (WordPress plugin)

This file tells AI coding agents how to work safely and effectively in this repository.

---

## TL;DR for agents

- **What this is:** A WordPress plugin that displays posts/pages/terms alphabetically (A–Z index), via a Gutenberg block, a shortcode, and a widget.
- **Don't break:** The `[alphalisting]` shortcode attributes and behavior, the block's attributes, translations (`textdomain: alphalisting`), and public PHP hooks/filters — including their **legacy dashed aliases**.
- **Runtime reqs:** PHP **≥ 8.0** (`mbstring`, polyfilled by `symfony/polyfill-mbstring`), WordPress **≥ 6.0**, tested up to **7.1**.
- **Current version:** `4.6.0`. It must match in **three** places: the `Version:` header in `alphalisting.php`, `Stable tag` in `readme.txt`, and `version` in `package.json`.
- **There is no CI.** No `.github/` workflows exist — nothing runs on push. Local verification is the only gate.
- **No regressions.** Nothing here is covered by tests, so a regression ships silently. See **No regressions** below.

---

## Project overview

- **Goal:** Provide an A–Z index/rolodex-style list of content. The block, shortcode, and widget all render through the **same** code path.
- **Languages/tools:** PHP 8 (`declare(strict_types=1)`, PSR-4 autoloaded), JavaScript (block editor, built by `@wordpress/scripts`), SCSS/CSS, gettext.

### Layout

- `alphalisting.php` — bootstrap. Defines `ALPHALISTING_VERSION`, `ALPHALISTING_LOG`, `ALPHALISTING_PLUGIN_FILE`, `ALPHALISTING_DEFAULT_TEMPLATE`; loads `vendor/autoload.php` and the `functions/` + `widgets/` files; then `alphalisting_init()` on `init` priority **5** instantiates every singleton.
- `src/` — PSR-4 `eslin87\AlphaListing\`. Core: `Alphabet`, `Query`, `Indices`, `Grouping`, `Numbers`, `Strings`, `Shortcode`, `GutenBlock`, `Singleton`, `Extension`. Plus `src/Shortcode/` (`Query`, `PostsQuery`, `TermsQuery`, `Extension`) and `src/Shortcode/QueryParts/` (29 files — one per shortcode attribute, plus three abstract bases).
- `widgets/` — `class-alphalisting-widget.php` (also PSR-4 mapped).
- `functions/` — `enqueues.php`, `health-check.php`, `helpers.php`, `scripts.php`, `styles.php`.
- `templates/` — `a-z-listing.php` (the default, referenced by `ALPHALISTING_DEFAULT_TEMPLATE`) and `a-z-listing.example.php`.
- `scripts/` — block editor JS. `scripts/blocks/` (`index.js`, `edit.js`, `attributes.json`, `constants.js`, `shortcode-attributes.js`, `shortcode-upgrader.js`) and `scripts/components/`. Also `alphalisting-widget-admin.js`. **There is no `blocks/` directory at the repo root.**
- `css/` — `alphalisting-default.scss` and `alphalisting-customize.scss` are the sources compiled by `grunt sass`; `editor.css` and `style.css` are hand-authored.
- `languages/` — `alphalisting.pot` only. No `.po`/`.mo` files are tracked.
- `Gruntfile.js`, `package.json`, `composer.json` — build manifests.
- `readme.txt` — WordPress.org readme; the **canonical current changelog**.
- `changelog.md` — historical archive only. **Do not update it.**
- `README.md` — **generated** from `readme.txt` by `grunt readme`. Never hand-edit.
- `build/`, `vendor/`, `node_modules/` — gitignored; present locally, never committed.
- `.wordpress-org/` — **do not touch** (WP.org banners/icons/screenshots).

---

## Architecture: the query-part extension pattern

This is the single most important thing to understand before editing.

`src/Shortcode/Extension.php` is an abstract base (extending `Singleton`). A subclass declares three properties:

```php
public $attribute_name = 'group-by';
public $default_value  = '';
public $display_types  = array( 'posts' );
```

`Extension::initialize()` is `final` and derives all of its hook registrations from those properties:

- `alphalisting_get_shortcode_attributes` → contributes the attribute + default
- `alphalisting_sanitize_shortcode_attribute__{attribute_name}`
- `alphalisting_shortcode_query_for_attribute__{attribute_name}`
- `alphalisting_shortcode_query_for_display__{display}__and_attribute__{attribute_name}` (one per entry in `$display_types`)

It also hooks `alphalisting_shortcode_start` → `handler()` and `alphalisting_shortcode_end` → `cleanup()`, so hooks added during a render are unwound afterwards.

**To add a shortcode attribute:**

1. Add a class to `src/Shortcode/QueryParts/`, overriding `sanitize_attribute()` and/or `shortcode_query()` / `shortcode_query_for_display_and_attribute()`.
2. Register it in `alphalisting_init()` in `alphalisting.php` as `ClassName::instance()->activate( __FILE__ )->initialize();`. **Append it; don't reorder the list.** Registration order is the order the keys land in the shortcode attribute array, which is the order `Shortcode\Query::apply_query_to_shortcode()` processes them in.
3. Add a matching entry to `scripts/blocks/attributes.json` so the block exposes it.
4. Add the editor control to the fitting `PanelBody` in `scripts/blocks/edit.js` — *Listing selection* (what is listed), *Display options* (how items are bucketed, ordered, and labelled), or *Layout* (columns) — or to the relevant `scripts/components/*` panel. Those three are the only panels; each wraps a `*.Slot` render-prop for third-party fills.

Copy `QueryParts/BackToTop.php` (simple, boolean-ish) or `QueryParts/GroupBy.php` (enum affecting sorting) as your model. Copy `QueryParts/HideEmpty_Deprecated.php` as the model for deprecating an attribute. For an attribute that only needs to exist and be read from `$attributes` elsewhere, copy `QueryParts/Target.php` — a declaration-only part that overrides nothing.

---

## The block

There is **no `block.json`**. Registration is PHP-side in `src/GutenBlock.php`:

- `register_block_type( 'alphalisting/block', … )`
- `attributes` are `json_decode`d from `scripts/blocks/attributes.json` (26 entries), then passed through the `alphalisting_get_gutenberg_attributes` filter.
- `editor_script` `alphalisting-block-editor` loads `build/index.js` and reads its dependency list from `build/index.asset.php` — it **throws `\Error` if that file is missing**, so `npm run build` must have been run or the editor breaks.
- `editor_style` ← `css/editor.css`; `style` ← `css/alphalisting-default.css`.
- `render_callback` delegates to the registered `alphalisting` shortcode callback — **block and shortcode share one render path**, so a shortcode change is a block change.

`scripts/blocks/shortcode-upgrader.js` migrates legacy shortcode blocks into real blocks; changing it can orphan existing content.

---

## Alphabet: headings vs. ordering

`src/Alphabet.php` is built from a comma-separated string (`AÁÀÄÂaáàäâ,Bb,CÇcç,…`). Every character
in a comma-group maps to that group's first character, which is what puts `a` and `Á` under the
same `A` heading.

That map answers **which heading an item belongs to**. It cannot also answer **how to order items
within a heading**, because `src/Grouping.php` implements `grouping="3"` by *concatenating*
consecutive comma-groups into one (`Aa,Bb,Cc` → `AaBbCc`). Collapsing is correct for the heading and
wrong for the order: with everything in the group flattened to `A`, `Alphabet::compare_strings()`
sees `apple` as `A,P,P` and `banana` as `A,A,M`, ties on the first character, and lets the second
decide — so `banana` sorts first. Same for the `0-9` heading from `grouping="numbers"`.

So `Alphabet` keeps **two** maps:

| Purpose | Properties | Used by |
|---|---|---|
| Heading / bucketing — letters merged | `alphabet_keys`, `keyed_alphabet`, `get_letter_for_key()` | `src/Indices.php`, the letter list, `get_key_for_offset()`, `chars()`, `loop()` |
| Ordering — letters kept apart | `sorting_keys`, `sorting_keyed_alphabet`, `sorting_positions` | `compare_strings()` only |

The second is fed by the `alphalisting_sorting_alphabet` filter, applied to the *final* alphabet.
It defaults to the alphabet itself, in which case both maps are the same array and behaviour is
unchanged. `Grouping` and `Numbers` hook it to hand back their un-merged groups, each replacing only
the groups it created so the result does not depend on filter priority.

Anything that merges letters into one heading must hook this filter too, or ordering under that
heading breaks. Do not "fix" ordering with a blanket re-sort in `Query::the_letter()` — 4.3.2 did
that with `strcasecmp` and it had to be removed because it clobbered `group-by="last-word"`. Fixing
it in `compare_strings()` corrects the default comparator, `GroupBy`, and third-party
`alphalisting_item_sorting_comparator` callbacks that delegate to it, all at once.

---

## Index letter vs. displayed title

**The indexed title, the sort key, and the displayed link text are all one stored string.**
`Indices::get_item_indices()` stores the post-`alphalisting_pre_index_item_title` title in the
index entry's `'title'`; `Query::get_all_indices()` sorts on that same value, and
`Query::get_the_title()` reads it back to render the link. So
`alphalisting_pre_index_item_title` is **not** the hook for an indexing-only transform — it
rewrites the visible link text too.

The indexing-only seam is `alphalisting_item_index_letter`: it receives the title, but the
letter it returns does not write back to the stored title. Pair it with
`alphalisting_item_sorting_comparator`:

| Filter | Decides |
|---|---|
| `alphalisting_item_index_letter` | **which heading** the item files under |
| `alphalisting_item_sorting_comparator` | **the order within** that heading |

**Hooking one without the other yields a listing whose letters disagree with its order.** The
comparator only reorders *inside* a bucket that the index filter already assigned, so a "sort
by X" attribute that skips the index filter sorts items that are still in the wrong letter.
`QueryParts/GroupBy.php` (`group-by="last-word"`) and `QueryParts/IgnoreArticles.php`
(`ignore-articles`) are the two worked examples — copy either.

Further gotchas for anything on this pair:

- **They collide.** `GroupBy` and `IgnoreArticles` both hook these two filters, and
  `IgnoreArticles` is registered later in `alphalisting_init()`, so it would silently win.
  It deliberately stands down when `group-by="last-word"` is set, rather than racing it; the
  block editor disables its control to match. A third attribute here must resolve the same
  conflict explicitly.
- **Empty attribute values never reach the query filters at all.**
  `Shortcode\Query::apply_query_to_shortcode()` skips `empty( $value )`, so a query part with
  a falsey `$default_value` is inert by construction — and one with a non-empty default (like
  `back-to-top`) fires on *every* listing. Prefer the former for anything new.
- **Set a `$query['_alphalisting_<name>']` marker** for anything that changes bucketing or
  ordering, or an external cache will serve the default listing in its place.
- **Per-render state belongs in `handler()`.** `Extension::cleanup()` is `final` and only
  unhooks; it does not reset your properties. `alphalisting_shortcode_start` → `handler()`
  fires *before* attributes are sanitized and the query is built, so reset there.
- **The default comparator lowercases with `strtolower()`, not `mb_strtolower()`** — an
  ASCII-only wart in `Query::get_all_indices()`. It does not bite `IgnoreArticles` because
  every article in all four supported languages is ASCII; a language with an accented article
  would need its own case folding.
- **Do not add `remove_accents()` or transliteration** to letter logic. The alphabet string
  already groups accents with their base letter (`EÉÈËÊeéèëê`), so `Étranger` buckets and
  sorts under E for free. Transliterating diverges from the alphabet the rest of the plugin
  sorts by, and breaks a custom `alphabet=""` that files accents separately on purpose.
- **Elided articles carry no space, and apostrophes come in several characters.**
  `L'Étranger` and `Un'Altra Vita` are a single whitespace token, so a first-word split files
  them under L and U. `IgnoreArticles` matches `l'`/`un'` *before* the word-split branch,
  against a class covering `'` (U+0027), `’` (U+2019 — what `wptexturize` stores in saved
  post titles, so it is the common case) and `ʼ` (U+02BC), with an optional trailing space.
  Normalise for the match only and cut the remainder out of the original string, so the
  displayed title keeps its own apostrophe. The inverse matters too: only a leading *article*
  is elided, so `O'Brien Hall` must survive untouched.

---

## Dev environment setup

PHP ≥ 8.0 with `mbstring`; Node ≥ 18; Composer.

```bash
npm ci || npm install
composer install --no-interaction
```

---

## Build, watch, and assets

`npm run build` already chains Grunt — do **not** run them as two separate steps.

```bash
npm run build          # wp-scripts build ./scripts/blocks/index.js && grunt
npm run build:release  # npm run build && composer makepot — use at release time only
npm run start          # dev watch — JS only; does NOT run grunt/sass
npx grunt              # one-shot: addtextdomain + readme + sass. NOT a watch task.
npx grunt sass         # recompile css/*.scss only
npx grunt readme       # regenerate README.md from readme.txt
npm run package        # release zip — see Packaging & release. Do NOT use for everyday builds.
```

**Notes for agents**

- Use `npm run build` for everyday work. POT regeneration is deliberately **not** part of it: `make-pot` rewrites `POT-Creation-Date` on every run, so bundling it would leave `languages/alphalisting.pot` dirty after every build and train you to discard changes that sometimes matter. It would also make the JS build require PHP + `composer install`.
- `npm run build:release` is the release-time superset. `npm run makepot` regenerates the POT alone (an alias for `composer makepot`).
- Do **not** commit built assets (`build/`, `vendor/`, compiled `css/*.css` beyond what's already tracked).
- `grunt readme` overwrites `README.md`. Edit `readme.txt` and regenerate.
- `npm run package` is release-only. It swaps `vendor/` to a no-dev install mid-run and swaps it back — don't reach for it to check that a build works.

---

## No regressions

There is no test suite and no CI, so nothing will catch a regression for you — it ships to
WordPress.org and users find it. **A change must not break existing behaviour.** Treat this as part
of the task, not a nice-to-have: a fix that trades one bug for another is not done.

Before calling a change finished:

- **Know what you are changing the behaviour of.** Read the code path end to end and list every
  caller of what you touched. Shared code paths are the trap — the block and the shortcode render
  through the same callback, and `Alphabet` serves both headings and ordering.
- **Prove the old behaviour still holds**, not just that the new behaviour works. Exercise the
  *unchanged* configuration as deliberately as the fixed one, and diff the before/after output.
  Running the same check against `git stash`ed or `git show HEAD:`-extracted source, from a temp
  directory outside the repo, is the cheapest way to do this.
- **Prefer changes that are inert by default.** A new filter that defaults to today's value, or a
  branch that reuses the existing object when nothing opted in, cannot regress the common case.
- **Re-read the diff** for accidental scope: a renamed property, a changed default, a moved hook, a
  helper that is now shared by a second caller.
- **Check the risky list** under **Safe vs risky areas**, and the public API under **Backward
  compatibility**, before you touch anything on them.

If you cannot verify a part of the change, say so explicitly in the PR description rather than
implying it was tested.

---

## Verification & linting

**This repository has no test suite, and agents must not add one.** There is no PHPUnit, jest, PHPCS, `npm test`, or `test/` directory. Do **not** create test files, test directories, scratch harnesses, debug scripts, or `verify-*.php`-style throwaways anywhere in the repo — not in a new `test/`, not alongside the code they exercise. If you need to execute something to convince yourself a change works, run it from a temp directory outside the repo and delete it when you're done.

Verify changes by:

- Reading the affected code path end to end (`src/Shortcode.php` → `src/Query.php` → `templates/a-z-listing.php`).
- The PHP syntax sweep below.
- The lint scripts below.
- Manual testing in a real WordPress install — render the shortcode and the block, and note what you exercised in the PR description.

Linting exists (via `@wordpress/scripts` defaults — there is no `.eslintrc`/`.stylelintrc`):

```bash
npm run lint:js        # eslint over ./scripts
npm run lint:css       # ./css/editor.css ./css/style.css only
npm run lint:pkg-json
npm run format:js
```

Nothing enforces lint in CI, so the baseline is already dirty: as of 4.5.1 `lint:js` reports **~1713 problems**, almost all `prettier/prettier` "Delete `␍`" CRLF line-ending errors. Do not run `--fix` across `scripts/` to clean this up — it would rewrite every file and bury your actual change. Compare against the baseline instead of expecting zero.

PHP syntax sanity sweep:

```bash
find . -name "*.php" -not -path "./vendor/*" -not -path "./node_modules/*" -print0 | xargs -0 -n1 php -l
```

---

## Backward compatibility

### Shortcode `[alphalisting]`
Registered in `src/Shortcode.php`. `Shortcode::handle()` seeds **no** attributes of its own — every one is contributed by a `QueryParts` class. All of these are public API:

- **Any display:** `display`, `return`, `alphabet`, `numbers`, `group-numbers`, `grouping`, `symbols-first`, `back-to-top`, `target`, `instance-id`, `columns`, `column-width`, `column-gap`
- **`display="posts"`:** `post-type`, `parent-post`, `terms`, `exclude-posts`, `exclude-terms`, `get-all-children`, `group-by`, `ignore-articles`
- **`display="terms"`:** `taxonomy`, `terms`, `parent-term`, `parent-term-id`, `exclude-terms`, `hide-empty-terms`, `hide-empty` *(deprecated)*

Special cases worth knowing: `return="letters"` renders only the `<div class="az-letters">` block; `grouping="numbers"` is special-cased; `target` accepts a post ID or a URL.

### Block attributes
Treat `scripts/blocks/attributes.json` as public API. Add new attributes with safe defaults; never rename or remove one without an upgrade path.

### Hooks
Do not rename or delete public hooks. Retire them with `apply_filters_deprecated()` (see the existing `alphalisting_term_indices`, `alphalisting_post_indices`, `alphalisting_item_indices`, `az_sections`).

Many filters ship a **legacy dashed alias** alongside the underscored name (`alphalisting-query` / `alphalisting_query`, `alphalisting-sections` / `alphalisting_sections`, `alphalisting-item-index-letter`, `the-a-z-letter-title`, …). Both must keep firing.

Hook families and where they live:

| Area | File | Examples |
|---|---|---|
| Shortcode attributes & query | `src/Shortcode/Query.php`, `src/Query.php` | `alphalisting_get_shortcode_attributes`, `alphalisting_sanitize_shortcode_attributes`, `alphalisting_shortcode_query_types`, `alphalisting_query` |
| Items & indices | `src/Indices.php`, `src/Shortcode/QueryParts/IgnoreArticles.php` | `alphalisting_get_item_title_for_display__{type}`, `alphalisting_pre_index_item_title`, `alphalisting_item_index_letter`, `alphalisting_articles_for_language` |
| Alphabet | `src/Alphabet.php` | `alphalisting_alphabet`, `alphalisting_sorting_alphabet`, `alphalisting_non_alpha_char`, `alphalisting_unknown_letter_is_first` |
| Sorting & sections | `src/Query.php` | `alphalisting_item_sorting_comparator`, `alphalisting_sections`, `alphalisting_extract_item_indices` |
| Block | `src/GutenBlock.php` | `alphalisting_get_gutenberg_attributes` |
| Styles/output | `functions/enqueues.php`, `templates/` | `alphalisting_add_styling`, `alphalisting_styles`, `alphalisting_show_back_to_top` |
| Actions | `src/Shortcode.php`, `src/Query.php` | `alphalisting_shortcode_start`, `alphalisting_shortcode_end`, `alphalisting_save_cache`, `alphalisting_log` |

Grep before you assume — the list above is representative, not exhaustive.

---

## Internationalization (i18n)

- Text domain: `alphalisting`. Domain path `/languages`.
- All user-facing strings — **PHP and block editor JS** — must be wrapped in WP i18n functions.
- Regenerate the POT with:
  ```bash
  npm run makepot     # or: composer makepot
  ```
  This uses WP-CLI's `i18n make-pot`, which scans **both PHP and JS**. It is *not* part of `npm run build` — see **Build** for why.
- **Always pass the domain explicitly:** `__( 'Foo', 'alphalisting' )`. `make-pot` silently drops strings with a missing *or* wrong domain — it still reports `Success:` and exits 0, so a forgotten domain produces no error, just a missing translation.
- `grunt i18n` runs `addtextdomain`, which rewrites a missing domain into **PHP** source as a safety net. It does **not** cover JS — a domainless `__()` in `scripts/` will vanish from the POT with nothing to catch it. Keep `grunt-wp-i18n` installed for this task.
- `addtextdomain` and `composer makepot` do not conflict — the first writes PHP source, the second writes only the POT. But **run `addtextdomain` first**: `make-pot` reads source, so a domain fix made afterwards won't reach the POT until you regenerate.
- `addtextdomain` is configured with `updateDomains: true`, so it **replaces** any existing textdomain with `alphalisting`. Never reference a string from another domain (e.g. WP core's `'default'`) in PHP here — it will be silently rewritten and then wrongly extracted into our POT.
- `make-pot` is not byte-idempotent: every run rewrites `POT-Creation-Date`, so the POT always shows as modified. If that header is the only diff, discard it.
- **Gotcha:** `grunt-wp-i18n`'s other task, `makepot`, scans PHP only and will silently drop every JS string. It has been removed from the `grunt i18n` task for exactly that reason — do not re-add it, and do not regenerate the POT with Grunt.
- Only `alphalisting.pot` is tracked. Translations live on translate.wordpress.org; do not create `.po`/`.mo` files here.

---

## Commit & PR guidelines

- Clear, atomic commits (Conventional Commits style preferred).
- PRs should include what changed and why, screenshots/GIFs for UI, and manual shortcode/block test notes.
- **Version bump flow:**
  1. `Version:` header in `alphalisting.php`
  2. `Stable tag` in `readme.txt` (plus `Tested up to` / `Requires PHP` / `Requires at least` if they moved)
  3. `version` in `package.json`
  4. New changelog entry in `readme.txt`
  5. `npm run build:release` — builds, regenerates `README.md` from `readme.txt`, and refreshes `languages/alphalisting.pot`
  6. **Do not update `changelog.md`.**

---

## Packaging & release

```bash
npm run package   # build + POT + no-dev vendor + zip + restore dev vendor
```

Produces `alphalisting.zip` at the repo root, with an `alphalisting/` root folder inside — the shape
WordPress.org expects. Requires PHP, Composer, and a prior `composer install`.

1. Do the **version bump flow** above first.
2. Confirm `readme.txt` **Tested up to**, **Requires PHP**, **Requires at least** are accurate.
3. Confirm `Stable tag` == `alphalisting.php` version == `package.json` version.
4. `npm run package`. The last step verifies the archive and prints a summary:

   ```text
   Package verified: 82 files, 434 KB uncompressed.
     15 runtime asset references resolved
     11 required files present
     no dev dependencies or build source leaked
   ```

   If verification fails it **deletes the zip** and exits non-zero, so a broken archive is never left
   lying around to be uploaded by mistake.
5. Upload manually to the WordPress.org SVN repo.

**How it works, and what not to break**

- `npm run package` chains five steps: `build:release` → `package:vendor:prod` → `package:zip` → `package:vendor:dev` → `package:verify`. The order is load-bearing: `composer makepot` inside `build:release` needs the **dev** `vendor/`, so the no-dev swap has to come after it and the restore before verification (that way `vendor/` is always restored even when verification fails).
- If the run fails partway, `vendor/` may be left in its no-dev state — the giveaway is `composer makepot` failing on the next run. Recover with `npm run package:vendor:dev`.
- **Never run `npm run package:zip` on its own to produce a release.** It zips whatever is on disk, so with a normal dev `vendor/` present it produces a ~5 MB archive containing all of WP-CLI. `package:verify` now catches this, but the sub-scripts exist for recovery, not shortcuts — always go through `npm run package`.
- `bin/verify-package.mjs` inspects the built zip and fails the build on: a plugin-relative asset path referenced by shipped PHP but absent from the archive, a missing entry from its `MUST_SHIP` list, or anything matching `MUST_NOT_SHIP` (dev Composer packages, `vendor/bin/`, webpack source, `.scss`, repo tooling). **When you add a file the plugin reads at runtime, add it to `files` in `package.json` — and if it is critical, to `MUST_SHIP` as well.**
- Only **one** Composer package ships: `symfony/polyfill-mbstring`. Everything else in `vendor/` is dev tooling pulled in by `wp-cli/i18n-command`. Never prune `vendor/` by hand — `vendor/composer/autoload_files.php` force-requires dev bootstrap files, so deleting directories leaves dangling requires and a fatal. `composer install --no-dev --optimize-autoloader` regenerates the maps correctly.
- `vendor/` must still **ship**: `alphalisting.php` requires `vendor/autoload.php` unguarded, and that autoloader is what provides the plugin's own `eslin87\AlphaListing\` PSR-4 map.
- The zip contents are the `files` allowlist in `package.json`. Add new shipped directories there.
- **`scripts/` is build source, but two files in it are read at runtime and MUST ship.** Removing either fatals the site on activation:
  - `scripts/blocks/attributes.json` — `file_get_contents()`d by `src/GutenBlock.php`; if absent, `json_decode(false)` throws a `TypeError` on every page load.
  - `scripts/alphalisting-widget-admin.js` — enqueued by `functions/enqueues.php`.
- More generally: **any file PHP reads or enqueues at runtime must be in the allowlist**, even if it lives in a directory that is otherwise build-time only. `package:verify` enforces this automatically by scanning shipped PHP for quoted asset paths, so you no longer have to remember — but it can only see *literal* paths. A path built at runtime from a variable is invisible to it; add those to `MUST_SHIP` by hand.
- **Do not delete `.npmignore`, and do not add `vendor` or `build` to it.** `wp-scripts plugin-zip` uses npm-packlist, where `.gitignore` outranks the `files` allowlist — and `.gitignore` lists `/vendor/` and `/build/`. npm-packlist disables `.gitignore` whenever `.npmignore` has rules, so that file's existence is the only thing keeping releases correct. The file itself explains this too.
- `git archive` is **not** a valid packaging method here; it omits `build/` and `vendor/`.

---

## Coding standards

- Follow **WordPress Coding Standards (WPCS)** where practical. Strict enforcement is not required; consistency is.

## Code style requirements

- **Indentation:** use **4 spaces** for new files. **Existing `src/` files are tab-indented** — when editing one, match the surrounding file rather than mixing the two. (Mixed indentation has already crept into `alphalisting.php` and `src/Shortcode/Extension.php`; don't add more.)
- **Curly braces:** opening `{` on the same line as the declaration:
    ```php
    function my_example($arg) {
        if ($arg) {
            echo "Hello";
        }
    }
    ```
- This brace/indent style takes priority over WPCS where they disagree.

---

## Safe vs risky areas

- **Safe:** internal helpers, new `QueryParts` attributes with off-by-default values, new block controls with safe defaults, added i18n, docs.
- **Risky:**
  - `src/Shortcode/Extension.php` — changes every attribute at once.
  - The shared `render_callback` in `src/GutenBlock.php` — one change hits block *and* shortcode.
  - `scripts/blocks/shortcode-upgrader.js` — migrates existing content.
  - `alphalisting_item_sorting_comparator`, `src/Alphabet.php`, and index-letter logic — subtle ordering regressions. See **Alphabet: headings vs. ordering** and **Index letter vs. displayed title**.
  - Renaming shortcode args, changing defaults, altering template markup IDs/classes, removing public hooks.
- **Forbidden for agents:** do **not** modify `.wordpress-org/` contents.

---

## No test or scratch files

Every file committed to this repository must be part of the shipped plugin (or its build/docs tooling). Do not add:

- test files, test directories, or test harnesses of any kind;
- debug, scratch, experiment, or "verify-*" scripts;
- example/sandbox PHP that isn't a real template (`templates/a-z-listing.example.php` is the one sanctioned example file).

A `test/` directory previously existed and was removed deliberately. Do not recreate it. If a change genuinely needs automated coverage, raise it with the maintainer first rather than committing a harness.

---

## Agent navigation tips

- Start at `alphalisting.php` → `alphalisting_init()` to see everything that is wired up.
- Attribute behavior lives in `src/Shortcode/QueryParts/<Name>.php`. Grep the attribute name to find its class.
- Rendering: `src/Shortcode.php` → `src/Query.php` → `templates/a-z-listing.php`.
- Block UI: `scripts/blocks/edit.js` + `scripts/components/`.
- `readme.txt` documents shortcode usage and options for end users.
