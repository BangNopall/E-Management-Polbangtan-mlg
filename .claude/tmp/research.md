# Research: Laravel 13 Upgrade — Laravel 12 → 13

> Researched: July 2026

## Summary

Laravel 13 was released on **March 17, 2026**, with a minimum PHP requirement of **8.3** (up from 8.2). It is positioned as a **relatively minor upgrade** — most applications can upgrade with little to no code changes.

---

## 1. Release & Requirements

| Item | Laravel 12 | Laravel 13 |
|---|---|---|
| Release date | February 7, 2025 | March 17, 2026 |
| PHP minimum | 8.2 | **8.3** |
| Passes | Passes list | Same |
| Minimum stability | stable | stable |

**Source:** laravel.com/docs/13.x/releases (primary), laravel-news.com

---

## 2. Breaking Changes

### 2.1 CSRF Middleware Renamed (HIGH IMPACT)

`VerifyCsrfToken` → **`PreventRequestForgery`**

- The old class name (`VerifyCsrfToken`) and `ValidateCsrfToken` remain as **deprecated aliases** but will be removed in a future version.
- Now includes **`Sec-Fetch-Site` header-based origin verification** for additional CSRF protection.
- **Action required:** Update any direct references to `VerifyCsrfToken` in your `bootstrap/app.php` or middleware configuration.

### 2.2 Cache/Redis Key Prefix Change (MEDIUM IMPACT)

- Default cache and Redis key prefixes changed from underscore-separated to **hyphen-separated** suffixes:
  - Laravel ≤ 12.x: `Str::slug(...).'_cache_'`
  - Laravel ≥ 13.x: `Str::slug(...).'-cache-'`
- **Effect:** Cached data with framework-level fallback config will be invalidated after upgrade (new keys won't match old cached entries).
- **Action required:** If you rely on framework default cache prefixes, plan for a cold cache after deployment.

### 2.3 PHP Requirement: 8.3 Minimum

- PHP 8.2 is no longer supported. Your environment must run PHP ≥ 8.3.

---

## 3. New Features

### 3.1 First-Party Laravel AI SDK 🎉

- `laravel/ai` package provides:
  - Text generation with unified API
  - Tool-calling agent support
  - Embeddings
  - Audio processing
  - Image generation
  - Vector-store integrations
- Available at `laravel.com/ai` and `github.com/laravel/ai`

### 3.2 New Artisan Dev Commands

- `php artisan dev` — starts the local development server
- `php artisan dev:list` — lists available dev command sources

### 3.3 Additional Improvements

- `Str::inlineMarkdown()` and `Str::markdown()` now support extensibility
- Enhanced testing utilities

---

## 4. Upgrade Steps

### 4.1 Composer Dependency Changes

Update `composer.json`:

| Package | Laravel 12 | Laravel 13 |
|---|---|---|
| `laravel/framework` | `^12.0` | **`^13.0`** |
| `laravel/tinker` | `^2.9` | **`^3.0`** |
| `laravel/boost` | `^1.0` | **`^2.0`** |
| `phpunit/phpunit` | `^11.0` | **`^12.0`** |
| `pestphp/pest` | `^3.0` | **`^4.0`** |

Then run:

```bash
composer update
```

### 4.2 CSRF Middleware Update

In `bootstrap/app.php`, change:
```php
// Old
\App\Http\Middleware\VerifyCsrfToken::class

// New
\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class
```

Or if using the `App\Http\Middleware\VerifyCsrfToken.php` file, refactor it to extend `PreventRequestForgery`.

---

## 5. Package Compatibility (for this project)

| Package | Current version | Likely compatible with L13? | Notes |
|---|---|---|---|
| `barryvdh/laravel-dompdf` `^3.0` | 3.x | ✅ Yes | L5-L11 compatible, no L12/13 specific issues |
| `maatwebsite/excel` `^3.1` | 3.1.x | ⚠️ Likely | May need update if it pins `laravel/framework` |
| `simplesoftwareio/simple-qrcode` `^4.2` | 4.x | ✅ Yes | Pure QR rendering, no Laravel internals dependency |
| `laravel/sanctum` `^4.0` | 4.x | ✅ Yes | Ships with Laravel, upgraded with framework |
| `nesbot/carbon` `^3.0` | 3.x | ✅ Yes | Updated automatically |
| `guzzlehttp/guzzle` `^7.2` | 7.x | ✅ Yes | Independent of Laravel version |

---

## 6. Relevant Laravel Skills

The following ECC skills exist and should be used when implementing changes:

- `ecc:laravel-patterns` — Laravel coding patterns
- `ecc:laravel-security` — Laravel-specific security (CSRF, etc.)
- `ecc:laravel-tdd` — Test-driven development for Laravel
- `ecc:laravel-verification` — Verification checklists for Laravel

---

## 7. Open Questions

1. Will the change in cache/Redis key prefix format (underscore to hyphen) silently invalidate existing cached data in production?
2. For applications using deprecated `VerifyCsrfToken` references, is there an automated upgrade path (e.g., a rector rule)?
3. Does `maatwebsite/excel` need a version bump to work with L13?

---

## Sources

- [Laravel 13.x Release Notes](https://laravel.com/docs/13.x/releases) (primary)
- [Laravel 13.x Upgrade Guide](https://laravel.com/docs/13.x/upgrade) (primary)
- [Laravel Framework CHANGELOG](https://github.com/laravel/framework/blob/13.x/CHANGELOG.md)
- [Laravel News: Laravel 13](https://laravel-news.com/laravel-13)
