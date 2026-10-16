PERUBAHAN KOSSPACE

1. Dashboard Pengelola sekarang memiliki dua grafik terpisah: Grafik Pemasukan dan Grafik Pengeluaran.
2. Pemasukan dihitung dari pembayaran berstatus 'lunas' berdasarkan tanggal_pembayaran.
3. Pengeluaran dihitung dari tabel pengeluaran berdasarkan tanggal_pengeluaran.
4. Status penghuni otomatis mengikuti pembayaran bulan berjalan:
   - Aktif jika tagihan periode bulan berjalan berstatus 'lunas'.
   - Nonaktif jika belum lunas atau belum ada pembayaran lunas bulan berjalan.
5. Tagihan bulanan tetap dibuat dari bulan tanggal_mulai_tinggal sampai bulan berjalan. Riwayat pembayaran lama tidak dihapus atau di-reset.
6. Penghuni nonaktif tetap dapat login dan membuka halaman pembayaran untuk melihat tagihan. Admin/Pengelola juga dapat memilih penghuni nonaktif saat mencatat atau mengedit pembayaran.
7. Setelah pembayaran ditandai lunas, helper menyinkronkan status penghuni dan status kamar.

CATATAN:
- Aturan status aktif diterapkan untuk bulan berjalan. Pembayaran bulan sebelumnya saja tidak menjadikan penghuni aktif untuk bulan ini.
- Backup ZIP proyek sebelum mengganti file di htdocs.
- Uji di salinan database terlebih dahulu. Jalankan aplikasi dan periksa bahwa tabel pembayaran memiliki kolom tanggal_pembayaran, periode_sewa, jumlah_pembayaran, status_pembayaran dan tabel penghuni memiliki status_penghuni.
