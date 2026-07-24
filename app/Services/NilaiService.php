<?php

declare(strict_types=1);

namespace App\Services;

/** Menyimpan satu transaksi rapor beserta data turunannya secara atomik. */
class NilaiService
{
    public function __construct(private \mysqli $db)
    {
    }

    /** @param array<int|string, mixed> $input */
    public function create(array $input, int $idPengguna): int
    {
        $idSiswa = (int) ($input['id_siswa'] ?? 0);
        $tahunAjaran = trim((string) ($input['tahun_ajaran'] ?? ''));
        $semester = (int) ($input['semester'] ?? 0);
        $nilaiMapel = $input['nilai_angka'] ?? [];

        if ($idSiswa <= 0 || $tahunAjaran === '' || !in_array($semester, [1, 2], true)) {
            throw new \InvalidArgumentException('Data siswa, tahun ajaran, atau semester tidak valid.');
        }
        if (!is_array($nilaiMapel) || $nilaiMapel === []) {
            throw new \InvalidArgumentException('Nilai mata pelajaran wajib diisi.');
        }

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO transaksi_raport (id_siswa, tahun_ajaran, id_pengguna, semester) VALUES (?, ?, ?, ?)'
            );
            $stmt->bind_param('isii', $idSiswa, $tahunAjaran, $idPengguna, $semester);
            $stmt->execute();
            $idTransaksi = $this->db->insert_id;
            $stmt->close();

            $this->insertAbsensi($idTransaksi, $input);
            $this->insertKepribadian($idTransaksi, $input);
            $this->insertCatatan($idTransaksi, $input);
            $this->insertEkskul($idTransaksi, $input);
            $this->insertNilai($idTransaksi, $nilaiMapel);

            $this->db->commit();
            return $idTransaksi;
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /** @param array<int|string, mixed> $input */
    private function insertAbsensi(int $idTransaksi, array $input): void
    {
        $izin = max(0, (int) ($input['izin'] ?? 0));
        $sakit = max(0, (int) ($input['sakit'] ?? 0));
        $tanpaKeterangan = max(0, (int) ($input['tanpa_keterangan'] ?? 0));
        $stmt = $this->db->prepare(
            'INSERT INTO absensi (id_transaksi, izin, sakit, tanpa_keterangan) VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('iiii', $idTransaksi, $izin, $sakit, $tanpaKeterangan);
        $stmt->execute();
        $stmt->close();
    }

    /** @param array<int|string, mixed> $input */
    private function insertKepribadian(int $idTransaksi, array $input): void
    {
        $kelakuan = trim((string) ($input['kelakuan'] ?? ''));
        $kerajinan = trim((string) ($input['kerajinan'] ?? ''));
        $kerapian = trim((string) ($input['kerapian'] ?? ''));
        $stmt = $this->db->prepare(
            'INSERT INTO kepribadian (id_transaksi, kelakuan, kerajinan, kerapian) VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('isss', $idTransaksi, $kelakuan, $kerajinan, $kerapian);
        $stmt->execute();
        $stmt->close();
    }

    /** @param array<int|string, mixed> $input */
    private function insertCatatan(int $idTransaksi, array $input): void
    {
        $catatan = trim((string) ($input['catatan_wali_kelas'] ?? ''));
        $stmt = $this->db->prepare('INSERT INTO catatan_wali_kelas (id_transaksi, catatan) VALUES (?, ?)');
        $stmt->bind_param('is', $idTransaksi, $catatan);
        $stmt->execute();
        $stmt->close();
    }

    /** @param array<int|string, mixed> $input */
    private function insertEkskul(int $idTransaksi, array $input): void
    {
        $pramuka = trim((string) ($input['ekskul_pramuka'] ?? ''));
        $pmr = trim((string) ($input['ekskul_pmr'] ?? ''));
        $paskibra = trim((string) ($input['ekskul_paskibra'] ?? ''));
        if ($pramuka === '' && $pmr === '' && $paskibra === '') {
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO ekstrakurikuler (id_transaksi, pramuka, pmr, paskibra) VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('isss', $idTransaksi, $pramuka, $pmr, $paskibra);
        $stmt->execute();
        $stmt->close();
    }

    /** @param array<int|string, mixed> $nilaiMapel */
    private function insertNilai(int $idTransaksi, array $nilaiMapel): void
    {
        $stmt = $this->db->prepare('INSERT INTO nilai (id_transaksi, id_mapel, nilai_angka) VALUES (?, ?, ?)');
        foreach ($nilaiMapel as $idMapel => $nilai) {
            $idMapel = (int) $idMapel;
            if ($idMapel <= 0 || !is_numeric($nilai)) {
                throw new \InvalidArgumentException('Data nilai mata pelajaran tidak valid.');
            }
            $nilaiAngka = (float) $nilai;
            $stmt->bind_param('iid', $idTransaksi, $idMapel, $nilaiAngka);
            $stmt->execute();
        }
        $stmt->close();
    }
}
