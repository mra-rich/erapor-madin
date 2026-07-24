# Task 2 Brief: Fix DB Error Credential Leak in config/koneksi.php

## Project Context
PHP web app (e-Raport / erapor). Working directory: C:\xampp\htdocs\erapor
DB: MySQLi connecting to Aiven cloud DB.

## Problem
In config/koneksi.php, the connection error handler exposes raw mysqli connect_error to HTTP response:
```php
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
```
`connect_error` on a failed Aiven SSL connection can include host, port, SSL cert path — surfaced directly to HTTP response.

## Required Fix
Replace with:
```php
if ($koneksi->connect_error) {
    error_log("DB connect failed: " . $koneksi->connect_error);
    http_response_code(503);
    die("Layanan tidak tersedia. Silakan coba lagi nanti.");
}
```

Exact requirements:
- Use `error_log()` to log the real error internally
- Use `http_response_code(503)` before die
- The user-facing message must be: `"Layanan tidak tersedia. Silakan coba lagi nanti."`
- Do NOT change any other part of the file
- Do NOT change connection logic, SSL config, session config, or any other code

## Steps
1. Read config/koneksi.php fully
2. Find the exact connect_error handler
3. Apply the fix
4. Stage the file: git add config/koneksi.php
5. Write report to .superpowers/sdd/task-2-report.md

## Report Contract
Write to `.superpowers/sdd/task-2-report.md`:
- Status: DONE | DONE_WITH_CONCERNS | NEEDS_CONTEXT | BLOCKED
- Before/after snippet
- Any concerns
Return only status + one-line summary in your final message.
