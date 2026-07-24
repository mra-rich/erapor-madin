## Plan gambar/mockup berurutan

Output dibuat sebagai artefak visual di folder baru:

```text
public/assets/mockups/
```

Urutan:

1. **Mockup Dashboard Premium**
   - Desktop 1440×900.
   - Gradient stat cards, sidebar, aktivitas, quick actions.
   - File: `mockup-dashboard.html`, `mockup-dashboard.png`.

2. **Mockup Input Nilai Mobile**
   - Mobile 390×844.
   - Alur 2–3 klik: pilih kelas/mapel → list santri → input nilai cepat.
   - File: `mockup-input-nilai-mobile.html`, `mockup-input-nilai-mobile.png`.

3. **Logo E-Rapor Baru**
   - SVG premium: ikon rapor/kitab/check + emerald/blue gradient.
   - File: `logo-erapor-premium.svg`, `logo-erapor-premium.png`.

4. **Banner/Login Background**
   - Visual hero untuk login: madrasah/rapor digital/emerald glass.
   - File: `login-banner.svg`, `login-banner.png`.

5. **Ilustrasi Empty State**
   - Untuk data kosong: santri/nilai/import/cetak.
   - File: `empty-state-rapor.svg`, `empty-state-rapor.png`.

Teknis:
- Buat mockup HTML/SVG statis dulu.
- Render PNG via Playwright screenshot.
- Tidak mengubah halaman produksi dulu; hanya membuat aset/mockup.
- Setelah semua gambar siap, baru kita pilih yang mau diterapkan ke UI nyata.