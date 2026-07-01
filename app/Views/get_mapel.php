<?php
require 'koneksi.php';

if (isset($_POST['kelas'])) {
    $kelas = $_POST['kelas'];
    $id_pengguna = $_SESSION['id_pengguna'] ?? 0;
    $peran = $_SESSION['peran'] ?? '';
    
    // Query untuk mengambil mata pelajaran berdasarkan kelas
    if ($peran == 'Admin' || $peran == 'Kepala Madrasah') {
        $query = "SELECT m.* FROM mata_pelajaran m 
                  JOIN pengampu_mapel pm ON m.id_mapel = pm.id_mapel 
                  WHERE pm.id_kelas = '$kelas' AND pm.status = 'Aktif' AND m.status = 'Aktif' 
                  ORDER BY m.nama_mapel ASC";
    } else {
        // Guru / Wali Kelas hanya bisa input nilai mapel yang diampunya
        $query = "SELECT mp.* FROM mata_pelajaran mp 
                  JOIN pengampu_mapel pm ON mp.id_mapel = pm.id_mapel 
                  WHERE pm.id_kelas = '$kelas' AND pm.id_guru = '$id_pengguna' AND pm.status = 'Aktif' AND mp.status = 'Aktif'
                  ORDER BY mp.nama_mapel ASC";
    }
    
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) > 0) {
        $no = 1;
        while ($mapel = mysqli_fetch_assoc($result)) {
            $id_mapel = $mapel['id_mapel'];
            echo "<tr class='hover:bg-slate-50 transition-colors group'>
                    <td class='py-2 px-6 border border-slate-300 text-center font-medium text-slate-900'>$no</td>
                    <td class='py-2 px-6 border border-slate-300 font-bold text-slate-800'>{$mapel['nama_mapel']}</td>
                    <td class='py-2 px-6 border border-slate-300'>
                        <input type='number' name='nilai_angka[$id_mapel]' 
                          class='bg-gray-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm text-center font-bold' 
                          oninput='convertNilai(this, \"nilai_huruf_$id_mapel\")' required>
                    </td>
                    <td class='py-2 px-6 border border-slate-300'>
                        <input type='text' id='nilai_huruf_$id_mapel' 
                          name='nilai_huruf[$id_mapel]' 
                          class='bg-slate-100 border-none text-slate-500 text-sm rounded-xl block w-full p-2.5 shadow-inner text-center font-semibold pointer-events-none' readonly>
                    </td>
                  </tr>";
            $no++;
        }
    } else {
        echo "<tr><td colspan='4' class='text-center p-8 text-slate-500 border border-slate-300'>Tidak ada mata pelajaran untuk kelas ini</td></tr>";
    }
}
?>
