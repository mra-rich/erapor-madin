# Task 3 Report: Fix CSRF Token Exposed in GET URL for Hapus Guru

## Status: DONE

## Files Changed
- `app/Controllers/GuruController.php`
- `app/Views/data_guru.php`

## Key Changes

### Part A: GuruController.php — Confirmation Page (line ~235)

**Before:**
```php
echo '        <a href="hapus_guru.php?id=' . $id . '&konfirmasi=ya&csrf_token=' . CsrfService::generate() . '" class="px-4 py-2 bg-red-600 text-white font-medium rounded hover:bg-red-700">Ya, Hapus Data</a>';
```

**After:**
```php
echo '        <form method="POST" action="hapus_guru.php" style="display:inline;">';
echo '          <input type="hidden" name="id" value="' . $id . '">';
echo '          <input type="hidden" name="konfirmasi" value="ya">';
echo '          <input type="hidden" name="csrf_token" value="' . CsrfService::generate() . '">';
echo '          <button type="submit" class="px-4 py-2 bg-red-600 text-white font-medium rounded hover:bg-red-700">Ya, Hapus Data</button>';
echo '        </form>';
```

### Part B: data_guru.php — SweetAlert JS confirmDelete()

**Before:**
```js
if (result.isConfirmed) {
    window.location.href = 'hapus_guru.php?konfirmasi=ya&csrf_token=<?php echo generate_csrf_token(); ?>&id=' + id;
}
```

**After:**
```js
if (result.isConfirmed) {
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = 'hapus_guru.php';
    // hidden inputs: id, konfirmasi=ya, csrf_token (PHP-generated)
    document.body.appendChild(form);
    form.submit();
}
```

### Part C: Server-side handler (GuruController::delete)

No change needed. `verifyCsrf()` in BaseController already checks `$_POST['csrf_token']` first (`$_POST[$key] ?? $_GET[$key]`). `input()` similarly reads POST before GET. The delete handler reads `konfirmasi` and `id` via `input()` — works for POST.

## Notes / Concerns

- **Two delete paths exist**: (1) Direct link to `hapus_guru.php?id=X` without `konfirmasi` → renders the inline confirmation page with the POST form (fixed). (2) SweetAlert in `data_guru.php` → now submits POST form (fixed).
- The CSRF token in the SweetAlert path is rendered server-side at page load time (PHP `generate_csrf_token()` in JS string). This is standard practice — token is in HTML source but NOT in GET URL/access logs/Referer headers.
- `BaseController::verifyCsrf()` reads `$_POST['csrf_token'] ?? $_GET['csrf_token']` — the GET fallback remains but is no longer used by either delete path. Could be tightened in a future task by removing the `$_GET` fallback for mutation endpoints.
- All CSS classes and visual appearance preserved. SweetAlert confirmation dialog behavior unchanged.
