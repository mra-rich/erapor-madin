# Kumpulan Aturan Main (Wajib Dipatuhi)

1. **Role Access (Keamanan):**
   - Kepala Madrasah (`kepala_madrasah`) HANYA BOLEH MELIHAT (Read-Only). Jangan pernah berikan tombol Edit/Delete/Tambah untuk role ini di halaman manapun!
   - Wali Kelas (`wali_kelas`) tidak boleh mengatur Master Mapel atau menghubungkan Mapel dengan Guru. Fitur Pengaturan Mata Pelajaran HANYA eksklusif untuk Admin.
   - Admin memiliki akses penuh (Read/Write) ke semua fitur.

2. **Aturan Database (Penting!):**
   - Kolom `nama_kitab` **WAJIB** berada di tabel `pengampu_mapel` (Bukan di `mata_pelajaran`), karena setiap kelas harus bisa memiliki kitab yang berbeda-beda untuk mata pelajaran yang sama (meskipun ID Mapel-nya sama).

3. **Standar ID Mata Pelajaran (PAKEM):**
   - ID Mapel **WAJIB** mengikuti urutan mutlak berikut ini dan tidak boleh diubah:
     1=Ilmu Tafsir, 2=Ilmu Hadits, 3=Hadits, 4=Tauhid, 5=Akhlaq, 6=Fiqhi, 7=Ushul Fiqhi, 8=Qowaidul Fiqhi, 9=Faroidl, 10=Balaghoh, 11=Nahwu, 12=Shorof, 13=I'lal, 14=Bahasa Arab, 15=Pego, 16=Tajwid, 17=Tarekh, 18=Fasholatan, 19=Al-Qur'an, 20=Tes Lisan.
   - Urutan tampilan di UI (tabel, cetak, dll) harus selalu diurutkan berdasarkan `id_mapel ASC`.

4. **Log Aktivitas:**
   - Ketika memasukkan data ke tabel `log_aktivitas`, JANGAN menggunakan nama kolom `id_log`. Gunakan kolom `id` saja atau abaikan auto-incrementnya. Query yang benar: `INSERT INTO log_aktivitas (id_pengguna, aktivitas, tabel_terkait, waktu) VALUES (...)`

5. **Standar Desain UI (KONSISTENSI HARUS SAMA DI SEMUA HALAMAN):**
   - **Tombol Utama (Tambah Data dll)**: Wajib Solid Blue. Class: `text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-xl text-sm px-6 py-2.5 shadow-lg shadow-blue-500/30`
   - **Tombol Import (Di Halaman Utama)**: Wajib Ghost Emerald. Class: `text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 focus:ring-2 focus:ring-emerald-300 font-medium rounded-lg text-sm px-4 py-2.5`
   - **Tombol Export (Di Halaman Utama)**: Wajib Ghost Indigo. Class: `text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 focus:ring-2 focus:ring-indigo-300 font-medium rounded-lg text-sm px-4 py-2.5`
   - **Tombol Download Template (Di Modal Import)**: Wajib Ghost Emerald. Class: `text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 font-medium rounded-xl`
   - **Header Modal Import**: Wajib menggunakan gradasi emerald. Class: `bg-gradient-to-r from-emerald-50 to-teal-50`
