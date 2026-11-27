<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Kinerja Petugas Rekonfirmasi ATS 2026 - Kabupaten Polewali Mandar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 9.5px; background: #f8fafc; color: #0f172a; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #475569; padding: 4.5px 6px; line-height: 1.25; }
        th { background-color: #f1f5f9; text-align: center; vertical-align: middle; font-weight: 700; color: #0f172a; font-size: 9px; }
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

    <!-- Action Bar (Hanya tampil di monitor) -->
    <div class="max-w-[1600px] mx-auto mb-4 bg-white p-3 rounded-xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3 no-print">
        <div class="flex items-center gap-3">
            <a href="<?= base_url('ats') ?>" class="px-3.5 py-1.5 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs">
                <i class="fas fa-arrow-left text-[10px]"></i>
                <span>Kembali ke Panel ATS</span>
            </a>

            <!-- Info filter wilayah -->
            <div class="text-xs font-semibold text-slate-700 flex items-center gap-1.5">
                <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-bold">
                    Kecamatan: <?= esc($selectedKec) ?>
                </span>
                <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200 font-bold">
                    Desa: <?= esc($selectedDesa) ?>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Unduh Excel -->
            <a href="<?= base_url('ats/export/kinerja-excel?kecamatan=' . urlencode($selectedKec) . '&desa=' . urlencode($selectedDesa)) ?>" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                <i class="fas fa-file-excel text-xs"></i>
                <span>Unduh Excel (.xlsx)</span>
            </a>

            <!-- Tombol Cetak / PDF -->
            <button onclick="window.print()" class="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1.5">
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
            <h2 class="text-sm font-black uppercase tracking-wide text-slate-900">DINAS PENDIDIKAN</h2>
            <h3 class="text-[11.5px] font-extrabold uppercase text-indigo-900 mt-0.5">
                REKAPITULASI KINERJA PENGINPUTAN PETUGAS REKONFIRMASI ATS TAHUN 2026
            </h3>
            <p class="text-[9.5px] text-slate-600 mt-0.5 font-medium">
                Wilayah: Kecamatan <b><?= esc($selectedKec) ?></b> &bull; Desa/Kelurahan: <b><?= esc($selectedDesa) ?></b>
            </p>
        </div>

        <!-- Info Ringkasan Capaian -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3 text-[9.5px]">
            <div class="p-2 bg-slate-50 border border-slate-200 rounded">
                <span class="text-slate-500 font-semibold block text-[8px] uppercase">Total Petugas:</span>
                <span class="font-bold text-slate-900 text-xs"><?= esc($summary['total_petugas']) ?> Orang</span>
                <span class="text-[8.5px] text-emerald-700 block mt-0.5">(<?= esc($summary['petugas_aktif']) ?> Aktif Entri)</span>
            </div>
            <div class="p-2 bg-slate-50 border border-slate-200 rounded">
                <span class="text-slate-500 font-semibold block text-[8px] uppercase">Realisasi Rekonfirmasi:</span>
                <span class="font-bold text-blue-700 text-xs"><?= esc($summary['rekonfirmasi']) ?> Data</span>
                <span class="text-[8.5px] text-blue-600 block mt-0.5">Target Lapangan</span>
            </div>
            <div class="p-2 bg-slate-50 border border-slate-200 rounded">
                <span class="text-slate-500 font-semibold block text-[8px] uppercase">Input Data Baru:</span>
                <span class="font-bold text-amber-700 text-xs"><?= esc($summary['data_baru']) ?> Data</span>
                <span class="text-[8.5px] text-amber-600 block mt-0.5">Temuan Lapangan</span>
            </div>
            <div class="p-2 bg-slate-50 border border-slate-200 rounded">
                <span class="text-slate-500 font-semibold block text-[8px] uppercase">Rincian Status Anak:</span>
                <span class="font-bold text-emerald-700 text-xs"><?= esc($summary['total_ats']) ?> ATS &bull; <?= esc($summary['total_sekolah']) ?> Sekolah</span>
                <span class="text-[8.5px] text-slate-500 block mt-0.5">Hasil Rekonfirmasi</span>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th class="w-8">No</th>
                        <th class="text-left w-60">Nama Petugas</th>
                        <th class="w-28">No. Kontak (WA)</th>
                        <th class="w-36">Kecamatan</th>
                        <th class="w-40">Desa / Kelurahan</th>
                        <th class="w-32">Realisasi Rekonfirmasi</th>
                        <th class="w-28">Input Data Baru</th>
                        <th class="w-24">Total ATS</th>
                        <th class="w-24">Total Bersekolah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-6 text-slate-400 font-semibold">
                                Tidak ada data petugas yang sesuai dengan kriteria filter ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1; 
                        foreach ($data as $p): 
                            $bg = $no % 2 === 0 ? 'bg-slate-50/50' : 'bg-white';
                        ?>
                        <tr class="<?= $bg ?>">
                            <td class="text-center font-bold text-slate-500"><?= $no++ ?></td>
                            <td class="font-bold text-slate-900 uppercase whitespace-nowrap"><?= esc($p['nama']) ?></td>
                            <td class="font-mono text-center"><?= esc($p['hp']) ?></td>
                            <td class="font-semibold text-slate-700 text-center"><?= esc($p['kecamatan_akses']) ?></td>
                            <td class="font-semibold text-slate-800 text-center"><?= esc($p['desa_akses']) ?></td>
                            <td class="font-bold text-blue-700 text-center font-mono"><?= esc($p['rekonfirmasi_input']) ?></td>
                            <td class="font-semibold text-amber-700 text-center font-mono"><?= esc($p['data_baru_input']) ?></td>
                            <td class="text-center font-mono text-slate-600"><?= esc($p['total_ats']) ?></td>
                            <td class="text-center font-mono text-slate-600"><?= esc($p['total_sekolah']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-bold text-slate-900 border-t-2 border-slate-400">
                        <td colspan="5" class="py-2 text-right uppercase tracking-wider font-black">TOTAL KESELURUHAN:</td>
                        <td class="text-center font-black text-blue-700 font-mono"><?= esc($summary['rekonfirmasi']) ?></td>
                        <td class="text-center font-black text-amber-700 font-mono"><?= esc($summary['data_baru']) ?></td>
                        <td class="text-center font-mono text-slate-700"><?= esc($summary['total_ats']) ?></td>
                        <td class="text-center font-mono text-slate-700"><?= esc($summary['total_sekolah']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Titimangsa & Tanda Tangan Resmi -->
        <div class="mt-5 pt-3 flex justify-between items-start text-[9.5px] text-slate-800" style="page-break-inside: avoid;">
            <div class="text-center w-64">
                <p>Mengetahui,</p>
                <p class="font-bold">Tim Pendamping Rekonfirmasi</p>
                <div class="h-16"></div>
                <p class="font-bold underline">................................................................</p>
                <p class="text-[8.5px]">NIP. ........................................................</p>
            </div>
            <div class="text-center w-64">
                <p>Polewali Mandar, <?= date('d F Y') ?></p>
                <p class="font-bold">Koordinator Pendataan ATS,</p>
                <div class="h-16"></div>
                <p class="font-bold underline uppercase"><?= esc(session()->get('nama') ?? 'Administrator ATS') ?></p>
                <p class="text-[8.5px]">NIP / Kontak: <?= esc(session()->get('hp') ?? '-') ?></p>
            </div>
        </div>

    </div>
</body>
</html>
