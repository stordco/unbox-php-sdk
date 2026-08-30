When writing plans or documentation use: ASD-STE100 Simplified Technical English (STE for short).

# Agent guidance

Handwritten PHP client for the Unbox (Penny Black) public HTTP API. Packagist: `pennyblack/php-sdk`. Default branch: `main`. PHP `>=7.4`. This SDK is **not** generated from OpenAPI.

## Purpose

`PennyBlack\Api` wraps ingest and fulfilment calls with `X-Api-Key` auth, JSON bodies, and typed errors. It implements PSR-7, PSR-17, and PSR-18: callers supply `ClientInterface`, `RequestFactoryInterface`, and `StreamFactoryInterface`. Guzzle is a **dev** dependency only; production users must provide their own PSR HTTP client and factories.

Endpoints (see [API docs](https://pennyblack.stoplight.io/docs/pennyblack/)):

- `installStore` — `POST ingest/install`
- `sendOrder` — `POST ingest/order` (retries up to 3 times on 500 / 5xx)
- `requestPrint` — `POST fulfilment/orders/print` (warehouse API key; may differ from the merchant key)
- `requestBatchPrint` — `POST fulfilment/orders/batch-print`
- `getOrderPrintStatus` — `GET fulfilment/orders/{merchantId}/{orderId}`

Base URLs: `https://api.pennyblack.io/` (prod) and `https://api.test.pennyblack.io/` (`$isTest = true`). Models `Order` and `Customer` use fluent setters and `toArray()`.

When the public API changes, update this SDK by hand to match `unbox-api-docs` (`reference/ingest.yaml`, `reference/fulfilment.yaml`). Do not add an OpenAPI generator.

## Commands

```bash
composer install          # includes Guzzle and quality tools
composer unit-test        # PHPUnit: ./vendor/bin/phpunit tests/
composer quality-check    # phpcs + phpstan + phpmd
```

CI on PRs to `main` runs that pair on PHP 7.4, 8.2, and 8.3 with `composer update --prefer-lowest`.

## Layout

- `src/Api.php` — HTTP client
- `src/Model/` — `Order`, `Customer`
- `src/Exception/` — `PennyBlackException` and subclasses (`ApiException`, `AuthenticationException`, `ServerErrorException`, `ServiceUnavailableException`)
- `tests/Unit/` — PHPUnit 8 tests
- `example/` — install, send-order, print, batch-print, print-status samples

## Conventions

- PSR-4: `PennyBlack\` → `src/`
- PHPCS: PSR-12 on `src/` (`phpcs.xml`)
- PHPStan: level 5 on `src/` (`phpstan.neon`)
- PHPMD: `phpmd.xml` on `src/`
- `composer.lock` is gitignored. CI resolves with `--prefer-lowest`; do not commit a lockfile.
- Tests extend `PHPUnit\Framework\TestCase`. Method names use `testIt...`. Mock PSR HTTP types; do not hit the live API.
- Keep the public surface small. New calls need a matching example under `example/` and unit tests.
