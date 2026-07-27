# Task 1 Brief: Add layout view toggle and Tab mode in evaluasi_wali.php

## Context
Wali Kelas uses `evaluasi_wali.php` to input grades/assessments (Kepribadian, Ekskul, Absensi, Catatan) for all students.
On mobile devices (below `sm` breakpoint), the screen displays a long vertical stack of cards containing all input fields for each student. This requires heavy scrolling.

## Goal
Add a View Switcher (Toggle) in Mobile View that allows users to choose between:
1. **Mode Detail** (Original Stack layout): Full card per student.
2. **Mode Tab Kategori** (New Tabbed layout): Displays only one category (Kepribadian, Ekskul, Absensi, OR Catatan) for all students at a time, using a horizontal tab selection bar.

## Requirements
- Maintain both layouts inside `evaluasi_wali.php`'s mobile viewport branch (`sm:hidden`).
- View Switcher: Add a select dropdown or button group at the top of the mobile view to toggle between "Tampilan Detail (Card)" and "Tampilan Ringkas (Tab)".
- The switcher choice must be persisted in `localStorage.setItem('eval_view_mode', ...)` and loaded on DOM ready.
- Tab Layout:
  - Add 4 tab buttons: "Kepribadian", "Ekstrakurikuler", "Absensi", "Catatan".
  - Active tab displays the respective fields for ALL students.
  - Inactive tabs hide their fields.
- Auto-save feature (`input` events / debounce timer) must function identically in both layout modes.
- Existing variable names, form structure, action targets, and PHP structures must not be modified.
- Tailwind and standard CSS styles must match the surrounding aesthetics.

## Steps
1. Read `app/Views/evaluasi_wali.php` completely.
2. Add DOM containers for both Mobile layouts (Card Stack vs Tab Kategori).
3. Implement the View Mode Switcher UI and logic in JavaScript at the bottom.
4. Implement the Tab Selection UI and toggling logic.
5. Verify both layout modes update input fields that map to the same `name` fields (e.g. `kelakuan[id_siswa]`, `sakit[id_siswa]`) so the server receives correct POST arrays.
6. Verify auto-save triggers on input edits in both layouts.
7. Stage changes.

## Report Contract
Write to `.superpowers/sdd/task-1-report.md`:
- Status: DONE | DONE_WITH_CONCERNS | NEEDS_CONTEXT | BLOCKED
- Brief before/after outline
- Verification results
Return status and one-line summary.
