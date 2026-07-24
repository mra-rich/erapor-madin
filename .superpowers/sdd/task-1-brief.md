# Task 1 Brief: Fix SQL Injection in data_guru.php and export_guru.php

## Project Context
PHP web app (e-Raport / erapor) — school report card system.
Working directory: C:\xampp\htdocs\erapor
Git branch: main (working tree — changes not yet committed)
DB: MySQLi with Aiven PostgreSQL-compatible connection via config/koneksi.php

## Problem
Two files use `mysqli_real_escape_string` on `$search` then interpolate directly into LIKE clauses. This does NOT escape `%` and `_` wildcards, enabling wildcard abuse (DoS-level full-table scan) and is one step from SQLi if charset mismatch occurs.

### File 1: app/Views/data_guru.php (around line 19)
Current pattern (approximate):
```php
$search = trim($_GET['search'] ?? '');
$search = mysqli_real_escape_string($koneksi, $search);
// ...
$where_clause .= " AND (p.nama LIKE '%$search%' OR g.nip LIKE '%$search%' OR p.username LIKE '%$search%')";
```

### File 2: app/Views/export_guru.php (around line 33)
Same pattern.

## Required Fix
Replace both with parameterized prepared statements using MySQLi. Pattern:

```php
$search = trim($_GET['search'] ?? '');
$like = '%' . $search . '%';
// Build query string with ? placeholders instead of interpolation
// Use mysqli_prepare + mysqli_stmt_bind_param
// Bind $like for each LIKE ? placeholder
```

Important constraints:
- Use MySQLi (not PDO) — matches existing codebase style
- Do NOT change surrounding query logic, pagination, joins — only the search/LIKE part
- Keep all existing columns, JOINs, WHERE conditions intact
- The prepared statement must handle the case where $search is empty (LIKE '%%' returns all — acceptable)
- Do NOT add any new features
- Existing variable names for result sets must remain compatible with the rest of the file

## Steps
1. Read the current content of both files fully
2. Identify the exact LIKE clause(s) and their surrounding query structure
3. Refactor to prepared statements
4. Verify no other search interpolation exists in either file
5. Stage changes (git add) but do NOT commit yet — Task 5 will do the final commit
6. Write report to .superpowers/sdd/task-1-report.md

## Report Contract
Write to `.superpowers/sdd/task-1-report.md`:
- Status: DONE | DONE_WITH_CONCERNS | NEEDS_CONTEXT | BLOCKED
- Files changed (list)
- What exactly was changed (before/after snippets)
- Any concerns
Return only status + one-line summary in your final message.
