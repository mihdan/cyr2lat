# Cyr-To-Lat 7.0 Development Plan

## 1. Purpose

Version 7.0 should move Cyr-To-Lat from a broad `sanitize_title`-based implementation to an explicit, data-type-oriented architecture.

The current plugin still works, but the main transliteration entry point is too old and too wide: `sanitize_title` is called by WordPress and plugins in many unrelated contexts. As a result, the plugin has to guess whether the value being sanitized is a real slug, a WooCommerce attribute, a query value, an old slug redirect, a frontend product attribute lookup, or something else.

The goal of 7.0 is not to change transliteration results. The goal is to keep the same visible behavior while moving each supported data type to the most specific available hook or processing point.

Version 7.0 should be treated as an architecture migration, not as a large feature release.

## 2. Current behavior inventory

Based on the current code and readme, Cyr-To-Lat currently transliterates or protects the following data types.

### 2.1 New post slugs

Current mechanism:

- Global `sanitize_title` filter.
- Additional Gutenberg support through `wp_insert_post_data` in `Main::sanitize_post_name()` when `post_name` is empty and `post_title` is present.
- Old slug redirect protection through `post_updated` / `Main::check_for_changed_slugs()`.

Current scope:

- Posts.
- Pages.
- Public/custom post types where WordPress creates a `post_name`.
- WooCommerce products, because they are posts of type `product`.
- Attachments as post objects when their `post_name` is generated.
- Navigation menu items when included in background conversion settings.

### 2.2 Existing post slugs

Current mechanism:

- Converter page background process.
- WP-CLI command: `wp cyr2lat regenerate`.
- Configurable post types through the `ctl_post_types` filter.
- Configurable post statuses from plugin settings.

Current scope:

- Any selected post type in background conversion settings.
- Attachments are handled specially because they use `inherit` status.

### 2.3 New term slugs

Current mechanism:

- `pre_insert_term` marks the next `sanitize_title` call as a term context.
- `get_terms_args` also marks a term context for some term queries.
- `sanitize_title` then transliterates unless the value is an existing encoded term slug or a WooCommerce attribute that must be preserved.

Current scope:

- Categories.
- Tags.
- Custom taxonomies.
- WooCommerce product categories and product tags.
- WooCommerce attribute terms, indirectly, depending on the taxonomy and call path.

### 2.4 Existing term slugs

Current mechanism:

- Converter page background process.
- WP-CLI command.
- Direct query against `terms` and `term_taxonomy`.
- Existing term conversion explicitly excludes WooCommerce global attribute taxonomies matching `^pa_.*$`.

Current scope:

- Terms in non-attribute taxonomies.
- WooCommerce product categories and tags.
- Custom taxonomy terms.
- Not global WooCommerce attribute terms under `pa_*` taxonomies.

### 2.5 Attachment file names

Current mechanism:

- `sanitize_file_name` filter.
- `ctl_pre_sanitize_filename` short-circuit filter.
- Lowercasing for UTF-8 filenames before transliteration.

Current scope:

- Uploaded media files.
- Any WordPress upload path using `sanitize_file_name`.

### 2.6 WooCommerce global attributes

Current mechanism:

- Mostly guarded inside `Main::sanitize_title()`.
- `Main::is_wc_attribute_taxonomy()` checks registered WooCommerce attribute taxonomies.
- `Main::is_wc_attribute()` prevents transliteration for some attribute identifiers because WooCommerce expects exact attribute names/slugs in specific contexts.

Current problem:

- Attribute taxonomies are not normal terms from the plugin’s point of view.
- Attribute slugs are stored and registered by WooCommerce separately from ordinary term slugs.
- The current approach is defensive and context-dependent.

### 2.7 WooCommerce local product attributes

Current mechanism:

- `Main::is_local_attribute()` inspects request actions and posted fields:
  - `woocommerce_save_attributes` AJAX action.
  - `editpost` product save action.
  - variable product add-to-cart flow.
  - cart/session loading flow.
  - encoded `attribute_*` request fields.

Current problem:

- This is the clearest symptom that `sanitize_title` is too broad.
- Local attribute names are sometimes real user-facing labels, sometimes request keys, sometimes variation attribute identifiers.
- The same value must be transliterated in one context and preserved in another.

### 2.8 Frontend WooCommerce attribute rendering and variation matching

Current mechanism:

- On frontend only, the plugin temporarily adds `sanitize_title` before WooCommerce template rendering and removes it after template rendering.
- This was added to support WooCommerce attributes on frontend templates.

Current problem:

- Template boundaries are not precise data boundaries.
- It is very easy to miss AJAX, Store API, block templates, custom themes, shortcodes, and non-template WooCommerce flows.

### 2.9 Multilingual handling

Current mechanism:

- Polylang locale detection through REST request data, classic editor post language, and term language.
- WPML locale detection through `wpml_get_current_language()` and `wpml_active_languages`.
- Transliteration table selected through plugin settings and `ctl_locale` / `ctl_table` filters.

Current scope:

- Must be preserved in 7.0.
- Hook refactoring must not bypass locale selection.

### 2.10 Public extension points

Current filters that must remain compatible:

- `ctl_table`
- `ctl_locale`
- `ctl_pre_sanitize_title`
- `ctl_pre_sanitize_filename`
- `ctl_allow`
- `ctl_post_types`

7.0 may add new filters, but should not silently break these filters.

## 3. Version 7.0 target architecture

### 3.1 Core principle

Introduce explicit slug processors instead of one global title processor.

Recommended structure:

```text
src/php/
  Transliteration/
    Transliterator.php
    Context.php
    Result.php
  Slugs/
    PostSlugService.php
    TermSlugService.php
    FilenameService.php
    OldSlugRedirectService.php
  WooCommerce/
    WooCommerceService.php
    GlobalAttributeService.php
    LocalAttributeService.php
    VariationAttributeService.php
  Compatibility/
    LegacySanitizeTitleBridge.php
    MultilingualService.php
  Background/
    PostSlugConversionJob.php
    TermSlugConversionJob.php
    AttributeConversionJob.php
```

This does not require a full namespace move in one pull request. It is a target direction.

### 3.2 Transliteration API

Create one internal API that all handlers use:

```php
$slug = $this->slug_generator->generate_from_text(
    $raw_value,
    new Context(
        type: 'post|term|filename|wc_global_attribute|wc_local_attribute|wc_variation_attribute',
        object_id: $id,
        object_type: $post_type_or_taxonomy,
        locale: $locale,
        source: 'admin|rest|cli|frontend|ajax'
    )
);
```

The API should:

- Apply the active transliteration table.
- Preserve `ctl_table` and `ctl_locale`.
- Preserve old pre-filters where possible.
- Apply WordPress-compatible final slug cleanup.
- Be testable without WordPress request globals.

### 3.3 Backend-first rule

Cyr-To-Lat is a backend data-normalization plugin, not a UI plugin.

The 7.0 architecture should normalize data at backend entry points:

- WordPress post save.
- WordPress term creation/update.
- WordPress media upload pipeline.
- WordPress REST API.
- WooCommerce CRUD/API/admin save flows.
- WooCommerce frontend variation/cart matching where required.
- WP-CLI/background conversion.

Browser behavior should not be the primary contract. If data comes from Gutenberg, Classic Editor, Quick Edit, WP-CLI, REST, importers, or another plugin, the backend should still normalize it correctly.

## 4. Hook migration plan

### 4.1 Posts and custom post types

Preferred hook:

- Keep `wp_insert_post_data`, but make it the primary post slug handler.

Handler behavior:

- Run only when saving a post object.
- Use `$data`, `$postarr`, `$unsanitized_postarr`, and `$update` when available.
- If `post_name` is empty, generate from raw post title.
- If `post_name` was manually supplied, transliterate only when it contains non-Latin characters and only if the plugin setting allows manual slug normalization.
- Skip autosaves, revisions, auto-drafts, and REST autosave payloads.
- Support block editor, classic editor, REST API, XML-RPC, WP-CLI, and programmatic `wp_insert_post()`.

Implementation notes:

- Change callback registration to accept 4 args where supported:

```php
add_filter( 'wp_insert_post_data', [ $post_slug_service, 'filter_post_data' ], 10, 4 );
```

- Keep `sanitize_post_name()` temporarily as a wrapper or deprecate it after moving logic.
- Add tests for posts, pages, products, custom post types, and attachments-as-posts.

### 4.2 Gutenberg / block editor save paths

Gutenberg should be covered through REST integration tests, not Playwright.

The block editor normally saves through WordPress REST endpoints. Version 7.0 should therefore verify the REST/backend behavior directly.

Required REST coverage:

- Create post through `/wp/v2/posts` with Cyrillic title and empty slug.
- Create page through `/wp/v2/pages` with Cyrillic title and empty slug.
- Create custom post type through its REST route where REST support is enabled.
- Update draft through REST and verify slug behavior.
- Publish draft through REST and verify final slug.
- Update post through REST with explicit Cyrillic slug.
- Update post through REST with explicit Latin/manual slug.
- Verify manual slug is not unexpectedly overwritten.
- Verify autosave/revision requests do not create broken slugs.
- Verify duplicate Cyrillic titles produce unique Latin slugs.

Example test shape:

```php
$request = new WP_REST_Request( 'POST', '/wp/v2/posts' );
$request->set_body_params(
    [
        'title'  => 'Тестовая запись',
        'status' => 'publish',
    ]
);
$response = rest_do_request( $request );
$post_id  = $response->get_data()['id'];
$post     = get_post( $post_id );

$this->assertSame( 'testovaya-zapis', $post->post_name );
```

Decision:

- Do not add Playwright as a required 7.0 dependency.
- Add Playwright later only if a real UI-only bug is discovered.

### 4.3 Old post slug redirects

Preferred hook:

- Keep `post_updated` behavior, but move it out of `Main`.

Handler behavior:

- Preserve the current `_wp_old_slug` protection.
- Ensure that automatically generated transliterated slugs do not create broken redirects from the original Cyrillic title.
- Do not create old slug entries for hierarchical post types unless existing behavior intentionally requires it.

Implementation notes:

- Extract to `OldSlugRedirectService`.
- Add regression tests for empty previous `post_name`, published posts, attachments, and hierarchical post types.

### 4.4 Terms and taxonomies

Preferred hooks:

- `pre_insert_term` to capture raw term input and taxonomy.
- `pre_term_slug` for direct slug filtering.
- Possibly `created_term` / `edited_term` only for recovery or migration, not primary slug generation.

Handler behavior:

- Generate term slugs only for actual term creation/update paths.
- Do not rely on unrelated `sanitize_title` calls to infer term context.
- When a slug is explicitly supplied in `$args['slug']`, transliterate that slug directly.
- When no slug is supplied, generate from the raw term name.
- Preserve existing encoded slugs where WordPress/WPML/Polylang need them.
- Avoid touching frontend term query values.

Implementation notes:

- Replace `$this->is_term` and `$this->taxonomies` state flags with a scoped term context object.
- Add a narrow fallback only if WordPress core still routes a term slug through `sanitize_title` without exposing enough information.
- Add tests for category, post tag, custom taxonomy, WooCommerce product category, WooCommerce product tag, and multilingual term creation.

### 4.5 Attachment filenames

Preferred hook:

- Keep `sanitize_file_name`.

Handler behavior:

- This is already a reasonably specific hook.
- Move logic from `Main::sanitize_filename()` to `FilenameService`.
- Preserve `ctl_pre_sanitize_filename`.
- Preserve macOS normalization handling and UTF-8 lowercasing.

Implementation notes:

- Add tests with Cyrillic, Greek, Georgian, Hebrew, Chinese, mixed Latin/Cyrillic, spaces, dots, and composed/decomposed Unicode forms.
- Add a test for WordPress 6.9+ `wp_is_valid_utf8` behavior if test matrix supports it.

### 4.6 WooCommerce global attributes

Preferred hooks / APIs to investigate and use:

- WooCommerce attribute creation/update functions and admin handlers around `wc_create_attribute()` / attribute taxonomy management.
- Attribute taxonomy registration lifecycle.
- Product attribute taxonomy term creation should still be handled through the term service where taxonomy is `pa_*`.
- WooCommerce REST API for product attributes, if supported in the tested version.

Target behavior:

- Transliterate global attribute taxonomy slugs when the merchant creates or edits an attribute under Products → Attributes.
- Do not transliterate already registered `pa_*` taxonomy names during frontend lookup/rendering.
- Do not transliterate `pa_` request keys, variation keys, or query keys.
- Decide explicitly whether existing `pa_*` attribute terms should be included in background conversion. Current code excludes them; 7.0 should either keep exclusion by default or introduce a separate opt-in converter.

Implementation notes:

- Add `GlobalAttributeService`.
- Add a compatibility matrix for:
  - Creating global attribute `Цвет` → expected attribute slug.
  - Editing global attribute name without changing slug.
  - Creating terms under `pa_color` / transliterated equivalent.
  - Filtering products by global attribute on frontend.
  - REST API product attribute creation if WooCommerce exposes it.

Open decision:

- Whether 7.0 should convert existing global attribute taxonomy slugs. This is risky because it can affect product variations, layered nav, URLs, lookup tables, and stored product metadata. Strong recommendation: do not include this in automatic conversion. Add a separate experimental tool only after tests prove it is safe.

### 4.7 WooCommerce local product attributes

Preferred hooks / APIs:

- Product object save flow, primarily `woocommerce_admin_process_product_object` for classic product editor.
- Product variation save hooks for variation attributes.
- WooCommerce AJAX action `woocommerce_save_attributes` only as a compatibility layer, not as the main architecture.
- WooCommerce REST API product create/update.
- Store API / product block editor save flow must be investigated separately if the tested WooCommerce version uses it.

Target behavior:

- Local attribute display names should remain human-readable labels.
- Internal local attribute keys/slugs should be generated once and consistently transliterated.
- Variation attribute keys must match saved product attributes exactly.
- Frontend add-to-cart and cart/session loading must preserve request keys and only normalize where WooCommerce expects normalized keys.

Implementation notes:

- Add `LocalAttributeService` with explicit methods:
  - `normalize_product_attribute_keys( WC_Product $product )`
  - `normalize_variation_attribute_keys( WC_Product_Variation $variation )`
  - `map_request_attribute_key_to_saved_key( string $request_key, WC_Product $product )`
- Remove most `$_POST` action sniffing from `Main::is_local_attribute()` after the new service is covered by tests.
- Keep a legacy bridge for one release if needed.

High-risk scenarios to test:

- Product with local attribute `Цвет`.
- Variable product using local attribute `Цвет` for variations.
- Variation creation from all attributes.
- Save attributes via AJAX.
- Save full product form.
- Add variation to cart on frontend.
- Load cart from session.
- Product imported through CSV.
- Product created/updated through REST API.

### 4.8 WooCommerce frontend attribute behavior

Current behavior adds/removes `sanitize_title` around template rendering.

7.0 target:

- Remove template-bound `sanitize_title` injection if explicit WooCommerce handlers cover all necessary flows.
- If a frontend fallback remains necessary, make it narrower:
  - Only during known WooCommerce variation matching or attribute key normalization.
  - Never for arbitrary template rendering.
  - Never for unrelated frontend `sanitize_title` calls.

Implementation notes:

- Add logging in development mode to detect unknown frontend calls that currently rely on `sanitize_title`.
- Use this logging only in dev/test builds or behind a debug constant.

### 4.9 Legacy `sanitize_title` bridge

7.0 probably cannot remove `sanitize_title` immediately without risk.

Recommended staged approach:

#### 7.0-alpha

- Introduce new explicit services.
- Keep `sanitize_title`, but convert it into a legacy bridge.
- The bridge should only run when a known context token is active.
- Log unknown calls in debug mode.

#### 7.0-beta

- Disable broad `sanitize_title` behavior by default in test builds.
- Run full regression suite.
- Identify remaining real paths that still depend on it.

#### 7.0 release

- Either remove broad `sanitize_title`, or keep it as a narrow fallback controlled by a filter:

```php
apply_filters( 'ctl_enable_legacy_sanitize_title_bridge', false );
```

Suggested default: `false` for new installs, possibly `true` for upgraded installs if regression risk is too high. Since WordPress.org plugins do not have real telemetry, be conservative if support risk is unacceptable.

## 5. Backward compatibility rules

### 5.1 Must not change

- Transliteration table behavior.
- `ctl_table` filter.
- `ctl_locale` filter.
- `ctl_pre_sanitize_title` filter for title-derived slugs.
- `ctl_pre_sanitize_filename` filter for filenames.
- WP-CLI command name: `wp cyr2lat regenerate`.
- Background conversion UX unless intentionally improved.
- Existing settings names where possible.

### 5.2 May change with migration/deprecation notice

- Internal `Main::sanitize_title()` behavior.
- Reliance on frontend WooCommerce template hooks.
- Term context flags such as `$this->is_term` and `$this->taxonomies`.
- Direct request sniffing in WooCommerce local attribute logic.

### 5.3 New compatibility filters

Recommended additions:

```php
ctl_should_transliterate_post_slug
ctl_should_transliterate_term_slug
ctl_should_transliterate_filename
ctl_should_transliterate_wc_global_attribute_slug
ctl_should_transliterate_wc_local_attribute_slug
ctl_enable_legacy_sanitize_title_bridge
ctl_slug_context
```

These filters should receive a context object/array, not just a string.

## 6. Test strategy

### 6.1 Test pyramid

Version 7.0 should use a backend-first test strategy:

1. Unit tests for pure transliteration and low-level services.
2. WordPress integration tests for posts, terms, filenames, settings, background jobs, and WP-CLI.
3. REST integration tests for Gutenberg/block-editor save paths.
4. WooCommerce integration tests for products, taxonomies, global attributes, local attributes, variable products, variations, carts, and REST/API paths.
5. No required Codeception, Playwright, acceptance, or browser tests for 7.0.

Rationale:

- Cyr-To-Lat normalizes backend data.
- Gutenberg is important, but the relevant plugin-facing path is REST/backend save behavior.
- Browser tests would be slower, more fragile, and less useful than direct backend entry-point coverage.
- Playwright may be introduced later as optional smoke coverage only if a real UI-only problem is discovered.

Integration test tooling decision:

- Use the standard WordPress PHPUnit integration test suite for WordPress and REST integration coverage.
- Keep the existing PHPUnit + WP_Mock unit suite for fast isolated tests.
- Add a dedicated integration suite/config, for example `tests/integration` and `phpunit.integration.xml`.
- Use a project-specific integration-test database, not a shared `wp-tests` database used by other projects.
- Do not commit the integration-test database name, database user, database password, or other local DB connection values.
- Read integration-test DB settings from local environment variables or an ignored local env file.
- Do not introduce Codeception for the 7.0 integration layer.
- Do not introduce acceptance-test infrastructure for 7.0.
- Revisit Codeception only if a later, concrete need appears for acceptance-style flows that cannot be covered cleanly through WordPress PHPUnit, REST integration tests, WooCommerce integration tests, or optional future browser smoke tests.

### 6.2 Unit tests

Add pure tests for:

- Transliteration API.
- Context object.
- Mac Unicode normalization.
- Chinese splitting behavior.
- Locale/table selection.
- Pre-filter short-circuit behavior.
- Already-Latin slugs.
- Mixed Cyrillic/Latin input.
- Symbols, punctuation, whitespace, and duplicate separators.

### 6.3 WordPress integration tests

Use the standard WordPress PHPUnit integration test suite, not Codeception.

Add tests for:

- Post creation with Cyrillic title.
- Post creation with manual Cyrillic slug.
- Post creation with manual Latin slug.
- Post update where title changes but slug is already set.
- Draft-to-publish behavior.
- Page creation.
- Custom post type creation.
- Duplicate Cyrillic titles and unique slug generation.
- Attachment upload filename through WordPress upload pipeline.
- Attachment post slug.
- Category creation.
- Tag creation.
- Custom taxonomy term creation.
- Explicit term slug supplied in args.
- Existing term with URL-encoded Cyrillic slug.
- WP-CLI regenerate command.
- Background conversion queue payloads.

### 6.4 REST integration tests for Gutenberg coverage

These tests replace mandatory Playwright coverage for the block editor.

Implement these as WordPress PHPUnit integration tests that exercise REST requests through WordPress backend APIs. Do not use Codeception or browser automation for this layer.

Required scenarios:

1. Create post via REST with Cyrillic title and empty slug.
2. Create page via REST with Cyrillic title and empty slug.
3. Create REST-enabled custom post type with Cyrillic title and empty slug.
4. Update draft via REST and verify generated slug.
5. Publish draft via REST and verify final slug.
6. Update post via REST with explicit Cyrillic slug.
7. Update post via REST with explicit Latin/manual slug.
8. Verify manual slug is not unexpectedly overwritten.
9. Verify autosave/revision requests do not create broken slugs.
10. Verify duplicate Cyrillic titles produce unique Latin slugs.
11. Verify multilingual REST requests still select the expected transliteration table where Polylang/WPML coverage is practical.

### 6.5 WooCommerce integration tests

Use the WooCommerce test environment and pin tested versions.

Prefer WooCommerce integration tests on top of the WordPress PHPUnit integration suite. Do not add Codeception unless a later task identifies a specific WooCommerce flow that cannot be covered through backend integration, REST/API, CRUD, admin-handler, or optional future smoke coverage.

Required test matrix:

- Latest WooCommerce stable.
- Minimum supported WooCommerce version, if declared.
- WordPress latest stable.
- WordPress trunk/nightly only in allowed CI job, not release-blocking unless desired.
- PHP 7.4, 8.1, 8.2, 8.3, 8.4.

WooCommerce cases:

1. Simple product with Cyrillic title → product slug transliterated.
2. Product category with Cyrillic name → term slug transliterated.
3. Product tag with Cyrillic name → term slug transliterated.
4. Global attribute name in Cyrillic → attribute slug transliterated or preserved according to explicit 7.0 decision.
5. Global attribute term in Cyrillic → term slug behavior documented and tested.
6. Global attribute creation through WooCommerce REST/API, if supported.
7. Local product attribute `Цвет` saved on product.
8. Variable product with local attribute `Цвет` and variation values.
9. Variation attributes saved and reloaded consistently.
10. Variation add-to-cart from frontend.
11. Cart reload from session.
12. Product attribute AJAX save.
13. Product full admin save flow through backend handlers.
14. Product REST API save.
15. Product CSV import if feasible.
16. Product block editor / new WooCommerce product editor backend save flow if available.

### 6.6 Browser and acceptance tests policy

Version 7.0 should not depend on Playwright, Codeception acceptance tests, or browser-based end-to-end tests.

Policy:

- Do not add Playwright as a release-blocking dependency.
- Do not add Codeception as a required test dependency.
- Do not add acceptance tests as a required 7.0 layer.
- Do not use browser tests to replace integration tests.
- Add Playwright only later as optional smoke coverage if a real UI-only regression is discovered.

Potential future Playwright smoke cases, not required for 7.0:

- Gutenberg post creation with Cyrillic title.
- Gutenberg manual slug edit.
- WooCommerce product with Cyrillic local attribute.
- WooCommerce variable product with Cyrillic variation attribute.
- Media upload with Cyrillic filename.

### 6.7 Regression tests for current bugs/fixes

Cover changelog-sensitive areas:

- WC local attributes fixed in 6.7.0.
- Product attribute processing fixed in 6.0.8.
- Variable product attributes fixed in 6.0.7.
- Tags during editing fixed in 6.5.0.
- Gutenberg slug behavior through REST.
- WPML and Polylang locale behavior.

## 7. Migration strategy

### 7.1 Phase 1 — Safety net and internal extraction without behavior changes

Goal:

- Create a testable internal architecture while keeping public behavior and old hooks unchanged.

This phase should not try to fix WooCommerce behavior yet. It should make future WooCommerce fixes safe.

Tasks:

- Add regression tests for current behavior before changing behavior.
- Create `Transliterator` service responsible only for converting text using the active Cyr-To-Lat table.
- Create a lightweight `SlugContext` structure to describe conversion context:
  - post
  - term
  - filename
  - WooCommerce global attribute
  - WooCommerce local attribute
  - WooCommerce variation attribute
  - admin
  - frontend
  - AJAX
  - REST
  - CLI
- Move low-level transliteration helpers from `Main` into `Transliterator`, without changing behavior.
- Create `LegacySanitizeTitleBridge` and move the current `Main::sanitize_title()` logic there.
- Keep `Main::sanitize_title()` registered on the `sanitize_title` filter, but make it delegate to `LegacySanitizeTitleBridge`.
- Move filename sanitization logic into `FilenameService`, keeping the existing `sanitize_file_name` hook unchanged.
- Add unit tests for the transliteration core.
- Add WordPress integration tests for current post, term, filename, and WooCommerce attribute behavior where possible.
- Add REST integration tests for current Gutenberg save-path behavior.
- Do not remove or narrow the global `sanitize_title` hook in this phase.
- Do not change database conversion behavior in this phase.
- Do not introduce automatic WooCommerce attribute migration in this phase.
- Do not add Playwright in this phase.

### 7.2 Phase 2 — Explicit WordPress slug handlers

Goal:

- Move standard WordPress data types away from broad `sanitize_title` behavior.

Tasks:

- Move post slugs to `PostSlugService`.
- Use `wp_insert_post_data` as the primary post slug handler.
- Add/complete REST integration coverage for Gutenberg paths.
- Move filenames to `FilenameService`.
- Move term slugs to `TermSlugService`.
- Use `pre_insert_term` and `pre_term_slug` explicitly.
- Keep `sanitize_title` bridge enabled as a fallback.
- Run tests with broad bridge disabled in CI to discover missed paths.

### 7.3 Phase 3 — WooCommerce explicit handlers

Goal:

- Replace WooCommerce request sniffing and frontend template-bound `sanitize_title` behavior with explicit WooCommerce services.

Tasks:

- Move WooCommerce global attributes to `GlobalAttributeService`.
- Move WooCommerce local attributes to `LocalAttributeService`.
- Move variation attributes to `VariationAttributeService` or keep them in `LocalAttributeService` if simpler.
- Add WooCommerce CRUD/API integration coverage.
- Add WooCommerce REST integration coverage where available.
- Add frontend variation/cart integration coverage without Playwright.
- Reduce `sanitize_title` bridge scope after tests pass.

### 7.4 Phase 4 — Legacy bridge reduction

Goal:

- Remove or strictly limit broad `sanitize_title` behavior.

Tasks:

- Disable broad `sanitize_title` fallback in tests.
- Keep compatibility filter to re-enable it.
- Document behavior changes.
- Decide whether upgraded installs should keep a conservative fallback enabled for one release.
- Add development-only logging for unknown bridge calls.

### 7.5 User-facing migration

- No automatic destructive conversion of WooCommerce global attribute taxonomy slugs.
- No automatic conversion of existing WooCommerce variation attribute keys unless a dedicated migration tool is added.
- Existing background converter keeps current behavior by default.
- Add a warning or help text explaining that old WooCommerce attributes created before Cyr-To-Lat may need manual or dedicated conversion.

### 7.6 Database safety

For any future WooCommerce attribute migration tool:

- Require explicit admin action.
- Show backup warning.
- Dry-run first.
- List affected products, variations, terms, lookup tables, and taxonomies.
- Process in small batches.
- Provide rollback metadata where realistic.

## 8. Proposed Junie task breakdown

### Epic 1 — Behavior capture before refactor

- [ ] Add tests documenting current `Main::sanitize_title()` behavior.
- [ ] Add tests for post slug generation through `wp_insert_post_data`.
- [ ] Add REST integration tests for Gutenberg/block-editor save paths.
- [ ] Add tests for term slug generation through `pre_insert_term` / `sanitize_title` interaction.
- [ ] Add tests for `sanitize_file_name` transliteration.
- [ ] Add tests for current WooCommerce global attribute behavior.
- [ ] Add tests for current WooCommerce local attribute behavior.
- [ ] Add tests for frontend WooCommerce variation add-to-cart with Cyrillic local attribute.
- [ ] Add tests for cart/session loading with Cyrillic local attributes.

### Epic 2 — Extract transliteration core

- [ ] Create `Transliterator` service.
- [ ] Create `SlugContext` value object or associative context structure.
- [ ] Move `fix_mac_string()` into transliteration core.
- [ ] Move `split_chinese_string()` into transliteration core.
- [ ] Preserve `ctl_table` and `ctl_locale` behavior.
- [ ] Add unit tests for transliteration core.

### Epic 3 — Extract filename handling

- [ ] Create `FilenameService`.
- [ ] Move `sanitize_filename()` logic from `Main`.
- [ ] Preserve `ctl_pre_sanitize_filename`.
- [ ] Add filename integration tests.

### Epic 4 — Extract post slug handling

- [ ] Create `PostSlugService`.
- [ ] Register `wp_insert_post_data` with 4 accepted args.
- [ ] Support empty `post_name` generation from raw title.
- [ ] Support explicit Cyrillic `post_name` normalization.
- [ ] Preserve manual Latin slugs.
- [ ] Skip autosaves, revisions, and auto-drafts.
- [ ] Add tests for post/page/CPT/product slugs.
- [ ] Add REST tests for posts/pages/CPTs.

### Epic 5 — Extract old slug redirect handling

- [ ] Create `OldSlugRedirectService`.
- [ ] Move `check_for_changed_slugs()` logic.
- [ ] Add regression tests for `_wp_old_slug` behavior.

### Epic 6 — Extract term slug handling

- [ ] Create `TermSlugService`.
- [ ] Replace `$is_term` / `$taxonomies` state with scoped context.
- [ ] Use `pre_insert_term` and `pre_term_slug` explicitly.
- [ ] Add tests for category/tag/custom taxonomy/product taxonomy terms.
- [ ] Add tests for explicit term slug in args.
- [ ] Add WPML/Polylang term tests if practical.

### Epic 7 — WooCommerce global attributes

- [ ] Research exact WooCommerce hooks around attribute taxonomy create/update.
- [ ] Research WooCommerce REST/API paths for product attributes.
- [ ] Create `GlobalAttributeService`.
- [ ] Add tests for creating/editing global attributes.
- [ ] Add tests for global attribute terms.
- [ ] Add tests for frontend global attribute filtering where practical.
- [ ] Decide and document existing `pa_*` attribute migration policy.

### Epic 8 — WooCommerce local and variation attributes

- [ ] Create `LocalAttributeService`.
- [ ] Create `VariationAttributeService` if separation is cleaner.
- [ ] Normalize saved local product attribute keys explicitly.
- [ ] Normalize variation attribute keys explicitly.
- [ ] Support AJAX attribute save flow.
- [ ] Support full product save flow.
- [ ] Support WooCommerce REST/API product save flow.
- [ ] Support frontend add-to-cart and cart session loading without broad `sanitize_title`.
- [ ] Add WooCommerce CRUD/API integration tests.

### Epic 9 — Legacy bridge reduction

- [ ] Create `LegacySanitizeTitleBridge`.
- [ ] Move old `sanitize_title` logic into bridge.
- [ ] Add `ctl_enable_legacy_sanitize_title_bridge` filter.
- [ ] Add development-only logging for unknown bridge calls.
- [ ] Disable broad bridge in tests and fix uncovered paths.

### Epic 10 — Converter and WP-CLI review

- [ ] Keep `wp cyr2lat regenerate` command stable.
- [ ] Ensure background post converter uses `PostSlugService`.
- [ ] Ensure background term converter uses `TermSlugService`.
- [ ] Preserve `ctl_post_types`.
- [ ] Decide whether WooCommerce attributes need a separate converter page section.
- [ ] Add dry-run mode proposal for future attribute migrations.

### Epic 11 — Browser tests decision

- [ ] Do not add Playwright as a required dependency for 7.0.
- [ ] Do not add Codeception as a required dependency for 7.0.
- [ ] Use the standard WordPress PHPUnit integration suite for integration tests.
- [ ] Document that Gutenberg coverage is provided by REST integration tests.
- [ ] Document that WooCommerce coverage is provided by WooCommerce CRUD/API/admin-handler integration tests.
- [ ] Revisit optional Playwright smoke tests only after a real UI-only bug is identified.

### Epic 12 — Documentation and release preparation

- [ ] Update readme feature list.
- [ ] Add upgrade notes for 7.0.
- [ ] Document WooCommerce attribute limitations clearly.
- [ ] Document legacy bridge filter.
- [ ] Document testing strategy: unit, WordPress PHPUnit integration, REST integration, WooCommerce integration, no required Codeception, no required Playwright.
- [ ] Add changelog entry.
- [ ] Test with WordPress latest and WooCommerce latest.

## 9. Suggested release criteria

7.0 should be considered ready when:

- All existing plugin tests pass.
- New tests cover post, term, filename, WooCommerce product, WooCommerce global attribute, WooCommerce local attribute, and variation flows.
- REST integration tests cover Gutenberg/block-editor slug paths.
- Broad `sanitize_title` dependency is removed or reduced to a documented legacy bridge.
- WooCommerce frontend product selection works with Cyrillic local attributes.
- Background conversion still works for posts and terms.
- WP-CLI command still works.
- WPML and Polylang behavior is not regressed.
- No automatic destructive WooCommerce attribute migration is introduced without a dry-run tool.
- Codeception is not required for release.
- Playwright is not required for release.

## 10. Strong recommendation

Do not make WooCommerce attribute migration part of the first 7.0 release.

The first 7.0 release should fix architecture and future behavior. Existing WooCommerce attributes created before Cyr-To-Lat, especially local attributes and global `pa_*` taxonomies, should be handled by a separate migration tool after the new explicit services are stable.

That separation will keep 7.0 shippable and reduce the chance of breaking existing stores.

Also, do not make Codeception or Playwright part of the required 7.0 release plan. The right 7.0 test strategy is backend-first: unit tests, standard WordPress PHPUnit integration tests, REST integration tests, and WooCommerce integration tests. Acceptance/browser tests can be added later only if the plugin starts failing in a UI-only path that cannot be reproduced through backend entry points.
