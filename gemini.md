PROYEK: Aplikasi Pelaporan Produksi Harian dan Rekapitulasi Lifting Minyak Bumi Berbasis Web
INSTANSI: Dinas Energi dan Sumber Daya Mineral (ESDM) Provinsi Sumatera Selatan
TECH STACK: Laravel (PHP 8.x), MySQL, Vue.js, Tailwind CSS

ROLES & ATURAN AKSES:
1. Operator BKU / Koperasi / UMKM:
   - Menginput laporan produksi & lifting harian per kontrak.
   - TIDAK BISA mengedit/mengubah data laporan harian secara langsung.
   - Jika ada kesalahan data, BKU harus mengajukan "Justifikasi Revisi".
2. Staf Dinas ESDM:
   - Mengelola master data (BKU, Kontrak, Sumur).
   - Meninjau, menyetujui (approve), atau menolak (reject) pengajuan Justifikasi Revisi dari BKU.
   - Eksekusi perubahan data laporan harian hanya terjadi setelah Justifikasi disetujui.

STRUKTUR DATABASE & ENTITAS:
- bku (id, nama, penetapan)
- kontrak (id, nama, keterangan)
- bku_kontrak [Pivot N:M] (id, bku_id, kontrak_id, jumlah_sumur) -> UNIQUE(bku_id, kontrak_id)
- laporan_harian (id, bku_kontrak_id, tanggal, total_produksi, total_lifting, keterangan) -> UNIQUE(bku_kontrak_id, tanggal)
- sumur (id, bku_kontrak_id, nama_sumur, desa, kecamatan, kabupaten, latitude, longitude)
- justifikasi_revisi (id, laporan_harian_id, bku_id, alasan_revisi, produksi_usulan, lifting_usulan, status ['pending','approved','rejected'], ditinjau_oleh [FK users.id], catatan_dinas)
- audit_logs (id, user_id, action, auditable_type, auditable_id, old_values [JSON], new_values [JSON], ip_address, user_agent)