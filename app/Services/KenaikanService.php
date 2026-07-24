<?php

declare(strict_types=1);

namespace App\Services;

/** Memproses kenaikan kelas atau kelulusan secara transaksional. */
class KenaikanService
{
    public function __construct(private \mysqli $db)
    {
    }

    /** @param int[] $siswaIds */
    public function process(int $kelasAsal, int $kelasTujuan, string $tahunAjaran, array $siswaIds, string $action): int
    {
        if ($kelasAsal <= 0 || !$siswaIds || !in_array($action, ['naik', 'lulus'], true)) {
            throw new \InvalidArgumentException('Data kenaikan kelas tidak valid.');
        }
        if ($action === 'naik' && ($kelasTujuan <= 0 || $tahunAjaran === '')) {
            throw new \InvalidArgumentException('Kelas tujuan dan tahun ajaran wajib diisi.');
        }

        $this->db->begin_transaction();
        try {
            $activeYear = $this->activeYear();
            $this->resetStatuses($kelasAsal, $activeYear);
            $count = $action === 'lulus'
                ? $this->graduate($siswaIds, $activeYear)
                : $this->promote($siswaIds, $kelasTujuan, $tahunAjaran, $activeYear);
            $this->db->commit();
            return $count;
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    private function activeYear(): string
    { $q=$this->db->query('SELECT tahun_ajaran FROM pengaturan LIMIT 1');$r=$q?->fetch_assoc();return (string)($r['tahun_ajaran']??''); }
    private function resetStatuses(int $kelas, string $ta): void
    { $q=$this->db->prepare("UPDATE siswa SET status_kenaikan = 'Tidak' WHERE id_kelas = ?");$q->bind_param('i',$kelas);$q->execute();$q->close();$q=$this->db->prepare("UPDATE riwayat_kelas SET status_kenaikan = 'Tidak' WHERE id_kelas = ? AND tahun_ajaran = ?");$q->bind_param('is',$kelas,$ta);$q->execute();$q->close(); }
    /** @param int[] $ids */
    private function graduate(array $ids, string $ta): int
    { $a=$this->db->prepare("UPDATE siswa SET status = 'Alumni' WHERE id_siswa = ?");$b=$this->db->prepare("UPDATE riwayat_kelas SET status_kenaikan = 'Lulus' WHERE id_siswa = ? AND tahun_ajaran = ?");$n=0;foreach($ids as $id){$id=(int)$id;if($id<=0)continue;$b->bind_param('is',$id,$ta);$b->execute();$a->bind_param('i',$id);$a->execute();$n+=$a->affected_rows>0?1:0;}$a->close();$b->close();return $n; }
    /** @param int[] $ids */
    private function promote(array $ids, int $to, string $newTa, string $oldTa): int
    { $a=$this->db->prepare('UPDATE siswa SET id_kelas = ?, tahun_ajaran = ?, status_kenaikan = \'Naik\' WHERE id_siswa = ?');$b=$this->db->prepare("UPDATE riwayat_kelas SET status_kenaikan = 'Naik' WHERE id_siswa = ? AND tahun_ajaran = ?");$c=$this->db->prepare('INSERT IGNORE INTO riwayat_kelas (id_siswa, id_kelas, tahun_ajaran, status_kenaikan) VALUES (?, ?, ?, NULL)');$n=0;foreach($ids as $id){$id=(int)$id;if($id<=0)continue;$b->bind_param('is',$id,$oldTa);$b->execute();$a->bind_param('isi',$to,$newTa,$id);$a->execute();$n+=$a->affected_rows>0?1:0;$c->bind_param('iis',$id,$to,$newTa);$c->execute();}$a->close();$b->close();$c->close();return $n; }
}
