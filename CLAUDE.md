# VRTs WordPress Plugin

## Overview

WordPress plugin for visual regression testing. Connects to the VRTs Service API to manage test schedules, trigger screenshot comparisons, and display visual change alerts in the WordPress admin.

**Tech Stack:** PHP 7.0+, WordPress APIs, @wordpress/scripts (React/webpack), SCSS

## Bootstrap Sequence

Entry point: `visual-regression-tests.php`

1. Defines `VRTS_PLUGIN_FILE` and `VRTS_SERVICE_ENDPOINT` (env-overridable, default: `https://bleech-vrts-app.blee.ch/api/v1/`)
2. Loads Composer autoloader (`vendor/autoload.php`) if present
3. Loads custom autoloader (`includes/autoload.php`)
4. Creates global `vrts()` function returning the `Plugin` singleton
5. Calls `vrts()->setup('vrts', [...])` with three namespace-to-directory mappings that **auto-instantiate all classes** in:
   - `Vrts\Features\` -> `includes/features/` (20 feature classes)
   - `Vrts\Tables\` -> `includes/tables/` (3 table schema classes)
   - `Vrts\Rest_Api\` -> `includes/rest-api/` (4 REST controllers)

## Custom Autoloader

**File:** `includes/autoload.php` - WordPress-flavored PSR-4 via `spl_autoload_register`.

- **Namespace prefix:** `Vrts\`
- **Base directory:** `includes/`
- **Mapping:** Namespace segments are lowercased, underscores become hyphens, class files get a `class-` prefix

Examples:
- `Vrts\Core\Plugin` -> `includes/core/class-plugin.php`
- `Vrts\Models\Test` -> `includes/models/class-test.php`
- `Vrts\Core\Utilities\Date_Time_Helpers` -> `includes/core/utilities/class-date-time-helpers.php`

This is NOT standard PSR-4 - it follows WordPress naming conventions.

## Namespace / Class Structure

```
Vrts\
├── Core\
│   ├── Plugin                    # Singleton, orchestrates setup, component rendering
│   ├── Traits\Singleton          # Singleton pattern trait
│   ├── Traits\Macroable          # Dynamic method binding trait
│   ├── Settings\Manager          # WordPress Settings API abstraction
│   └── Utilities\                # Url_Helpers, Image_Helpers, Date_Time_Helpers,
│                                 # String_Helpers, Color_Helpers, Array_Helpers,
│                                 # Sanitization, Async_Request, Background_Process
├── Features\                     # Auto-instantiated on setup() -- each hooks into WP
│   ├── Admin                     # Menu registration, plugin action links
│   ├── Admin_Columns             # Custom columns in post list tables
│   ├── Admin_Header              # Shared admin page header component
│   ├── Admin_Notices             # Dismissible admin notifications
│   ├── Bulk_Actions              # Bulk "add test" from post list
│   ├── Cron_Jobs                 # WP cron scheduling (hourly fetch, retry polling)
│   ├── Deactivate                # Plugin deactivation cleanup
│   ├── Enqueue_Scripts           # CSS/JS registration for admin + block editor
│   ├── Install                   # Activation: table creation, service connection
│   ├── Metaboxes                 # Classic editor sidebar (VRTs toggle per post)
│   ├── Onboarding                # Guided tour (driver.js)
│   ├── Post_Update_Actions       # Hooks into save_post, trash, slug changes
│   ├── Service                   # HTTP client for Laravel API communication
│   ├── Settings_Page             # Click selectors, license, triggers, notifications
│   ├── Subscription              # Credit/tier management via WP options
│   ├── Test_Runs_Page            # Test runs admin page
│   ├── Tests_Page                # Tests admin page (add/run/bulk actions)
│   ├── Tests                     # Additional test logic
│   ├── Translations              # i18n loading
│   └── Upgrade_Page              # Pricing/upgrade page
├── Models\                       # Data access layer (static methods, direct $wpdb)
│   ├── Test                      # CRUD for vrts_tests, calculated status logic
│   ├── Alert                     # CRUD for vrts_alerts, state management
│   └── Test_Run                  # CRUD for vrts_test_runs, trigger metadata
├── Services\                     # Business logic layer
│   ├── Test_Service              # Create/delete/resume tests (local + remote)
│   ├── Test_Run_Service          # Process run webhooks, create alerts from comparisons
│   ├── Alert_Service             # Create alert records from comparison data
│   ├── Email_Service             # Send HTML test run notification emails
│   └── Manual_Test_Service       # Trigger manual test runs (subscription required)
├── Tables\                       # DB schema definitions (dbDelta)
│   ├── Tests_Table
│   ├── Alerts_Table
│   └── Test_Runs_Table
├── Rest_Api\                     # REST endpoint controllers
│   ├── Rest_Service_Controller   # Webhook receiver (signature-verified)
│   ├── Rest_Tests_Controller     # Test CRUD via REST
│   ├── Rest_Alerts_Controller    # False positive + read status
│   └── Rest_Test_Runs_Controller # Read status for runs
└── List_Tables\                  # WP_List_Table implementations
    ├── Tests_List_Table
    ├── Test_Runs_List_Table
    └── Test_Runs_Queue_List_Table
```

## Custom Database Tables

### `{prefix}vrts_tests` (DB_VERSION 1.5)

| Column                 | Type          | Purpose                                 |
|------------------------|---------------|-----------------------------------------|
| `id`                   | bigint(20) PK | Auto-increment ID                       |
| `status`               | boolean       | 0=paused, 1=active                      |
| `post_id`              | bigint(20)    | WP post ID being tested                 |
| `service_test_id`      | varchar(40)   | Remote test ID on Laravel service       |
| `base_screenshot_url`  | varchar(2048) | S3 URL of baseline screenshot           |
| `base_screenshot_date` | datetime      | When baseline was captured              |
| `last_comparison_date` | datetime      | Last comparison timestamp               |
| `next_run_date`        | datetime      | Next scheduled run                      |
| `is_running`           | boolean       | Whether comparison is in progress       |
| `hide_css_selectors`   | longtext      | CSS selectors to hide during screenshot |

**Calculated statuses** (in `Test` model): `disconnected`, `no-credit-left`, `post-not-published`, `waiting`, `running`, `scheduled`, `has-alert`, `passed`

### `{prefix}vrts_alerts` (DB_VERSION 1.2)

| Column                          | Type          | Purpose                       |
|---------------------------------|---------------|-------------------------------|
| `id`                            | bigint(20) PK | Auto-increment ID             |
| `title`                         | text          | "Alert #N"                    |
| `post_id`                       | bigint(20)    | WP post that had changes      |
| `test_run_id`                   | bigint(20)    | FK to test_runs table         |
| `screenshot_test_id`            | varchar(40)   | Remote test ID                |
| `target_screenshot_url`         | varchar(2048) | New screenshot URL            |
| `target_screenshot_finish_date` | datetime      | When new screenshot was taken |
| `base_screenshot_url`           | varchar(2048) | Baseline screenshot URL       |
| `base_screenshot_finish_date`   | datetime      | When baseline was taken       |
| `comparison_screenshot_url`     | varchar(2048) | Diff image URL                |
| `comparison_id`                 | varchar(40)   | Remote comparison ID          |
| `differences`                   | int(4)        | Pixel diff count              |
| `alert_state`                   | tinyint       | 0=Open, 1=Archived            |
| `is_false_positive`             | tinyint       | 1=marked as false positive    |
| `meta`                          | text          | Serialized metadata           |

### `{prefix}vrts_test_runs` (DB_VERSION 1.1)

| Column                | Type          | Purpose                                          |
|-----------------------|---------------|--------------------------------------------------|
| `id`                  | bigint(20) PK | Auto-increment ID                                |
| `service_test_run_id` | varchar(40)   | Remote run ID                                    |
| `tests`               | text          | Serialized array of test info                    |
| `trigger`             | varchar(20)   | `manual`, `scheduled`, `api`, `update`, `legacy` |
| `trigger_notes`       | text          | Human-readable trigger description               |
| `trigger_meta`        | text          | Serialized metadata (user_id, update info)       |
| `started_at`          | datetime      | Run start time                                   |
| `scheduled_at`        | datetime      | Scheduled time                                   |
| `finished_at`         | datetime      | Completion time                                  |

## Service Communication

All API calls go through `Vrts\Features\Service` using `wp_remote_post()` / `wp_remote_get()`.

**Authentication:** Bearer token (`vrts_project_token` WP option), custom User-Agent: `VRTs/{version};{wp-user-agent}`

### Outbound (Plugin -> Laravel Service)

| Route                              | Method | Purpose                            |
|------------------------------------|--------|------------------------------------|
| `sites`                            | POST   | Register site (initial connection) |
| `sites/{id}`                       | PUT    | Update site settings               |
| `sites/{id}`                       | DELETE | Disconnect site                    |
| `sites/{id}/resume`                | POST   | Resume all tests                   |
| `sites/{id}/trigger`               | POST   | Trigger manual test run            |
| `sites/{id}/updates`               | GET    | Poll for test/run updates          |
| `sites/{id}/runs`                  | GET    | Fetch specific test runs           |
| `sites/{id}/secret`                | POST   | Create webhook signing secret      |
| `sites/{id}/register`              | POST   | Register license key               |
| `sites/{id}/unregister`            | POST   | Remove license key                 |
| `tests`                            | POST   | Create test(s)                     |
| `tests/{id}`                       | PUT    | Update test (URL, hide selectors)  |
| `tests/{id}`                       | DELETE | Delete test                        |
| `tests/{id}/resume`                | POST   | Resume individual test             |
| `tests/{id}/false-positives`       | POST   | Mark comparison as false positive  |
| `tests/{id}/false-positives/{cid}` | DELETE | Unmark false positive              |

### Inbound Webhooks (Laravel Service -> Plugin)

Endpoint: `wp-json/vrts/v1/service` (+ `admin-ajax.php` fallback for WPML compat)

Signature verification: HMAC-SHA256 of JSON payload using `vrts_project_secret`.

| Action                 | Purpose                                            |
|------------------------|----------------------------------------------------|
| `test_updated`         | Test screenshot/comparison ready                   |
| `run_updated`          | Test run completed, creates alerts for diffs > 1px |
| `run_deleted`          | Run deleted remotely                               |
| `subscription_changed` | Subscription tier changed                          |

### Connection Flow

1. On activation, `Service::connect_service()` POSTs to `sites` with `create_token`, `rest_url`, `admin_ajax_url`
2. Service responds with `id`, `token`, `secret`, credit info
3. Plugin stores as WP options: `vrts_project_id`, `vrts_project_token`, `vrts_project_secret`
4. Homepage is auto-added as the first test

## REST API Endpoints

All registered under `wp-json/vrts/v1/`:

| Endpoint                      | Method      | Auth             | Purpose                       |
|-------------------------------|-------------|------------------|-------------------------------|
| `/service`                    | POST        | Signature        | Webhook receiver from service |
| `/tests`                      | GET         | Public           | Get remaining/total credits   |
| `/tests/post/{post_id}`       | GET         | Public           | Get test data for a post      |
| `/tests/post/{post_id}`       | POST        | `manage_options` | Create test for a post        |
| `/tests/post/{post_id}`       | DELETE      | `manage_options` | Delete test for a post        |
| `/tests/post/{post_id}`       | PUT/PATCH   | `manage_options` | Update test (CSS selectors)   |
| `/alerts/{id}/false-positive` | POST/DELETE | `manage_options` | Toggle false positive         |
| `/alerts/{id}/read-status`    | POST/DELETE | `manage_options` | Toggle read status            |
| `/test-runs/{id}/read-status` | POST/DELETE | `manage_options` | Toggle run read status        |

## WordPress Hooks

### Activation / Deactivation
- `register_activation_hook` -> Creates DB tables, connects to service, adds homepage test
- `register_deactivation_hook` -> Deletes unfinished test runs, disconnects from service
- `upgrader_process_complete` -> Reinstalls tables, reconnects on plugin update

### Post Integration
- `save_post` -> Saves test toggle state from classic editor
- `rest_after_insert_{post_type}` -> Updates hide CSS selectors from block editor
- `wp_after_insert_post` -> Resumes test (retakes screenshot) after post update
- `trashed_post` -> Deletes test, archives alerts when post is trashed
- `transition_post_status` -> Creates/deletes remote test on publish/unpublish
- `post_updated` -> Updates test URL on service when slug changes

### Cron Jobs
- `vrts_fetch_updates_cron` (hourly) -- Polls service for all test/run updates
- `vrts_fetch_test_updates` (single-fire, exponential backoff, 10 retries at `20s * 2 * try`) -- Polls after creating a new test until `base_screenshot_date` is set
- `vrts_fetch_test_run_updates` (same backoff pattern) -- Polls after triggering a run until `finished_at` is set

## WordPress Options

| Option                  | Purpose                     |
|-------------------------|-----------------------------|
| `vrts_project_id`       | Service project UUID        |
| `vrts_project_token`    | API authentication token    |
| `vrts_project_secret`   | Webhook HMAC signing secret |
| `vrts_remaining_tests`  | Credits remaining           |
| `vrts_total_tests`      | Total test quota            |
| `vrts_has_subscription` | Premium subscription flag   |
| `vrts_tier_id`          | Subscription tier           |

## Frontend / JavaScript

### Build System

Webpack via `@wordpress/scripts` with two entry points:
- `assets/admin.js` -> `build/admin.js` (admin pages)
- `assets/editor.js` -> `build/editor.js` (Gutenberg block editor)

### Admin JS
- Auto-imports all `script.js` from `components/` directories
- Auto-imports all `_style.scss` from `components/`
- `components/` contains PHP template components rendered via `vrts()->component()` with co-located JS/SCSS

### Editor JS
- Uses `@wordpress/plugins` to register `PluginDocumentSettingPanel` + `PluginSidebar`
- Renders React `<Metabox />` component (`editor/components/metabox/`)

### Key JS Libraries
- `driver.js` -- Guided onboarding tours
- `a11y-dialog` -- Accessible modals (comparison dialog)
- `lottie-web` -- Animations
- `dompurify` -- HTML sanitization
- `iframe-resizer` -- Resizable iframes (upgrade page)

### Localized Data
- `vrts_admin_vars`: `rest_url`, `rest_nonce`, `pluginUrl`, `currentUserId`, `onboarding`
- `vrts_editor_vars`: `plugin_name`, `rest_url`, `has_post_alert`, `base_screenshot_url`, `remaining_tests`, `total_tests`, `test_status`, `test_settings`, etc.

## Admin Pages

Registered under the main "VRTs" menu (position 80):

| Slug            | Class            | Purpose                                           |
|-----------------|------------------|---------------------------------------------------|
| `vrts`          | `Tests_Page`     | Test list with add/run/bulk actions               |
| `vrts-runs`     | `Test_Runs_Page` | Test run history, alert list, comparison modal    |
| `vrts-settings` | `Settings_Page`  | Click selectors, license, triggers, notifications |
| `vrts-upgrade`  | `Upgrade_Page`   | Pricing/upgrade (iframe to external)              |

## Linting

```bash
npm run lint           # Run all linters
npm run lint:js        # ESLint (@wordpress/eslint-plugin)
npm run lint:css       # Stylelint (@wordpress/stylelint-config)
npm run lint:php       # PHPCS (WordPress Coding Standards)

npm run lint-fix       # Auto-fix all
npm run lint-fix:js    # Auto-fix JS
npm run lint-fix:css   # Auto-fix CSS
npm run lint-fix:php   # Auto-fix PHP (phpcbf)
```

Always try `lint-fix` before fixing manually.

## Code Style

- **PHP:** WordPress Coding Standards (WPCS), `Vrts\` namespace, DocBlocks required
- **JS:** ESLint with `@wordpress/eslint-plugin`, ES6+, React components
- **CSS/SCSS:** Stylelint with `@wordpress/stylelint-config`, BEM naming, `.vrts-` prefix
