<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MATRIKS REKONFIRMASI DATA ATS - DINAS PENDIDIKAN POLEWALI MANDAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 8.5px; background: #f8fafc; color: #0f172a; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #475569; padding: 3px 4px; line-height: 1.15; }
        th { background-color: #f1f5f9; text-align: center; vertical-align: middle; font-weight: 700; color: #0f172a; }
        .text-center { text-align: center; }
        @media print {
            body { background: #fff; padding: 0; color: #000; }
            th, td { border: 1px solid #000 !important; }
            th { background-color: #e2e8f0 !important; color: #000 !important; }
            .no-print { display: none !important; }
            @page { size: landscape; margin: 6mm; }
        }
    </style>
</head>
<body class="p-3 md:p-6">
    <!-- Action Bar -->
    <div class="max-w-[1600px] mx-auto mb-3 flex items-center justify-between no-print">
        <a href="<?= base_url('ats') ?>" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-800 text-white rounded text-xs font-semibold transition flex items-center gap-1.5">
            &larr; Kembali ke Panel ATS
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-bold shadow transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Matriks (Print / PDF)
            </button>
        </div>
    </div>

    <!-- Container Cetak Matriks -->
    <div class="max-w-[1600px] mx-auto bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        
        <!-- Header Dokumen Resmi -->
        <div class="mb-3 text-center">
            <h1 class="text-xs sm:text-sm font-extrabold uppercase tracking-wide text-slate-900">MATRIKS REKONFIRMASI DATA ANAK TIDAK SEKOLAH (ATS)</h1>
            <h2 class="text-[11px] sm:text-xs font-bold uppercase tracking-wide text-slate-800">DINAS PENDIDIKAN KABUPATEN POLEWALI MANDAR</h2>
            <h3 class="text-[10px] font-semibold text-slate-600">TAHUN 2026</h3>
        </div>

        <!-- Info Wilayah & Petugas -->
        <div class="mb-2 text-[9.5px] font-semibold text-slate-800 grid grid-cols-2 gap-y-0.5 max-w-xl">
            <div class="flex"><span class="w-36">Kabupaten</span><span class="mr-1">:</span><span>Polewali Mandar</span></div>
            <div class="flex"><span class="w-40">Nama Petugas Rekonfirmasi</span><span class="mr-1">:</span><span><?= esc($displayPetugas) ?></span></div>
            <div class="flex"><span class="w-36">Kecamatan</span><span class="mr-1">:</span><span><?= esc($displayKecamatan) ?></span></div>
            <div class="flex"><span class="w-40">No. HP Petugas</span><span class="mr-1">:</span><span><?= esc($displayPetugasHp ?: '-') ?></span></div>
            <div class="flex"><span class="w-36">Kelurahan / Desa</span><span class="mr-1">:</span><span><?= esc($displayDesa) ?></span></div>
            <div class="flex"><span class="w-40">Total Anak Terdata</span><span class="mr-1">:</span><span class="font-bold text-indigo-700"><?= count($data) ?> Anak</span></div>
        </div>

        <!-- Tabel Matriks 27 Kolom -->
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <!-- Baris 1: Header Utama -->
                    <tr>
                        <th rowspan="3" class="w-7">No</th>
                        <th rowspan="3" style="min-width: 65px;">NISN<br><span class="font-normal text-[7.5px]">(1)</span></th>
                        <th rowspan="3" style="min-width: 95px;">NIK<br><span class="font-normal text-[7.5px]">(2)</span></th>
                        <th rowspan="3" style="min-width: 110px;">Nama ATS<br><span class="font-normal text-[7.5px]">(3)</span></th>
                        <th rowspan="3" class="w-7">JK<br><span class="font-normal text-[7.5px]">(L/P)<br>(4)</span></th>
                        <th rowspan="3" style="min-width: 55px;">Tgl Lahir<br><span class="font-normal text-[7.5px]">(5)</span></th>
                        <th rowspan="3" class="w-7">Umur<br><span class="font-normal text-[7.5px]">(6)</span></th>
                        <th rowspan="3" style="min-width: 75px;">Pendidikan Tertinggi*<br><span class="font-normal text-[7.5px]">(7)</span></th>
                        <th colspan="2">Kegiatan Saat Ini<br><span class="font-normal text-[7.5px]">(8)</span></th>
                        <th colspan="5">Status Tidak Sekolah<br><span class="font-normal text-[7.5px]">(9)</span></th>
                        <th rowspan="3" style="min-width: 120px;">Alasan Tidak Sekolah<br><span class="font-normal text-[7.5px]">(Alasan DO atau LTM)<br>(10)</span></th>
                        <th rowspan="3" style="min-width: 100px;">Sekolah Asal<br><span class="font-normal text-[7.5px]">(11)</span></th>
                        <th rowspan="3" style="min-width: 55px;">Penerima KIP<br><span class="font-normal text-[7.5px]">(Ya/Tidak)<br>(12)</span></th>
                        <th rowspan="3" style="min-width: 95px;">Nama Orang Tua / Wali<br><span class="font-normal text-[7.5px]">(13)</span></th>
                        <th rowspan="3" style="min-width: 65px;">Keinginan Kembali Sekolah**<br><span class="font-normal text-[7.5px]">(Ya/Tidak)<br>(14)</span></th>
                        <th colspan="2">Jika Ya, Tuliskan Satuan Pendidikan Tujuan<br><span class="font-normal text-[7.5px]">(15)</span></th>
                        <th rowspan="3" class="w-12">Disabilitas / Cacat<br><span class="font-normal text-[7.5px]">(16)</span></th>
                        <th rowspan="3" style="min-width: 80px;">Nomor HP<br><span class="font-normal text-[7.5px]">(17)</span></th>
                        <th rowspan="3" class="w-10">Ukuran Baju<br><span class="font-normal text-[7.5px]">(18)</span></th>
                        <th rowspan="3" class="w-10">Ukuran Sepatu<br><span class="font-normal text-[7.5px]">(19)</span></th>
                        <th rowspan="3" style="min-width: 85px;">Jenis Keterampilan Dibutuhkan***<br><span class="font-normal text-[7.5px]">(20)</span></th>
                        <th rowspan="3" style="min-width: 75px;">Keterangan****<br><span class="font-normal text-[7.5px]">(21)</span></th>
                    </tr>

                    <!-- Baris 2: Sub-Header Level 2 -->
                    <tr>
                        <th rowspan="2" class="w-12">Bersekolah<br><span class="font-normal text-[7.5px]">(8a)</span></th>
                        <th rowspan="2" class="w-12">Tidak Bersekolah<br><span class="font-normal text-[7.5px]">(8b)</span></th>
                        <th colspan="3">Putus Sekolah (DO)<br><span class="font-normal text-[7.5px]">(9a)</span></th>
                        <th colspan="2">Lulus Tdk Lanjut (LTM)<br><span class="font-normal text-[7.5px]">(9b)</span></th>
                        <th rowspan="2" style="min-width: 70px;">Formal<br><span class="font-normal text-[7.5px]">(15a)</span></th>
                        <th rowspan="2" style="min-width: 70px;">Non Formal<br><span class="font-normal text-[7.5px]">(15b)</span></th>
                    </tr>

                    <!-- Baris 3: Sub-Header Level 3 -->
                    <tr>
                        <th style="min-width: 50px;">Jenjang<br><span class="font-normal text-[7px]">(9a1)</span></th>
                        <th class="w-8">Kelas<br><span class="font-normal text-[7px]">(9a2)</span></th>
                        <th class="w-8">Tahun<br><span class="font-normal text-[7px]">(9a3)</span></th>
                        <th style="min-width: 50px;">Jenjang<br><span class="font-normal text-[7px]">(9b1)</span></th>
                        <th class="w-8">Tahun<br><span class="font-normal text-[7px]">(9b2)</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="27" class="text-center p-4 text-slate-500 font-semibold">
                                Tidak ada data ATS yang ditemukan untuk kriteria filter ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($data as $r): ?>
                        <tr class="<?= $no % 2 === 0 ? 'bg-slate-50/60' : 'bg-white' ?>">
                            <td class="text-center font-bold text-slate-500"><?= $no++ ?></td>
                            <td class="font-mono text-center"><?= esc($r['nisn']) ?></td>
                            <td class="font-mono text-center font-semibold"><?= esc($r['nik']) ?></td>
                            <td class="font-bold text-slate-900 uppercase"><?= esc($r['nama_lengkap']) ?></td>
                            <td class="text-center font-bold <?= $r['jk'] === 'L' ? 'text-blue-700' : 'text-pink-700' ?>"><?= esc($r['jk']) ?></td>
                            <td class="text-center whitespace-nowrap"><?= esc($r['tanggal_lahir']) ?></td>
                            <td class="text-center font-semibold"><?= esc($r['umur']) ?></td>
                            <td class="text-center"><?= esc($r['pendidikan_tertinggi']) ?></td>
                            <td class="text-center font-bold text-emerald-700"><?= esc($r['bersekolah']) ?></td>
                            <td class="text-center font-bold text-red-700"><?= esc($r['tidak_bersekolah']) ?></td>
                            <td class="text-center"><?= esc($r['putus_jenjang']) ?></td>
                            <td class="text-center font-semibold"><?= esc($r['putus_kelas']) ?></td>
                            <td class="text-center"><?= esc($r['putus_tahun']) ?></td>
                            <td class="text-center"><?= esc($r['ltm_jenjang']) ?></td>
                            <td class="text-center"><?= esc($r['ltm_tahun']) ?></td>
                            <td><?= esc($r['alasan']) ?></td>
                            <td><?= esc($r['sekolah_asal']) ?></td>
                            <td class="text-center font-semibold"><?= esc($r['menerima_kip']) ?></td>
                            <td class="uppercase font-medium"><?= esc($r['nama_ortu']) ?></td>
                            <td class="text-center font-bold <?= $r['keinginan'] === 'Ya' ? 'text-indigo-800' : 'text-slate-500' ?>"><?= esc($r['keinginan']) ?></td>
                            <td><?= esc($r['tujuan_formal']) ?></td>
                            <td><?= esc($r['tujuan_non_formal']) ?></td>
                            <td class="text-center"><?= esc($r['disabilitas']) ?></td>
                            <td class="font-mono text-center"><?= esc($r['no_hp']) ?></td>
                            <td class="text-center"><?= esc($r['ukuran_baju']) ?></td>
                            <td class="text-center"><?= esc($r['ukuran_sepatu']) ?></td>
                            <td><?= esc($r['keterampilan']) ?></td>
                            <td class="text-[7.5px]"><?= esc($r['keterangan']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footnotes / Keterangan Resmi -->
        <div class="mt-3 p-2.5 bg-slate-50 border border-slate-300 rounded text-[8px] text-slate-700 space-y-0.5">
            <p class="font-bold text-[8.5px] text-slate-900 uppercase">KETERANGAN PENGISIAN MATRIKS:</p>
            <p><strong>*)</strong> Pendidikan Tertinggi adalah jenjang pendidikan yang paling tinggi yang ditamatkan (SD, SMP, SMA sederajat). Apabila ATS belum pernah bersekolah, tuliskan pada kolom (7) ini BPB (Belum Pernah Bersekolah).</p>
            <p>Jika pada kolom (7) ATS berstatus BPB, maka langsung menuju ke kolom (13).</p>
            <p><strong>**)</strong> Jika ATS tidak ingin bersekolah (kolom 14), maka langsung menuju ke kolom (20).</p>
            <p><strong>***)</strong> Kolom (20) diisi khusus untuk anak yang tidak ingin bersekolah kembali (perhatikan jawaban kolom 14). Tuliskan jenis keterampilan (life skills) yang dibutuhkan, misalnya: perbengkelan, barista, menjahit, tata boga, komputer, pertukangan, dll.</p>
            <p><strong>****)</strong> Kolom (21) Keterangan berfungsi untuk mencatat informasi rekonfirmasi lapangan lain yang relevan.</p>
        </div>

        <!-- Titimangsa & Tanda Tangan -->
        <div class="mt-4 pt-3 flex justify-between items-start text-[9.5px] text-slate-800" style="page-break-inside: avoid;">
            <div class="text-center w-60">
                <p>Mengetahui,</p>
                <p class="font-bold">Kepala Dinas Pendidikan / Koordinator Wilayah</p>
                <div class="h-16"></div>
                <p class="font-bold underline">................................................................</p>
                <p class="text-[8.5px]">NIP. ........................................................</p>
            </div>
            <div class="text-center w-64">
                <p>Polewali Mandar, <?= date('d F Y') ?></p>
                <p class="font-bold">Petugas Pendata / Rekonfirmasi,</p>
                <div class="h-16"></div>
                <p class="font-bold underline uppercase"><?= esc($displayPetugas) ?></p>
                <p class="text-[8.5px]">No. HP: <?= esc($displayPetugasHp ?: '-') ?></p>
            </div>
        </div>

    </div>
</body>
</html>
