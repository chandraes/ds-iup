
## About DS IUP (Indosupply Universal Perkasa)

- Laravel UI for Authentication.
- Laravel LANG for Language.
- Cleave JS for input masking.
- Barryvdh/laravel-dompdf for generate PDF.
- Barryvdh/laravel-debugbar

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


## Pembaruan pegawai — 16 Oktober 2026

CRUD pegawai dirapikan pada halaman db/staff: DataTables server dengan pagination10/25/50/100, pencarian nama/panggilan/jabatan, default aktif dan pilihan seluruh status. Form create/edit berbagi db.karyawan.form, memakai Select2 jabatan lokal, kelompok biodata/gaji/identitas/rekening/dokumen, preview foto, SweetAlert dan overlay blokir mutasi. Jabatan terpakai tidak dapat dihapus; nilai BPJS0 tetap nonaktif. Penggantian/penghapusan foto lama dilakukan setelah transaksi berhasil. Tiga tes EmployeeCrudTest memakai SQLite in-memory khusus dan tidak menyentuh database aplikasi;13assertions lulus. Perusahaan IUP Distributor telah dibuat di VPS absensi (slug iup-distributor); konektor dan token belum disiapkan, belum sinkronisasi pegawai. Perubahan CRUD ini lokal; belum deploy ke server IUP.

Konvensi tampilan tabel: gunakan `table-success` pada `thead` untuk pengembangan berikutnya. Kolom pertama daftar pegawai berlabel “No” dan menampilkan nomor urut sesuai pagination, bukan nomor identitas pegawai.

Tombol CRUD pegawai menggunakan ikon Font Awesome 4.7 yang sudah tersedia, dengan label teks tetap ditampilkan dan ikon dekoratif `aria-hidden`.

Daftar pegawai menggunakan infinite scroll DataTables dengan pengambilan 25 baris per permintaan server, tanpa kontrol pagination. Pencarian, urutan, dan perubahan status memulai ulang hasil. Informasi bank memakai label Bank, Nomor Rekening, dan Nama Rekening. Gunakan pola pemuatan bertahap ini untuk tabel berikutnya.

Area scroll daftar pegawai mengikuti pola SAIND: `scrollY`/`scrollX` DataTables memisahkan header dari badan tabel sehingga header tetap terlihat. Pengambilan data bertahap tetap dipertahankan tanpa dependensi tambahan.

Pegawai: tindakan utama Nonaktifkan/Aktifkan kembali mempertahankan riwayat dan penugasan. Akun web terkait pegawai nonaktif ditolak saat login dan permintaan terautentikasi berikutnya. Hapus permanen tersedia di Koreksi salah input, wajib alasan dan server menolak jika ditemukan keterikatan karyawan_id pada tabel mana pun atau rekap gaji atas nama yang sama (pemeriksaan konservatif karena rekap menyimpan snapshot tanpa ID). Tidak ada batas usia pembuatan. Konektor absensi IUP belum aktif; sinkronisasi status mobile belum tersedia.

## Konektor absensi IUP — 16 Oktober 2026

Konektor lokal aktif ke https://absensi.ds-groups.com dengan token khusus perusahaan IUP Distributor (slug iup-distributor), disimpan hanya di .env dan penyimpanan privat VPS. Sinkronisasi awal: 11 pegawai terkirim, 0 tertunda. Tidak ada gaji, rekening, foto, ataupun password dalam payload. Payload identitas menggunakan ID sumber IUP, nomor, nama, jabatan, status 1/0 menjadi active/inactive, dan tanggal mulai bekerja.

Panel admin: db/staff/attendance-integration, tersedia dari daftar pegawai. Panel menyediakan sinkronisasi manual, status pengiriman bertahap, serta pembacaan hasil absensi/cuti/saldo cuti. Akses dibatasi role su/admin. Perubahan model ditangkap observer; pengiriman setelah commit. Antrean menyimpan versi, validasi acknowledgment, lease pengiriman, dan backoff retry. Rekonsiliasi mendeteksi perubahan langsung database dan nama jabatan. Pegawai yang sudah terkirim ke absensi tidak boleh dihapus permanen; gunakan Nonaktifkan.

Perintah operasional: php artisan attendance:sync-employees --send; php artisan attendance:flush; php artisan attendance:results. Saat deploy IUP nanti, jalankan migrasi dan pasang ATTENDANCE_* secara privat pada server IUP. Scheduler server harus menjalankan php artisan schedule:run setiap menit agar retry tiap menit dan rekonsiliasi tiap lima menit berjalan. Tidak ada cron server IUP yang dipasang dalam pekerjaan lokal ini; konektor belum dideploy ke server IUP. Jangan mengaktifkan dua sumber database IUP berbeda dengan token yang sama karena ID sumber/versinya dapat bertabrakan. Database produksi IUP harus menjadi satu sumber setelah cutover, dengan versi outbox dipertahankan.

UI integrasi IUP diseragamkan dengan SAIND: kartu ringkasan, tabel status, filter Select2 dan tabel hasil bertahap, header table-success serta konfirmasi/overlay global pada sinkronisasi. Menggunakan aset lokal yang sudah tersedia.


## Integrasi penggajian dan BBM — 24 Oktober 2026
Form gaji mengambil snapshot absensi secara manual melalui Perbarui data absensi; tidak melakukan HTTP per pegawai/per halaman. Periode26bulanlalu–25bulangaji, ID sumber sama dengan karyawans.id. ATTENDANCE_ENABLED=false membuat potongan absensi/BBM0 tanpa HTTP. Jikaaktif, snapshot harus mencakup semua pegawaiaktif dan hari periode, schema_version1/monitoring_version1/fuel_allowance_version1. Data belumsiap, izinpending, shiftbelumditetapkan, kegagalan koneksi, revision berubah, atau payloadtakvalid memblokir gaji. Snapshot lokal payroll_attendance_periods menyimpan request_id tetap untuk retry lock. Lock request dipersist sebelumHTTP; timeout/pemrosesanlokalgagal tidak membuang identitas transaksi. Retry memakai request_id/revision sama; setelah localrekap berhasil periodesealed. HTTP409 meminta refresh; locktakpasti tidakbolehrefresh sembarang.
Non-sales: basis gajipokok+tunjanganjabatan+tunjangankeluarga; harian dibulatkan basis/25. Absent1hari, half_day1/2hari, present proporsional(late+early)/(scheduled-break), maksimal1hari; totalmaksimalfloor25%basis. Tidak ada potongan ganda. Sales: potonganabsensi0, tarif harian dari karyawans.tunjangan_bbm saatgajidiproses untuk seluruhperiode. BBM hanya pada hari kerja yang memiliki checkin dan statuspresent/half_day; keterlambatan masuk setelah toleransi mengurangi proporsional terhadap menit efektif, dibatasi100%BBMharian; tidakada capbulanan25%. Tanpapulang/pulangcepat tidakmengurangiBBM. Cuti/izin/libur/tanpamasuk0. BBMbersih menambahpenghasilan sebelum potonganBPJS/kasbon; tarif/nilaiharian/rinciantersimpan di rekap_gaji_details.rincian_absensi dengan attendance_revision dan karyawan_id. Klik nominal PotonganAbsensi(non-sales) atauBBM(sales), termasuk0setelahdihitung, untukmodalrincianhari. Rekaplama tidak diubah dan rincianbaru tetap dapatdibaca jika tarif/statuspegawai/integrasi berubah.
Migrasi2026_10_24_214601 diterapkan lokal setelahbackup privat storage/app/private/backups/before-payroll-attendance-*.sql; hitungankaryawan,rekap,detail,kasbon,kasbesar,invoice sama. Test73/660assertionslulus; backendpendukung13/125assertionslulus. Browsermengonfirmasi blokir saat belumadasnapshot dan Swal pembaruan(batal, tidak melakukan transaksi). Tidakmengubahnilaiupah/tarif/kas/datapegawaioperasional; tidakdeployserver. Sebelum digunakan pada server, deploypayloadbaru PayrollSnapshotService diabsensi dan migrasikodeIUP; backendversilama akan ditolak secaraaman. Snapshotabsensiterkunci sebelumnya tetapdikembalikanutuh; draftsnapshotbelumterkunci harusdiperbarui jika revision berubah.


24 Oktober 2026 — Backend payroll IUP berhasil dideploy: commit b3e9d10b1c4e2bd8808b7cf1f49343a49f6cb239, release /var/www/absensi/releases/20261024150039-b3e9d10b1c4e, backup lengkap /var/backups/absensi/20261024150035. Payload payroll menambahkan fuel_allowance_version=1 dan check_in_late_minutes (keterlambatan masuk setelah toleransi, termasuk catatan half_day) untuk perhitungan BBM sales di IUP. Aturan late_minutes lama dan snapshot yang sudah terkunci dipertahankan. Tidak ada migrasi baru; jumlah 51 pegawai, 232 absensi, 5 pengajuan cuti/izin, 0 kunci gaji tetap. Nginx, MariaDB, PHP-FPM, queue dan face aktif; /up dan /login HTTP 200. Tes backend terkait 13 lulus/125 assertions sebelum deploy. Tidak melakukan pembayaran gaji atau perubahan data pegawai. IUP lokal dapat menarik ulang snapshot; kode IUP server belum dideploy. Tidak memerlukan build Android/iOS.


26 Oktober 2026 — Peralihan integrasi IUP dari dev ke production selesai atas izin pemilik. Sumber /var/www/dev_distributor dan tujuan /var/www/iup_distributor pada VPS 31.97.111.53. Hanya Ade Zulkarnain ID20 dan PINI ID21 beserta empat lampiran foto KTP/diri disalin, dengan relasi jabatan yang telah dicocokkan. Backup privat terbatas dua record dan metadata sync berada pada storage/app/private/backups/iup-handover-20261026T081050Z di production; tidak membuat backup .env atau database penuh. Kenes Pranowo source1/internal96 di perusahaan IUP Distributor ID2 absensi dihapus sesuai instruksi, setelah dipastikan tanpa akun/data terkait dan dibuat backup privat record. Data perusahaan lain tidak diubah. Production dan perusahaan IUP di absensi kini sama-sama15pegawai, tidak ada perbedaan ID/nama/status, versi tertinggal atau antrean/error. Token sama dipertahankan, ATTENDANCE_ENABLED dev=false, production=true dan send_immediately=true. Observer saved/deleted dan transaksi afterCommit sudah terpasang; tambah/edit/nonaktif melalui CRUD mengirim perubahan identitas/status, bukan gaji/rekening/lampiran. Cron khusus /etc/cron.d/iup-attendance menjalankan schedule:run sebagai www-data per menit hanya pada production; retry tiap menit dan rekonsiliasi lima menit, withoutOverlapping. Eksekusi scheduler berhasil; kedua web merespons302 ke login normal. Tes lokal EmployeeCrudTest17lulus/172assertions; tidak mengubah nama/status pegawai operasional untuk pengujian.


26 Oktober 2026 — Dasar BPJS pada penggajian IUP lokal kini gaji_pokok + tunjangan_jabatan + tunjangan_keluarga. Tarif tetap: TK perusahaan4,89%/pegawai2%, Kesehatan perusahaan4%/pegawai1%; pembulatan rupiah penuh dan flag kepesertaan masing-masing dipertahankan. Komisi/BBM tidak menjadi dasar. Perubahan berlaku pada pratinjau dan proses gaji baru, tidak menghitung ulang rekap tersimpan. Tes KasbonTest54lulus/452assertions mencakup semua kombinasi kepesertaan, gaji pokok nol dengan tunjangan tetap, penyimpanan nominal dan ketetapan riwayat setelah data pegawai berubah. Pint lulus. Belum deploy production.


26 Oktober 2026 — Dasar BPJS Kesehatan IUP lokal dibatasi maksimal Rp12.000.000 dari gaji pokok+tunjangan jabatan+tunjangan keluarga, untuk perusahaan4% dan pegawai1%. Maksimum nominal masing-masing Rp480.000 dan Rp120.000. BPJS-TK tetap memakai total komponen tetap tanpa batas ini. Flag kepesertaan dan rekap lama dipertahankan. Tes sebelum perubahan membuktikan kasus dasar Rp15juta gagal pada nominal kesehatan; sesudah perubahan seluruh KasbonTest57lulus/467assertions, Pint lulus. Belum deploy production.
