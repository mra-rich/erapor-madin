<?php
require 'koneksi.php';
require 'cek_sesi.php';
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64">
  <div class="p-4 rounded-lg mt-10">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Input Nilai Santri</h2>

    <form action="proses_input_nilai.php" method="POST">
      <label class="block mb-2 font-medium">Pilih Kelas:</label>
      <select id="kelas" class="w-full p-2 border rounded mb-4" name="kelas" required>
        <option value="" disabled selected>Pilih Kelas</option>
        <?php
        $kelas_query = "SELECT DISTINCT kelas.id_kelas, kelas.nama_kelas 
                        FROM siswa 
                        JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
                        ORDER BY kelas.id_kelas ASC";
        $kelas_result = mysqli_query($koneksi, $kelas_query);
        while ($kelas = mysqli_fetch_assoc($kelas_result)) {
          echo "<option value='{$kelas['id_kelas']}'>{$kelas['nama_kelas']}</option>";
        }
        ?>
      </select>

      <label class="block mb-2 font-medium">Pilih Santri:</label>
      <select name="id_siswa" id="siswa" class="w-full p-2 border rounded mb-4" required>
        <option value="" disabled selected>Pilih Kelas Terlebih Dahulu</option>
      </select>

      <label class="block mb-2 font-medium">Pilih Semester:</label>
      <select name="semester" class="w-full p-2 border rounded mb-4" required>
        <option value="" disabled selected>Pilih Semester</option>
        <option value="1">Semester Ganjil</option>
        <option value="2">Semester Genap</option>
      </select>

      <div id="detail_santri" class="mb-4 p-2 border rounded bg-gray-100 hidden">
        <p><strong>NISN:</strong> <span id="nisn"></span></p>
        <p><strong>Tahun Ajaran:</strong> <span id="tahun_pelajaran"></span></p>
      </div>
      <input type="hidden" name="tahun_ajaran" id="input_tahun_ajaran">
      <input type="hidden" name="id_pengguna" id="id_pengguna" value="<?php echo $_SESSION['id_pengguna'] ?? ''; ?>">



      <table class="w-full border-collapse border border-gray-300 mb-4">
        <thead>
          <tr class="bg-gray-100">
            <th class="border p-2">No</th>
            <th class="border p-2">Mata Pelajaran</th>
            <th class="border p-2">Nilai Angka</th>
            <th class="border p-2">Nilai Huruf</th>
          </tr>
        </thead>
        <tbody id="list_mapel">
          <tr>
            <td colspan="4" class="text-center p-4">Pilih kelas terlebih dahulu</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="2" class="border p-2 font-bold text-right">Jumlah Nilai:</td>
            <td class="border p-2">
              <input type="text" id="jumlah_nilai" class="w-full p-2 border rounded bg-gray-200" readonly>
            </td>
            <td class="border p-2"></td>
          </tr>
        </tfoot>
      </table>

      <h3 class="text-lg font-bold text-gray-800 mb-2">Kepribadian</h3>
      <div class="grid grid-cols-3 gap-4 mb-4">
        <?php
        $aspek = ["kelakuan", "kerajinan", "kerapian"];
        foreach ($aspek as $item) {
        ?>
          <div>
            <label class="block font-medium"><?= ucfirst($item) ?>:</label>
            <select name="<?= $item ?>" class="w-full p-2 border rounded" onchange="updateKepribadian(this, '<?= $item ?>_huruf')" required>
              <option value="" disabled selected>Pilih Nilai</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="D">D</option>
              <option value="E">E</option>
            </select>
            <input type="text" id="<?= $item ?>_huruf" name="<?= $item ?>_huruf" class="w-full p-2 border rounded mt-2 bg-gray-100" readonly>
          </div>
        <?php } ?>
      </div>

      <h3 class="text-lg font-bold text-gray-800 mb-2">Absensi</h3>
      <div class="grid grid-cols-3 gap-4 mb-4">
        <?php
        $absensi = ["izin", "sakit", "tanpa_keterangan"];
        foreach ($absensi as $item) {
        ?>
          <div>
            <label class="block font-medium"><?= ucfirst(str_replace("_", " ", $item)) ?>:</label>
            <input type="number" name="<?= $item ?>" class="w-full p-2 border rounded" placeholder="Jumlah <?= $item ?>" required>
          </div>
        <?php } ?>
      </div>

      <h3 class="text-lg font-bold text-gray-800 mb-2">Catatan Wali Kelas</h3>
      <textarea name="catatan_wali_kelas" class="w-full p-2 border rounded mb-4" placeholder="Masukkan catatan wali kelas..."></textarea>

      <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded">Simpan Nilai</button>
    </form>
  </div>
</div>

<script>
  document.getElementById('kelas').addEventListener('change', function() {
    var kelas = this.value;
    fetch('get_siswa.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'kelas=' + encodeURIComponent(kelas)
      })
      .then(response => response.text())
      .then(data => document.getElementById('siswa').innerHTML = data);

    fetch('get_mapel.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'kelas=' + encodeURIComponent(kelas)
      })
      .then(response => response.text())
      .then(data => document.getElementById('list_mapel').innerHTML = data);
  });

  document.getElementById('siswa').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    document.getElementById('nisn').innerText = selectedOption.getAttribute('data-nisn');
    document.getElementById('tahun_pelajaran').innerText = selectedOption.getAttribute('data-tahun_pelajaran');
    document.getElementById('detail_santri').classList.remove('hidden');
  });

  function updateKepribadian(selectElement, targetId) {
    const nilaiHuruf = {
      "A": "Sangat Baik",
      "B": "Baik",
      "C": "Cukup",
      "D": "Kurang",
      "E": "Sangat Kurang"
    };
    document.getElementById(targetId).value = nilaiHuruf[selectElement.value] || "";
  }

  function terbilang(angka) {
    let satuan = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan"];
    let belasan = ["Sepuluh", "Sebelas", "Dua Belas", "Tiga Belas", "Empat Belas", "Lima Belas", "Enam Belas", "Tujuh Belas", "Delapan Belas", "Sembilan Belas"];
    let puluhan = ["", "", "Dua Puluh", "Tiga Puluh", "Empat Puluh", "Lima Puluh", "Enam Puluh", "Tujuh Puluh", "Delapan Puluh", "Sembilan Puluh"];

    if (angka < 10) {
      return satuan[angka];
    } else if (angka < 20) {
      return belasan[angka - 10];
    } else {
      let puluh = Math.floor(angka / 10);
      let sisa = angka % 10;
      return puluhan[puluh] + (sisa ? " " + satuan[sisa] : "");
    }
  }

  function convertNilai(input, targetId) {
    let nilai = parseInt(input.value) || 0;
    document.getElementById(targetId).value = terbilang(nilai);
  }

  function hitungJumlahNilai() {
    let total = 0;
    let inputs = document.querySelectorAll("input[name^='nilai_angka']"); // Ambil semua input nilai angka

    inputs.forEach(input => {
      let nilai = parseInt(input.value) || 0; // Jika kosong, anggap 0
      total += nilai;
    });

    document.getElementById("jumlah_nilai").value = total; // Tampilkan total nilai
  }

  // Pastikan setiap perubahan di input nilai angka menghitung ulang jumlah nilai
  document.addEventListener("input", function(event) {
    if (event.target.name.startsWith("nilai_angka")) {
      hitungJumlahNilai();
    }
  });
  document.getElementById('siswa').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    var tahunPelajaran = selectedOption.getAttribute('data-tahun_pelajaran');

    document.getElementById('tahun_pelajaran').innerText = tahunPelajaran;
    document.getElementById('input_tahun_ajaran').value = tahunPelajaran;
    document.getElementById('detail_santri').classList.remove('hidden');
  });
</script>

<script>
  // Ambil id_pengguna dari session PHP
  document.addEventListener("DOMContentLoaded", function() {
    var idPengguna = "<?php echo $_SESSION['id_pengguna']; ?>";

    // Masukkan nilai ID pengguna ke dalam input hidden
    document.getElementById("id_pengguna").value = idPengguna;
  });
</script>


<?php include 'include/footer.php'; ?>