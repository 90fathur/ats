-- ATS POLMANKAB DATABASE DUMP
-- Exported on 2026-11-01 08:47:31

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USER',
  `kecamatan_akses` text COLLATE utf8mb4_unicode_ci COMMENT 'Daftar kecamatan yang dapat diakses (koma dipisahkan atau JSON, null = semua)',
  `desa_akses` text COLLATE utf8mb4_unicode_ci COMMENT 'Daftar desa/kelurahan yang dapat diakses (koma dipisahkan, null = semua desa)',
  `hp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `username`, `password`, `nama`, `role`, `kecamatan_akses`, `desa_akses`, `hp`, `created_at`, `updated_at`) VALUES
('1', 'admin', '$2y$12$IQ2iyS5b43/Wwu8jD8/3j.9hhF9FKN1CS6OiHQW2qSuSJGVKvEin2', 'Administrator ATS Polman', 'ADMIN', NULL, NULL, '08114200000', '2026-11-01 16:32:04', '2026-11-01 16:32:04'),
('2', 'petugas_madatte', '$2y$12$HnCebZYqKbFdA45g14WUSehG71yTSVJzzwjDBfmaSw8zw.5.GJSBG', 'Tim Entri Kelurahan Madatte', 'USER', 'Polewali', 'Madate', '085145888881', '2026-11-01 16:32:04', '2026-11-01 16:32:04'),
('3', 'petugas_pekkabata', '$2y$12$XnCUWUniKEnhPKV17bgnn.GarGABJ74BYGER5DNYvjSfUt/Sn52Ey', 'Tim Entri Kel. Pekkabata & Takatidung', 'USER', 'Polewali', 'Pekkabata,Takatidung', '082198765432', '2026-11-01 16:32:04', '2026-11-01 16:32:04'),
('4', 'petugas_wonomulyo', '$2y$12$LKNuY3HMF1iDS2dObvWrjuxcT3nBx88lw9sRpnmlNWm2kyDziX2v6', 'Tim Entri Desa Sidorejo', 'USER', 'Wonomulyo', 'Sidorejo', '085299887711', '2026-11-01 16:32:04', '2026-11-01 16:32:04');

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_petugas` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USER',
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik_target` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_anak` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kecamatan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `desa` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `action_created_at` (`action`,`created_at`),
  KEY `username_created_at` (`username`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `ats_data`;
CREATE TABLE `ats_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `waktu_input` datetime DEFAULT NULL,
  `jenis_input` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DATA BARU',
  `kabupaten` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kecamatan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_petugas` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp_petugas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nisn` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_lengkap` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_kelamin` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `umur` int(3) NOT NULL,
  `disabilitas` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TIDAK',
  `no_hp_ortu` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ibu` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_sekolah` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sekolah_aktif` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pendidikan_tertinggi` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asal_sekolah` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_tdk_sekolah` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alasan` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alasan_lainnya` text COLLATE utf8mb4_unicode_ci,
  `menerima_kip` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keinginan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bentuk_pendidikan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `satuan_pendidikan` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ukuran_baju` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ukuran_sepatu` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterampilan` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_anak` longtext COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nik` (`nik`),
  KEY `kabupaten` (`kabupaten`),
  KEY `status_sekolah` (`status_sekolah`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ats_data` (`id`, `waktu_input`, `jenis_input`, `kabupaten`, `kecamatan`, `desa`, `nama_petugas`, `no_hp_petugas`, `nisn`, `nik`, `nama_lengkap`, `jenis_kelamin`, `tanggal_lahir`, `umur`, `disabilitas`, `no_hp_ortu`, `nama_ibu`, `status_sekolah`, `sekolah_aktif`, `pendidikan_tertinggi`, `asal_sekolah`, `status_tdk_sekolah`, `alasan`, `alasan_lainnya`, `menerima_kip`, `keinginan`, `bentuk_pendidikan`, `satuan_pendidikan`, `ukuran_baju`, `ukuran_sepatu`, `keterampilan`, `foto_anak`, `created_by`, `created_at`, `updated_at`) VALUES
('1', '2026-10-20 09:15:00', 'DATA BARU', 'POLEWALI MANDAR', 'POLEWALI', 'MANDING', 'ADMINISTRATOR', '085145888881', '0089123456', '7602011203080001', 'MUHAMMAD RIFKI', 'LAKI-LAKI', '2010-03-12', '16', 'TIDAK', '081234567890', 'NURHAYATI', 'TIDAK (ATS)', '-', 'SMP/MTS SEDERAJAT', 'SMPN 1 POLEWALI', 'LULUS TIDAK MELANJUTKAN', 'TIDAK ADA BIAYA', NULL, 'TIDAK', 'INGIN KEMBALI BERSEKOLAH', 'FORMAL', 'SMKN 1 POLEWALI', 'M', '40', NULL, NULL, 'admin', '2026-10-20 09:15:00', '2026-10-20 09:15:00'),
('2', '2026-10-21 10:30:00', 'REKONFIRMASI (DATA DASHBOARD ATS)', 'POLEWALI MANDAR', 'CAMPALAGIAN', 'BONDE', 'ADMINISTRATOR', '085145888881', '0112345678', '7602035507110002', 'SITI AISYAH', 'PEREMPUAN', '2012-07-15', '14', 'TIDAK', '082198765432', 'MARIAM', 'TIDAK (ATS)', '-', 'SD/MI SEDERAJAT', 'SDN 012 CAMPALAGIAN', 'PUTUS SEKOLAH (DO)', 'BEKERJA', NULL, 'YA', 'INGIN KEMBALI BERSEKOLAH', 'NON FORMAL', 'PKBM SIPATUO CAMPALAGIAN (PAKET B)', 'S', '36', NULL, NULL, 'admin', '2026-10-21 10:30:00', '2026-10-21 10:30:00'),
('3', '2026-10-22 14:00:00', 'DATA PENDAMPING DESA', 'POLEWALI MANDAR', 'TINAMBUNG', 'BATULAYA', 'ADMINISTRATOR', '085145888881', '-', '7602040802090003', 'FAHRI RAMADHAN', 'LAKI-LAKI', '2009-02-08', '17', 'TIDAK', '085241112233', 'HASNAH', 'TIDAK (ATS)', '-', 'SMP/MTS SEDERAJAT', 'SMPN 1 TINAMBUNG', 'PUTUS SEKOLAH (DO)', 'BEKERJA', NULL, 'TIDAK', 'TIDAK INGIN', '-', '-', '-', NULL, 'TEKNIK OTOMOTIF / PERBENGKELAN', NULL, 'admin', '2026-10-22 14:00:00', '2026-10-22 14:00:00'),
('4', '2026-10-23 11:20:00', 'DATA BARU', 'POLEWALI MANDAR', 'WONOMULYO', 'BUMIAYU', 'ADMINISTRATOR', '085145888881', '0134567891', '7602052410130004', 'ALVARO GABRIEL PAKAN', 'LAKI-LAKI', '2013-10-24', '13', 'TIDAK', '0895339997976', 'RIBKA SIREGAR', 'YA, MASIH SEKOLAH', 'SMPN 1 WONOMULYO', '-', '-', '-', '-', NULL, '-', '-', '-', '-', '-', NULL, '-', NULL, 'admin', '2026-10-23 11:20:00', '2026-10-23 11:20:00'),
('5', '2026-10-24 08:45:00', 'REKONFIRMASI (DATA DASHBOARD ATS)', 'POLEWALI MANDAR', 'BALANIPA', 'LAMBANAN', 'ADMINISTRATOR', '085145888881', '-', '7602064905150005', 'DEWI ANGGRAENI', 'PEREMPUAN', '2015-05-09', '11', 'TIDAK', '081399887766', 'SUTINAH', 'TIDAK (ATS)', '-', 'BPB', '-', '-', 'TIDAK ADA BIAYA', NULL, 'TIDAK', 'INGIN KEMBALI BERSEKOLAH', 'FORMAL', 'SDN 005 BALANIPA', 'S', '33', NULL, NULL, 'admin', '2026-10-24 08:45:00', '2026-10-24 08:45:00'),
('6', '2026-10-25 13:10:00', 'DATA BARU', 'POLEWALI MANDAR', 'MATAKALI', 'PASIANG', 'ADMINISTRATOR', '085145888881', '0071239845', '7602071808070006', 'YOHANES PAEBONG', 'LAKI-LAKI', '2007-08-18', '19', 'TIDAK', '085312345678', 'MARTHA TANDI', 'TIDAK (ATS)', '-', 'SMP/MTS SEDERAJAT', 'SMPN 1 MATAKALI', 'LULUS TIDAK MELANJUTKAN', 'SEKOLAH JAUH DARI RUMAH', NULL, 'YA', 'INGIN KEMBALI BERSEKOLAH', 'NON FORMAL', 'PKBM SIPATUO MATAKALI (PAKET C)', 'L', '41', NULL, NULL, 'admin', '2026-10-25 13:10:00', '2026-10-25 13:10:00'),
('7', '2026-10-26 15:30:00', 'DATA BARU', 'POLEWALI MANDAR', 'WONOMULYO', 'CAMPURJO', 'ADMINISTRATOR', '085145888881', '-', '7602056003080007', 'NURHALIZA', 'PEREMPUAN', '2008-03-20', '18', 'TIDAK', '082345678901', 'SUTIARTI', 'TIDAK (ATS)', '-', 'SMP/MTS SEDERAJAT', 'SMPN 2 WONOMULYO', 'LULUS TIDAK MELANJUTKAN', 'MENIKAH/MENGURUS RUMAH TANGGA', NULL, 'TIDAK', 'TIDAK INGIN', '-', '-', '-', NULL, 'TATA BOGA & KULINER', NULL, 'admin', '2026-10-26 15:30:00', '2026-10-26 15:30:00'),
('8', '2026-10-27 09:00:00', 'DATA BARU', 'POLEWALI MANDAR', 'MAPILLI', 'KURMA', 'ADMINISTRATOR', '085145888881', '0105678912', '7602081406100008', 'ANDI ILHAM SAPUTRA', 'LAKI-LAKI', '2010-06-14', '16', 'YA', '081244556677', 'ANDI ST. AMINAH', 'TIDAK (ATS)', '-', 'SD/MI SEDERAJAT', 'SDN 008 MAPILLI', 'LULUS TIDAK MELANJUTKAN', 'MASALAH KESEHATAN / PENYANDANG DISABILITAS', NULL, 'YA', 'INGIN KEMBALI BERSEKOLAH', 'FORMAL', 'SLB NEGERI POLEWALI', 'L', '39', NULL, NULL, 'admin', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
('9', '2026-10-27 14:15:00', 'DATA BARU', 'POLEWALI MANDAR', 'BINUANG', 'AMASSANGAN', 'ADMINISTRATOR', '085145888881', '0098765432', '7602091901090009', 'FATIMAH AZ-ZAHRA', 'PEREMPUAN', '2009-01-19', '17', 'TIDAK', '081233445566', 'HALIMAH', 'TIDAK (ATS)', '-', 'SMP/MTS SEDERAJAT', 'SMPN 1 BINUANG', 'LULUS TIDAK MELANJUTKAN', 'TIDAK ADA BIAYA', NULL, 'TIDAK', 'INGIN KEMBALI BERSEKOLAH', 'FORMAL', 'SMAN 1 BINUANG', 'M', '38', NULL, NULL, 'admin', '2026-10-27 14:15:00', '2026-10-27 14:15:00'),
('10', '2026-10-28 08:30:00', 'REKONFIRMASI (DATA DASHBOARD ATS)', 'POLEWALI MANDAR', 'ALLU', 'PETOOSANG', 'ADMINISTRATOR', '085145888881', '-', '7602102111110010', 'MUHAMMAD RUSDI', 'LAKI-LAKI', '2011-11-21', '15', 'TIDAK', '085299887711', 'HASMIDAR', 'TIDAK (ATS)', '-', 'SD/MI SEDERAJAT', 'SDN 004 ALLU', 'PUTUS SEKOLAH (DO)', 'BEKERJA', NULL, 'TIDAK', 'INGIN KEMBALI BERSEKOLAH', 'NON FORMAL', 'PKBM TUNAS HARAPAN ALLU', 'M', '38', NULL, NULL, 'admin', '2026-10-28 08:30:00', '2026-10-28 08:30:00');

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
('1', '2026-10-28-055821', 'App\\Database\\Migrations\\CreateAtsTables', 'default', 'App', '1793167121', '1'),
('2', '2026-11-01-081500', 'App\\Database\\Migrations\\AddKecamatanAksesToUsers', 'default', 'App', '1793520634', '2'),
('3', '2026-11-01-083500', 'App\\Database\\Migrations\\AddDesaAksesAndActivityLogs', 'default', 'App', '1793521883', '3');

SET FOREIGN_KEY_CHECKS=1;
