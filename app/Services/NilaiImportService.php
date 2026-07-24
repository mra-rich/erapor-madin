<?php

declare(strict_types=1);

namespace App\Services;

use Shuchkin\SimpleXLSX;

/** Import Excel nilai secara atomik. */
class NilaiImportService
{
    public function __construct(private \mysqli $db)
    {
    }

    /** @return int jumlah siswa berhasil */
    public function import(string $file, int $semester, string $tahunAjaran, int $idPengguna): int
    {
        $xlsx = SimpleXLSX::parse($file);
        if (!$xlsx) {
            throw new \RuntimeException('Tidak dapat membuka file Excel: ' . SimpleXLSX::parseError());
        }
        $rows = $xlsx->rows();
        if (!$rows) {
            throw new \InvalidArgumentException('File Excel kosong.');
        }

        $mapel = [];
        foreach ($rows[0] as $index => $header) {
            if (is_string($header) && str_starts_with($header, 'NILAI_')) {
                $mapel[$index] = (int) substr($header, 6);
            }
        }
        if (!$mapel) {
            throw new \InvalidArgumentException('Kolom nilai tidak ditemukan.');
        }

        $this->db->begin_transaction();
        try {
            $count = 0;
            foreach (array_slice($rows, 1) as $data) {
                $idSiswa = (int) ($data[0] ?? 0);
                if ($idSiswa <= 0) continue;

                $this->deleteOld($idSiswa, $semester, $tahunAjaran);
                $idTransaksi = $this->insertTransaction($idSiswa, $tahunAjaran, $idPengguna, $semester);
                $this->insertAbsensi($idTransaksi, $data);
                $this->insertPersonality($idTransaksi, $data);
                $this->insertNote($idTransaksi, $data);
                $this->insertEkskul($idTransaksi, $data);
                $this->insertGrades($idTransaksi, $mapel, $data);
                $count++;
            }
            $this->db->commit();
            return $count;
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    private function deleteOld(int $idSiswa, int $semester, string $ta): void
    {
        $stmt = $this->db->prepare('SELECT id_transaksi FROM transaksi_raport WHERE id_siswa = ? AND semester = ? AND tahun_ajaran = ?');
        $stmt->bind_param('iis', $idSiswa, $semester, $ta);
        $stmt->execute();
        $result = $stmt->get_result();
        $ids = [];
        while ($row = $result->fetch_assoc()) $ids[] = (int) $row['id_transaksi'];
        $stmt->close();
        foreach ($ids as $id) {
            foreach (['nilai','absensi','kepribadian','catatan_wali_kelas','ekstrakurikuler','transaksi_raport'] as $table) {
                $stmt = $this->db->prepare("DELETE FROM {$table} WHERE id_transaksi = ?");
                $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
            }
        }
    }

    private function insertTransaction(int $idSiswa, string $ta, int $user, int $semester): int
    {
        $stmt = $this->db->prepare('INSERT INTO transaksi_raport (id_siswa, tahun_ajaran, id_pengguna, semester) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('isii', $idSiswa, $ta, $user, $semester); $stmt->execute();
        $id = $this->db->insert_id; $stmt->close(); return $id;
    }

    private function insertAbsensi(int $id, array $d): void
    { $a=(int)($d[3]??0); $s=(int)($d[4]??0); $t=(int)($d[5]??0); $q=$this->db->prepare('INSERT INTO absensi (id_transaksi, izin, sakit, tanpa_keterangan) VALUES (?, ?, ?, ?)'); $q->bind_param('iiii',$id,$a,$s,$t);$q->execute();$q->close(); }
    private function insertPersonality(int $id, array $d): void
    { $a=(string)($d[6]??'');$b=(string)($d[7]??'');$c=(string)($d[8]??'');$q=$this->db->prepare('INSERT INTO kepribadian (id_transaksi, kelakuan, kerajinan, kerapian) VALUES (?, ?, ?, ?)');$q->bind_param('isss',$id,$a,$b,$c);$q->execute();$q->close(); }
    private function insertNote(int $id, array $d): void
    { $v=(string)($d[9]??'');$q=$this->db->prepare('INSERT INTO catatan_wali_kelas (id_transaksi, catatan) VALUES (?, ?)');$q->bind_param('is',$id,$v);$q->execute();$q->close(); }
    private function insertEkskul(int $id, array $d): void
    { $a=(string)($d[10]??'');$b=(string)($d[11]??'');$c=(string)($d[12]??'');if($a===''&&$b===''&&$c==='')return;$q=$this->db->prepare('INSERT INTO ekstrakurikuler (id_transaksi, pramuka, pmr, paskibra) VALUES (?, ?, ?, ?)');$q->bind_param('isss',$id,$a,$b,$c);$q->execute();$q->close(); }
    private function insertGrades(int $id, array $mapel, array $d): void
    { $q=$this->db->prepare('INSERT INTO nilai (id_transaksi, id_mapel, nilai_angka) VALUES (?, ?, ?)');foreach($mapel as $index=>$mapelId){$v=(float)str_replace(',','.',(string)($d[$index]??0));$q->bind_param('iid',$id,$mapelId,$v);$q->execute();}$q->close(); }
}
