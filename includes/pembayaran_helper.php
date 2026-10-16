<?php

function pastikanTagihanBulanIni(PDO $pdo): void
{
    $penghuniStmt = $pdo->query(
        "SELECT p.id_penghuni, p.id_kamar, p.tanggal_mulai_tinggal, k.harga_sewa
         FROM penghuni p
         LEFT JOIN kamar k ON k.id_kamar = p.id_kamar"
    );
    $semuaPenghuni = $penghuniStmt->fetchAll(PDO::FETCH_ASSOC);
    $bulanBerjalan = new DateTimeImmutable('first day of this month');
    $periodeSekarang = $bulanBerjalan->format('Y-m-d');

    $cek = $pdo->prepare(
        'SELECT COUNT(*) FROM pembayaran
         WHERE id_penghuni = :id_penghuni AND periode_sewa = :periode'
    );
    $insert = $pdo->prepare(
        "INSERT INTO pembayaran
         (id_penghuni, tanggal_pembayaran, periode_sewa, jumlah_pembayaran, status_pembayaran)
         VALUES (:id_penghuni, NULL, :periode_sewa, :jumlah_pembayaran, 'belum_bayar')"
    );

    foreach ($semuaPenghuni as $penghuni) {
        $idPenghuni = (int)$penghuni['id_penghuni'];
        $idKamar = (int)($penghuni['id_kamar'] ?? 0);
        $hargaSewa = (float)($penghuni['harga_sewa'] ?? 0);
        if ($hargaSewa > 0) {
            try {
                $tanggalMulai = !empty($penghuni['tanggal_mulai_tinggal'])
                    ? new DateTimeImmutable($penghuni['tanggal_mulai_tinggal'])
                    : $bulanBerjalan;
                $periodeMulai = $tanggalMulai->modify('first day of this month');
            } catch (Exception $e) {
                $periodeMulai = $bulanBerjalan;
            }

            if ($periodeMulai <= $bulanBerjalan) {
                for ($periode = $periodeMulai; $periode <= $bulanBerjalan; $periode = $periode->modify('+1 month')) {
                    $periodeDate = $periode->format('Y-m-d');
                    $cek->execute(['id_penghuni' => $idPenghuni, 'periode' => $periodeDate]);
                    if ((int)$cek->fetchColumn() > 0) continue;

                    try {
                        $insert->execute([
                            'id_penghuni' => $idPenghuni,
                            'periode_sewa' => $periodeDate,
                            'jumlah_pembayaran' => $hargaSewa,
                        ]);
                    } catch (PDOException $e) {
                      
                        try {
                            $fallback = $pdo->prepare(
                                "INSERT INTO pembayaran
                                 (id_penghuni, tanggal_pembayaran, periode_sewa, jumlah_pembayaran, status_pembayaran)
                                 VALUES (:id_penghuni, CURDATE(), :periode_sewa, :jumlah_pembayaran, 'belum_bayar')"
                            );
                            $fallback->execute([
                                'id_penghuni' => $idPenghuni,
                                'periode_sewa' => $periodeDate,
                                'jumlah_pembayaran' => $hargaSewa,
                            ]);
                        } catch (PDOException $fallbackError) {
                            error_log('Gagal membuat tagihan penghuni '.$idPenghuni.' periode '.$periodeDate.': '.$fallbackError->getMessage());
                        }
                    }
                }
            }
        }

        
        $cekLunas = $pdo->prepare(
            "SELECT COUNT(*) FROM pembayaran
             WHERE id_penghuni = :id_penghuni
               AND periode_sewa = :periode
               AND status_pembayaran = 'lunas'"
        );
        $cekLunas->execute(['id_penghuni' => $idPenghuni, 'periode' => $periodeSekarang]);
        $aktif = (int)$cekLunas->fetchColumn() > 0;
        $ubahStatus = $pdo->prepare('UPDATE penghuni SET status_penghuni = :status WHERE id_penghuni = :id');
        $ubahStatus->execute(['status' => $aktif ? 'aktif' : 'nonaktif', 'id' => $idPenghuni]);

        if ($idKamar > 0) {
            $sinkronKamar = $pdo->prepare(
                "UPDATE kamar SET status_kamar = CASE
                    WHEN EXISTS (SELECT 1 FROM penghuni WHERE id_kamar = :id_aktif AND status_penghuni = 'aktif')
                    THEN 'terisi' ELSE 'tersedia' END
                 WHERE id_kamar = :id_update"
            );
            $sinkronKamar->execute(['id_aktif' => $idKamar, 'id_update' => $idKamar]);
        }
    }
}
?>