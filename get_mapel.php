<?php
require 'koneksi.php';

if (isset($_POST['kelas'])) {
    $kelas = $_POST['kelas'];
    
    // Query untuk mengambil mata pelajaran berdasarkan kelas
    $query = "SELECT * FROM mata_pelajaran WHERE id_kelas = '$kelas' ORDER BY nama_mapel ASC";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) > 0) {
        $no = 1;
        while ($mapel = mysqli_fetch_assoc($result)) {
            $id_mapel = $mapel['id_mapel'];
            echo "<tr>
                    <td class='border p-2'>$no</td>
                    <td class='border p-2'>{$mapel['nama_mapel']}</td>
                    <td class='border p-2'>
                        <input type='number' name='nilai_angka[$id_mapel]' 
                          class='w-full p-2 border rounded' 
                          oninput='convertNilai(this, \"nilai_huruf_$id_mapel\")' required>
                    </td>
                    <td class='border p-2'>
                        <input type='text' id='nilai_huruf_$id_mapel' 
                          name='nilai_huruf[$id_mapel]' 
                          class='w-full p-2 border rounded bg-gray-100' readonly>
                    </td>
                  </tr>";
            $no++;
        }
    } else {
        echo "<tr><td colspan='4' class='text-center p-4'>Tidak ada mata pelajaran untuk kelas ini</td></tr>";
    }
}
?>
