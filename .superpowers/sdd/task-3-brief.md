# Task 3 Brief: Fix CSRF Token Exposed in GET URL for Hapus Guru

## Project Context
PHP web app (e-Raport). Working directory: C:\xampp\htdocs\erapor
CSRF service: app/Controllers/CsrfService.php (or similar)
GuruController: app/Controllers/GuruController.php
Router: public/index.php (whitelist router)

## Problem
The delete confirmation for guru uses CSRF token in GET URL:
```php
// In GuruController.php (around line 235) — confirmation anchor:
'<a href="hapus_guru.php?id=' . $id . '&konfirmasi=ya&csrf_token=' . CsrfService::generate() . '">Ya, Hapus Data</a>'
```
And in app/Views/data_guru.php (around line 436) — JS constructs same GET URL with CSRF token for SweetAlert confirmation.

CSRF tokens in GET URLs are logged in server access logs, browser history, Referer headers. This defeats CSRF protection.

## Required Fix

### Part A: GuruController.php
The delete flow should use POST. Change the confirmation anchor to a POST form:
```html
<form method="POST" action="hapus_guru.php" style="display:inline;">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="konfirmasi" value="ya">
    <input type="hidden" name="csrf_token" value="<?= CsrfService::generate() ?>">
    <button type="submit" class="...your-existing-button-classes...">Ya, Hapus Data</button>
</form>
```

### Part B: data_guru.php — SweetAlert JS
The JS that constructs the GET URL with csrf_token must be changed to either:
1. Submit a hidden POST form programmatically after SweetAlert confirmation, OR
2. Use fetch() POST to the delete endpoint

Preferred approach (simpler, matches PHP codebase style):
- Add a hidden form in the HTML for each guru row (or one shared form updated by JS)
- SweetAlert confirmation callback submits the form via JS form.submit()

### Part C: GuruController.php / hapus_guru.php — server-side handler
The delete handler currently reads CSRF from `$_GET['csrf_token']`. Change it to read from `$_POST['csrf_token']`. Also read `$_POST['id']` and `$_POST['konfirmasi']`.

## Important Constraints
- Keep all existing CSS classes and visual appearance intact
- Keep SweetAlert confirmation dialog behavior — user still sees confirm dialog before delete
- CSRF validation must still happen on server side
- Do NOT break the delete functionality
- Read the actual current file content before making changes — the exact line numbers and class names may differ from above approximations
- Stage all changed files when done

## Steps
1. Read GuruController.php fully — understand current delete flow
2. Read data_guru.php — find SweetAlert JS and delete URL construction
3. Check if there's a separate hapus_guru.php or if it's all in GuruController
4. Implement fix
5. Stage changed files: git add app/Controllers/GuruController.php app/Views/data_guru.php (and any other changed files)
6. Write report to .superpowers/sdd/task-3-report.md

## Report Contract
Write to `.superpowers/sdd/task-3-report.md`:
- Status: DONE | DONE_WITH_CONCERNS | NEEDS_CONTEXT | BLOCKED
- Files changed
- Before/after for key changes
- Any concerns
Return only status + one-line summary.
