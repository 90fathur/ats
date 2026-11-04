<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Verifikasi PANTAU ATS - <?= esc($data['nik']) ?> - <?= esc($data['nama_lengkap']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 11px; background: #f8fafc; color: #1e293b; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .print-card { box-shadow: none !important; border: 1px solid #94a3b8 !important; }
        }
    </style>
</head>
<body class="p-4 md:p-6">
    <div class="max-w-2xl mx-auto mb-3 flex items-center justify-between no-print">
        <a href="<?= base_url('ats') ?>" class="px-3 py-1.5 bg-slate-600 hover:bg-slate-700 text-white rounded text-xs font-semibold transition">
            &larr; Kembali ke Panel
        </a>
        <button onclick="window.print()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-bold shadow transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Kartu Verifikasi
        </button>
    </div>

    <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-md print-card border border-slate-200">
        <!-- Header Kop -->
        <div class="flex items-center gap-3 pb-3 border-b-2 border-slate-900 mb-4">
            <div class="w-12 h-12 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xl flex-shrink-0">
                🎓
            </div>
            <div class="flex-1 text-center">
                <h1 class="text-xs font-bold uppercase text-slate-700 tracking-wide">Pemerintah Kabupaten Polewali Mandar</h1>
                <h2 class="text-sm font-extrabold uppercase text-slate-900 tracking-wide">Dinas Pendidikan</h2>
                <p class="text-[9px] text-slate-500 mt-0.5">PANTAU ATS - Sistem Pemantauan dan Pemutakhiran Data Anak Tidak Sekolah</p>
            </div>
        </div>

        <div class="text-center mb-4">
            <h3 class="text-xs font-extrabold uppercase text-slate-800 tracking-wider inline-block border-b border-slate-800 pb-0.5">
                KARTU VERIFIKASI PANTAU ATS
            </h3>
            <p class="text-[9px] text-slate-500 mt-0.5">ID Reg: ATS-<?= date('Ymd', strtotime($data['waktu_input'])) ?>-<?= esc($data['id']) ?></p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <!-- Foto -->
            <div class="flex flex-col items-center">
                <div class="w-32 h-40 bg-slate-100 rounded-lg border border-slate-300 flex items-center justify-center overflow-hidden">
                    <?php if (!empty($data['foto_anak']) && str_starts_with($data['foto_anak'], 'data:image')): ?>
                        <img src="<?= $data['foto_anak'] ?>" alt="Foto Anak" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="text-center p-2 text-slate-400">
                            <svg class="w-8 h-8 mx-auto mb-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="text-[9px]">Belum Ada Foto</span>
                        </div>
                    <?php endif; ?>
                </div>
                <span class="mt-2 px-2 py-0.5 rounded text-[9px] font-bold uppercase <?= str_contains($data['status_sekolah'], 'TIDAK') ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                    <?= esc($data['status_sekolah']) ?>
                </span>
            </div>

            <!-- Biodata -->
            <div class="sm:col-span-2 text-[10px]">
                <table class="w-full">
                    <tr class="border-b border-slate-100">
                        <td class="py-1 text-slate-500 font-semibold w-28">NIK</td>
                        <td class="py-1 font-bold text-slate-800 font-mono">: <?= esc($data['nik']) ?></td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1 text-slate-500 font-semibold">NISN</td>
                        <td class="py-1 font-bold text-slate-800">: <?= esc($data['nisn'] ?: '-') ?></td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1 text-slate-500 font-semibold">Nama Lengkap</td>
                        <td class="py-1 font-bold text-slate-900 text-xs">: <?= esc($data['nama_lengkap']) ?></td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1 text-slate-500 font-semibold">Jenis Kelamin</td>
                        <td class="py-1 font-medium text-slate-800">: <?= esc($data['jenis_kelamin']) ?></td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1 text-slate-500 font-semibold">Tanggal Lahir / Usia</td>
                        <td class="py-1 font-medium text-slate-800">: <?= date('d-m-Y', strtotime($data['tanggal_lahir'])) ?> (<?= esc($data['umur']) ?> Tahun)</td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1 text-slate-500 font-semibold">Nama Ibu Kandung</td>
                        <td class="py-1 font-medium text-slate-800">: <?= esc($data['nama_ibu']) ?></td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1 text-slate-500 font-semibold">Disabilitas</td>
                        <td class="py-1 font-medium text-slate-800">: <?= esc($data['disabilitas']) ?></td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1 text-slate-500 font-semibold">No. HP Orang Tua</td>
                        <td class="py-1 font-medium text-slate-800">: <?= esc($data['no_hp_ortu'] ?: '-') ?></td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1 text-slate-500 font-semibold">Wilayah</td>
                        <td class="py-1 font-bold text-slate-800">: Desa <?= esc($data['desa']) ?>, Kec. <?= esc($data['kecamatan']) ?>, Kab. Polewali Mandar</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Detail Rekonfirmasi -->
        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 mb-4 text-[10px]">
            <span class="font-bold text-indigo-950 uppercase tracking-wider block mb-1.5 border-b pb-1">
                Data Intervensi & Rekonfirmasi
            </span>
            <div class="grid grid-cols-2 gap-x-3 gap-y-1.5">
                <div>
                    <span class="text-slate-500">Pendidikan Terakhir:</span>
                    <p class="font-bold text-slate-800"><?= esc($data['pendidikan_tertinggi'] ?: '-') ?> (Asal: <?= esc($data['asal_sekolah'] ?: '-') ?>)</p>
                </div>
                <div>
                    <span class="text-slate-500">Status Tidak Sekolah:</span>
                    <p class="font-bold text-slate-800"><?= esc($data['status_tdk_sekolah'] ?: '-') ?></p>
                </div>
                <div>
                    <span class="text-slate-500">Alasan Utama:</span>
                    <p class="font-bold text-slate-800"><?= esc($data['alasan'] ?: '-') ?><?= $data['alasan_lainnya'] ? ' (' . esc($data['alasan_lainnya']) . ')' : '' ?></p>
                </div>
                <div>
                    <span class="text-slate-500">Penerima Program PIP / KIP:</span>
                    <p class="font-bold text-slate-800"><?= esc($data['menerima_kip'] ?: '-') ?></p>
                </div>
                <div>
                    <span class="text-slate-500">Minat Kembali Sekolah:</span>
                    <p class="font-bold text-indigo-700"><?= esc($data['keinginan'] ?: '-') ?></p>
                </div>
                <div>
                    <span class="text-slate-500">Bentuk & Satuan Pendidikan:</span>
                    <p class="font-bold text-slate-800"><?= esc($data['bentuk_pendidikan'] ?: '-') ?> - <?= esc($data['satuan_pendidikan'] ?: '-') ?></p>
                </div>
                <?php if (!empty($data['ukuran_baju']) && $data['ukuran_baju'] !== '-'): ?>
                <div>
                    <span class="text-slate-500">Bantuan Seragam / Sepatu:</span>
                    <p class="font-bold text-slate-800">Baju: <?= esc($data['ukuran_baju']) ?> | Sepatu: <?= esc($data['ukuran_sepatu'] ?: '-') ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($data['keterampilan']) && $data['keterampilan'] !== '-'): ?>
                <div>
                    <span class="text-slate-500">Keterampilan Yang Dibutuhkan:</span>
                    <p class="font-bold text-slate-800"><?= esc($data['keterampilan']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tanda Tangan -->
        <div class="grid grid-cols-2 gap-6 text-[10px] text-center pt-2">
            <div>
                <p class="text-slate-500">Petugas Pendata Lapangan,</p>
                <div class="h-12 flex items-center justify-center">
                    <span class="italic text-slate-300 font-serif text-sm">[ Tanda Tangan ]</span>
                </div>
                <p class="font-bold text-slate-800 underline"><?= esc($data['nama_petugas']) ?></p>
                <p class="text-[9px] text-slate-400">HP: <?= esc($data['no_hp_petugas']) ?></p>
            </div>
            <div>
                <p class="text-slate-500">Kec. <?= esc($data['kecamatan']) ?>, <?= date('d-m-Y', strtotime($data['waktu_input'])) ?></p>
                <p class="text-slate-500">Mengetahui / Mengesahkan,</p>
                <div class="h-10 flex items-center justify-center">
                    <span class="px-2 py-0.5 rounded border border-indigo-200 text-[9px] text-indigo-700 font-bold bg-indigo-50">VERIFIED ATS</span>
                </div>
                <p class="font-bold text-slate-800 underline">Koordinator Wilayah / Pokja ATS</p>
            </div>
        </div>

        <div class="mt-4 pt-2 border-t border-slate-200 flex items-center justify-between text-[9px] text-slate-400">
            <span>Input: <?= esc($data['waktu_input']) ?> | Jenis: <?= esc($data['jenis_input']) ?></span>
            <span>PANTAU ATS (Pemantauan dan Pemutakhiran Data Anak Tidak Sekolah)</span>
        </div>
    </div>
</body>
</html>
