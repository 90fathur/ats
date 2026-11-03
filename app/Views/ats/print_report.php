<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PANTAU ATS - Rekapitulasi Laporan Pemantauan dan Pemutakhiran Data ATS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 10px; background: #f8fafc; color: #1e293b; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            @page { size: landscape; margin: 8mm; }
        }
    </style>
</head>
<body class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto mb-3 flex items-center justify-between no-print">
        <a href="<?= base_url('ats') ?>" class="px-3 py-1.5 bg-slate-600 hover:bg-slate-700 text-white rounded text-xs font-semibold transition">
            &larr; Kembali ke Panel
        </a>
        <button onclick="window.print()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-bold shadow transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Dokumen
        </button>
    </div>

    <div class="max-w-7xl mx-auto bg-white p-5 rounded-xl shadow-md border border-slate-200">
        <!-- Kop Surat -->
        <div class="text-center pb-3 border-b-2 border-slate-900 mb-4">
            <h1 class="text-xs font-bold uppercase text-slate-700 tracking-wider">PEMERINTAH KABUPATEN POLEWALI MANDAR</h1>
            <h2 class="text-sm font-extrabold uppercase text-slate-900 tracking-wide">DINAS PENDIDIKAN DAN KEBUDAYAAN</h2>
            <h3 class="text-[11px] font-bold uppercase text-indigo-800 mt-0.5">PANTAU ATS - REKAPITULASI PEMANTAUAN DAN PEMUTAKHIRAN DATA ANAK TIDAK SEKOLAH</h3>
            <p class="text-[9px] text-slate-500 mt-0.5">Wilayah: Kabupaten Polewali Mandar | Filter Kecamatan: <?= esc($kecamatan) ?> | Tanggal Cetak: <?= date('d F Y') ?></p>
        </div>

        <!-- Summary Highlights -->
        <div class="grid grid-cols-4 gap-3 mb-4 text-[10px]">
            <div class="p-2.5 bg-indigo-50 border border-indigo-200 rounded-lg">
                <span class="text-slate-500 font-semibold block">Total Anak Terdata</span>
                <span class="text-base font-bold text-indigo-900"><?= $summary['totalData'] ?> Anak</span>
            </div>
            <div class="p-2.5 bg-red-50 border border-red-200 rounded-lg">
                <span class="text-slate-500 font-semibold block">Anak Tidak Sekolah (ATS)</span>
                <span class="text-base font-bold text-red-700"><?= $summary['totalAts'] ?> Anak</span>
            </div>
            <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-lg">
                <span class="text-slate-500 font-semibold block">Masih Bersekolah</span>
                <span class="text-base font-bold text-emerald-700"><?= $summary['totalMasihSekolah'] ?> Anak</span>
            </div>
            <div class="p-2.5 bg-slate-100 border border-slate-200 rounded-lg">
                <span class="text-slate-500 font-semibold block">Gender (L/P)</span>
                <span class="text-base font-bold text-slate-800"><?= $summary['maleCount'] ?> L / <?= $summary['femaleCount'] ?> P</span>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto text-[10px]">
            <table class="w-full border-collapse border border-slate-300">
                <thead>
                    <tr class="bg-indigo-900 text-white text-center">
                        <th class="border border-slate-400 p-1">No</th>
                        <th class="border border-slate-400 p-1">NIK</th>
                        <th class="border border-slate-400 p-1">Nama Lengkap</th>
                        <th class="border border-slate-400 p-1">L/P</th>
                        <th class="border border-slate-400 p-1">Usia</th>
                        <th class="border border-slate-400 p-1">Wilayah (Desa, Kec)</th>
                        <th class="border border-slate-400 p-1">Status</th>
                        <th class="border border-slate-400 p-1">Ijazah Terakhir</th>
                        <th class="border border-slate-400 p-1">Alasan Tidak Sekolah</th>
                        <th class="border border-slate-400 p-1">Minat Kembali</th>
                        <th class="border border-slate-400 p-1">Rencana Satuan Pendidikan</th>
                        <th class="border border-slate-400 p-1">Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr><td colspan="12" class="p-3 text-center text-slate-500">Tidak ada data untuk ditampilkan.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($data as $d): ?>
                        <tr class="<?= $no % 2 === 0 ? 'bg-slate-50' : 'bg-white' ?>">
                            <td class="border border-slate-300 p-1 text-center"><?= $no++ ?></td>
                            <td class="border border-slate-300 p-1 font-mono"><?= esc($d['nik']) ?></td>
                            <td class="border border-slate-300 p-1 font-bold"><?= esc($d['nama_lengkap']) ?></td>
                            <td class="border border-slate-300 p-1 text-center"><?= $d['jenis_kelamin'] === 'PEREMPUAN' ? 'P' : 'L' ?></td>
                            <td class="border border-slate-300 p-1 text-center"><?= esc($d['umur']) ?> Thn</td>
                            <td class="border border-slate-300 p-1"><?= esc($d['desa']) ?>, Kec. <?= esc($d['kecamatan']) ?></td>
                            <td class="border border-slate-300 p-1 text-center">
                                <span class="px-1 py-0.2 rounded text-[9px] font-bold <?= str_contains($d['status_sekolah'], 'TIDAK') ? 'text-red-700 bg-red-100' : 'text-emerald-700 bg-emerald-100' ?>">
                                    <?= esc($d['status_sekolah']) ?>
                                </span>
                            </td>
                            <td class="border border-slate-300 p-1"><?= esc($d['pendidikan_tertinggi'] ?: '-') ?></td>
                            <td class="border border-slate-300 p-1"><?= esc($d['alasan'] ?: '-') ?></td>
                            <td class="border border-slate-300 p-1 font-semibold text-indigo-800"><?= esc($d['keinginan'] ?: '-') ?></td>
                            <td class="border border-slate-300 p-1"><?= esc($d['satuan_pendidikan'] ?: '-') ?></td>
                            <td class="border border-slate-300 p-1 text-slate-600"><?= esc($d['nama_petugas']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-2 gap-6 text-[10px] text-center mt-6 pt-2">
            <div></div>
            <div>
                <p>Polewali, <?= date('d F Y') ?></p>
                <p class="font-bold">Penanggung Jawab Program PANTAU ATS</p>
                <div class="h-12"></div>
                <p class="font-bold underline">Koordinator Wilayah</p>
                <p class="text-[9px] text-slate-500">Pokja PANTAU ATS - Penanganan Anak Tidak Sekolah</p>
            </div>
        </div>
    </div>
</body>
</html>
