<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Akun Petugas Rekonfirmasi ATS 2026 - Kabupaten Polewali Mandar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 9.5px; background: #f8fafc; color: #0f172a; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #475569; padding: 4px 6px; line-height: 1.2; }
        th { background-color: #f1f5f9; text-align: center; vertical-align: middle; font-weight: 700; color: #0f172a; font-size: 9.5px; }
        tr { page-break-inside: avoid; break-inside: avoid; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        
        @media print {
            body { background: #fff; padding: 0; color: #000; }
            th, td { border: 1px solid #000 !important; }
            th { background-color: #e2e8f0 !important; color: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            @page { size: landscape; margin: 8mm; }
        }
    </style>
</head>
<body class="p-3 md:p-6">

    <!-- Action Bar (Hanya tampil di layar monitor) -->
    <div class="max-w-[1600px] mx-auto mb-3 flex items-center justify-between no-print">
        <a href="<?= base_url('ats') ?>" class="px-3.5 py-1.5 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs">
            <i class="fas fa-arrow-left text-[10px]"></i>
            <span>Kembali ke Panel ATS</span>
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow transition flex items-center gap-1.5">
                <i class="fas fa-print text-xs"></i>
                <span>Cetak / Simpan PDF (A4 Landscape)</span>
            </button>
        </div>
    </div>

    <!-- Kontainer Dokumen Cetak -->
    <div class="max-w-[1600px] mx-auto bg-white p-5 rounded-xl shadow-xs border border-slate-200">
        
        <!-- Kop Dokumen Resmi -->
        <div class="text-center pb-3 border-b-2 border-slate-900 mb-3">
            <h1 class="text-xs font-bold uppercase tracking-wider text-slate-700">PEMERINTAH KABUPATEN POLEWALI MANDAR</h1>
            <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">DINAS PENDIDIKAN</h2>
            <h3 class="text-[11px] font-bold uppercase text-indigo-900 mt-0.5">
                DAFTAR AKUN DAN HAK AKSES SISTEM PANTAU ATS TAHUN 2026
            </h3>
            <p class="text-[9.5px] text-slate-600 mt-0.5">
                Lampiran Penugasan Tim Petugas Rekonfirmasi Lapangan & Administrator Sistem
            </p>
        </div>

        <!-- Info Ringkasan Sistem & Login -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3 text-[9.5px]">
            <div class="p-2 bg-slate-50 border border-slate-200 rounded">
                <span class="text-slate-500 font-semibold block text-[8.5px] uppercase">Alamat Aplikasi (URL):</span>
                <span class="font-mono font-bold text-indigo-700 text-[10px] break-all"><?= rtrim(base_url(), '/') . '/ats' ?></span>
            </div>
            <div class="p-2 bg-slate-50 border border-slate-200 rounded">
                <span class="text-slate-500 font-semibold block text-[8.5px] uppercase">Password Default:</span>
                <span class="font-mono font-bold text-slate-900 text-[10px] bg-amber-100 px-1 py-0.2 rounded">polmanbaik#</span>
            </div>
            <div class="p-2 bg-slate-50 border border-slate-200 rounded">
                <span class="text-slate-500 font-semibold block text-[8.5px] uppercase">Total Akun Terdaftar:</span>
                <span class="font-bold text-slate-900 text-[10px]"><?= count($users) ?> Akun Pengguna</span>
            </div>
            <div class="p-2 bg-slate-50 border border-slate-200 rounded">
                <span class="text-slate-500 font-semibold block text-[8.5px] uppercase">Waktu Cetak Dokumen:</span>
                <span class="font-bold text-slate-800 text-[10px]"><?= date('d F Y, H:i') ?> WITA</span>
            </div>
        </div>

        <!-- Tabel Rekap Akun -->
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th class="w-8 py-2">NO</th>
                        <th class="py-2 text-left px-2">NAMA LENGKAP & GELAR PETUGAS</th>
                        <th class="w-36 py-2">USERNAME LOGIN</th>
                        <th class="w-28 py-2">PASSWORD DEFAULT</th>
                        <th class="w-24 py-2">ROLE / LEVEL</th>
                        <th class="w-32 py-2">NO. HP / WHATSAPP</th>
                        <th class="py-2 text-left px-2">WILAYAH TUGAS ENTRI DATA</th>
                        <th class="w-28 py-2">TANDA TANGAN / PARAF</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($users as $u): ?>
                        <?php 
                            $isAdminUser = strtoupper($u['role'] ?? '') === 'ADMIN';
                            
                            // Format link WhatsApp
                            $hpVal = $u['hp'] ?? '';
                            $cleanDigits = preg_replace('/[^0-9]/', '', (string)$hpVal);
                            $waFormatted = '-';
                            if (!empty($cleanDigits)) {
                                $waDigits = $cleanDigits;
                                if (str_starts_with($waDigits, '08')) {
                                    $waDigits = '62' . substr($waDigits, 1);
                                } elseif (str_starts_with($waDigits, '8')) {
                                    $waDigits = '62' . $waDigits;
                                }
                                $waFormatted = $hpVal;
                            }

                            // Format Wilayah
                            $wilayahText = '';
                            if ($isAdminUser || (empty($u['kecamatan_akses']) && empty($u['desa_akses']))) {
                                $wilayahText = 'Seluruh 16 Kecamatan & Desa se-Kabupaten Polewali Mandar';
                            } else {
                                $kec = $u['kecamatan_akses'] ?: '-';
                                $desa = $u['desa_akses'] ?: '-';
                                $wilayahText = "Desa/Kel. {$desa} (Kec. {$kec})";
                            }
                        ?>
                        <tr class="<?= ($no % 2 === 0) ? 'bg-slate-50/50' : 'bg-white' ?>">
                            <td class="text-center font-bold text-slate-500"><?= $no++ ?></td>
                            <td class="font-bold text-slate-900 px-2">
                                <?= esc($u['nama']) ?>
                            </td>
                            <td class="text-center font-mono font-semibold text-indigo-900 bg-slate-50/80">
                                <?= esc($u['username']) ?>
                            </td>
                            <td class="text-center font-mono font-bold text-slate-800 bg-amber-50/40">
                                polmanbaik#
                            </td>
                            <td class="text-center">
                                <?php if ($isAdminUser): ?>
                                    <span class="font-bold text-indigo-700">Level 1: Admin</span>
                                <?php else: ?>
                                    <span class="font-semibold text-emerald-800">Level 2: Tim Entri</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center font-mono text-[9px]">
                                <?php if (!empty($cleanDigits)): ?>
                                    <span class="font-semibold"><?= esc($waFormatted) ?></span>
                                <?php else: ?>
                                    <span class="text-slate-400 italic">Belum ada</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-2 font-medium">
                                <?= esc($wilayahText) ?>
                            </td>
                            <td class="text-center text-slate-300 text-[8px] h-7 align-bottom pb-1">
                                <?= $no - 1 ?>. ....................
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Catatan & Tanda Tangan Pengesahan -->
        <div class="mt-4 pt-3 border-t border-slate-300 flex items-start justify-between text-[9.5px]">
            <div class="max-w-md text-slate-600">
                <p class="font-bold text-slate-800 mb-0.5"><i class="fas fa-circle-info text-indigo-600"></i> Petunjuk Penggunaan Akun:</p>
                <ol class="list-decimal list-inside space-y-0.5 text-[9px]">
                    <li>Login ke sistem melalui peramban (browser) Chrome di HP atau Komputer.</li>
                    <li>Petugas dapat login menggunakan <strong>Username</strong> atau <strong>Nomor HP</strong> masing-masing.</li>
                    <li>Password awal default adalah <strong>polmanbaik#</strong> dan dapat diperbarui melalui tombol <em>Profil Saya</em> setelah login.</li>
                    <li>Akun ini hanya dapat mengentri dan mengubah data anak di desa/kelurahan wilayah penugasan masing-masing.</li>
                </ol>
            </div>
            
            <div class="text-center w-64 pr-4">
                <p>Polewali, <?= date('d F Y') ?></p>
                <p class="font-semibold mt-0.5">Penanggung Jawab Kegiatan,</p>
                <div class="h-16"></div>
                <p class="font-extrabold text-slate-900 uppercase underline">TIM TEKNIS ATS POLMAN</p>
                <p class="text-[8.5px] text-slate-500">Dinas Pendidikan Kab. Polewali Mandar</p>
            </div>
        </div>

    </div>

</body>
</html>
