# Task 2 Report: Fix DB Error Credential Leak in config/koneksi.php

## Status: DONE

## Before
```php
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
```

## After
```php
if ($koneksi->connect_error) {
    error_log("DB connect failed: " . $koneksi->connect_error);
    http_response_code(503);
    die("Layanan tidak tersedia. Silakan coba lagi nanti.");
}
```

## Actions Taken
1. Read `config/koneksi.php` fully.
2. Located the connect_error handler at lines 37-39.
3. Applied exact fix per brief: `error_log()`, `http_response_code(503)`, user-safe message.
4. No other code changed.
5. Staged: `git add config/koneksi.php` — confirmed in staged changes.

## Concerns
None. Fix is minimal and surgical. No other logic touched.
