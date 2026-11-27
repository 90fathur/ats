<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MATRIKS REKONFIRMASI DATA ATS - DINAS PENDIDIKAN POLEWALI MANDAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            background: #f8fafc;
            color: #0f172a;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #475569;
            padding: 4px 5px;
            line-height: 1.25;
            font-size: 11px;
        }

        th {
            background-color: #f1f5f9;
            text-align: center;
            vertical-align: middle;
            font-weight: 700;
            color: #0f172a;
            font-size: 10px;
        }

        th span {
            font-size: 9px;
        }

        .text-center {
            text-align: center;
        }

        /* 5 Kolom Lebar Sama: Nama ATS, Alasan, Sekolah Asal, Keinginan Kembali, Keterangan */
        .col-lebar-standar {
            min-width: 140px !important;
            width: 140px !important;
        }

        @page {
            size: 330mm 215mm; /* Standar Ukuran Kertas F4 / Folio (330mm x 215mm) Landscape */
            margin: 4mm 5mm 5mm 5mm;
        }

        @media print {
            html, body {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                color: #000 !important;
                font-size: 8px !important;
                line-height: 1.15 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            th,
            td {
                border: 0.5pt solid #000 !important;
                padding: 2px 2.5px !important;
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
                font-size: 8px !important;
                line-height: 1.15 !important;
            }

            .col-lebar-standar {
                width: 9.5% !important;
                min-width: 0 !important;
            }

            th {
                background-color: #f1f5f9 !important;
                color: #000 !important;
                font-size: 7.2px !important;
                font-weight: 700 !important;
                text-align: center !important;
            }

            th span {
                font-size: 6.2px !important;
            }

            td {
                font-size: 8px !important;
            }

            .no-print {
                display: none !important;
            }

            .max-w-\[1600px\] {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }

            .overflow-x-auto {
                overflow: visible !important;
                display: block !important;
                width: 100% !important;
            }

            table {
                width: 100% !important;
                max-width: 100% !important;
                border-collapse: collapse !important;
                table-layout: auto !important;
            }

            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            thead {
                display: table-header-group !important;
            }

            tfoot {
                display: table-footer-group !important;
            }
        }
    </style>
</head>

<body class="p-3 md:p-6">
    <!-- Action Bar (Hanya tampil di monitor) -->
    <div class="max-w-[1600px] mx-auto mb-3 bg-white p-3 rounded-xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3 no-print">
        <div class="flex items-center gap-2.5">
            <a href="<?= base_url('ats') ?>" class="px-3.5 py-1.5 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs">
                <i class="fas fa-arrow-left text-[10px]"></i>
                <span>Kembali ke Panel ATS</span>
            </a>
            <div class="hidden sm:flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                <i class="fas fa-file-invoice text-indigo-600"></i>
                <span>Format Kertas: <b class="text-slate-800">F4 / Folio Landscape (330 x 215 mm)</b></span>
            </div>
            <!-- Pengatur Ukuran Font Layar -->
            <div class="flex items-center gap-1 text-xs bg-slate-100 px-2 py-1 rounded-lg border border-slate-200">
                <span class="text-slate-500 font-semibold text-[11px] mr-1">Ukuran Font:</span>
                <button type="button" onclick="adjustZoom(-1)" class="w-5 h-5 rounded bg-white hover:bg-slate-200 font-bold border border-slate-300 flex items-center justify-center text-xs" title="Kecilkan Font">-</button>
                <span id="zoomLevel" class="font-bold font-mono text-[11px] px-1.5 text-slate-800">100%</span>
                <button type="button" onclick="adjustZoom(1)" class="w-5 h-5 rounded bg-white hover:bg-slate-200 font-bold border border-slate-300 flex items-center justify-center text-xs" title="Besarkan Font">+</button>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Unduh Excel Resmi -->
            <a href="<?= base_url('ats/export/matriks') . '?' . http_build_query($_GET) ?>" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1.5" title="Unduh Matriks 27 Kolom Format Excel (.xlsx)">
                <i class="fas fa-file-excel text-xs"></i>
                <span>Unduh Excel (.xlsx)</span>
            </a>

            <!-- Tombol Cetak / PDF -->
            <button onclick="window.print()" class="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1.5" title="Cetak atau Simpan PDF format F4 Landscape">
                <i class="fas fa-print text-xs"></i>
                <span>Cetak / Simpan PDF (Kertas F4)</span>
            </button>
        </div>
    </div>

    <!-- Petunjuk Cetak (no-print) -->
    <div class="max-w-[1600px] mx-auto mb-3 p-2.5 bg-blue-50 border border-blue-200 rounded-lg text-[11px] text-blue-900 flex items-center justify-between gap-2 no-print">
        <div class="flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-600 text-sm"></i>
            <span>
                <b>Panduan Cetak Tulisan Lebih Besar di PDF:</b> Pada jendela cetak Chrome, pastikan <b>Ukuran Kertas: F4 / Folio</b> (atau Legal) &bull; <b>Orientasi: Lanskap (Landscape)</b> &bull; <b>Skala (Scale): 100%</b> (atau Sesuaikan lebar halaman) &bull; <b>Margin: Minimum (0.1")</b> agar hasil cetak jelas, tajam, dan tidak kekecilan.
            </span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-blue-500 hover:text-blue-800 text-xs px-1 font-bold">&times;</button>
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
                        <th rowspan="3" class="col-lebar-standar">Nama ATS<br><span class="font-normal text-[7.5px]">(3)</span></th>
                        <th rowspan="3" class="w-7">JK<br><span class="font-normal text-[7.5px]">(L/P)<br>(4)</span></th>
                        <th rowspan="3" style="min-width: 55px;">Tgl Lahir<br><span class="font-normal text-[7.5px]">(5)</span></th>
                        <th rowspan="3" class="w-7">Umur<br><span class="font-normal text-[7.5px]">(6)</span></th>
                        <th rowspan="3" style="min-width: 75px;">Pendidikan Tertinggi*<br><span class="font-normal text-[7.5px]">(7)</span></th>
                        <th colspan="2">Kegiatan Saat Ini<br><span class="font-normal text-[7.5px]">(8)</span></th>
                        <th colspan="5">Status Tidak Sekolah<br><span class="font-normal text-[7.5px]">(9)</span></th>
                        <th rowspan="3" class="col-lebar-standar">Alasan Tidak Sekolah<br><span class="font-normal text-[7.5px]">(Alasan DO atau LTM)<br>(10)</span></th>
                        <th rowspan="3" class="col-lebar-standar">Sekolah Asal<br><span class="font-normal text-[7.5px]">(11)</span></th>
                        <th rowspan="3" style="min-width: 55px;">Penerima KIP<br><span class="font-normal text-[7.5px]">(Ya/Tidak)<br>(12)</span></th>
                        <th rowspan="3" style="min-width: 95px;">Nama Orang Tua / Wali<br><span class="font-normal text-[7.5px]">(13)</span></th>
                        <th rowspan="3" class="col-lebar-standar">Keinginan Kembali Sekolah**<br><span class="font-normal text-[7.5px]">(Ya/Tidak)<br>(14)</span></th>
                        <th colspan="2">Jika Ya, Tuliskan Satuan Pendidikan Tujuan<br><span class="font-normal text-[7.5px]">(15)</span></th>
                        <th rowspan="3" class="w-12">Disabilitas / Cacat<br><span class="font-normal text-[7.5px]">(16)</span></th>
                        <th rowspan="3" style="min-width: 80px;">Nomor HP<br><span class="font-normal text-[7.5px]">(17)</span></th>
                        <th rowspan="3" class="w-10">Ukuran Baju<br><span class="font-normal text-[7.5px]">(18)</span></th>
                        <th rowspan="3" class="w-10">Ukuran Sepatu<br><span class="font-normal text-[7.5px]">(19)</span></th>
                        <th rowspan="3" style="min-width: 85px;">Jenis Keterampilan Dibutuhkan***<br><span class="font-normal text-[7.5px]">(20)</span></th>
                        <th rowspan="3" class="col-lebar-standar">Keterangan****<br><span class="font-normal text-[7.5px]">(21)</span></th>
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
                        <th style="min-width: 65px;">Jenjang<br><span class="font-normal text-[7px]">(9a1)</span></th>
                        <th class="w-8">Kelas<br><span class="font-normal text-[7px]">(9a2)</span></th>
                        <th class="w-8">Tahun<br><span class="font-normal text-[7px]">(9a3)</span></th>
                        <th style="min-width: 65px;">Jenjang<br><span class="font-normal text-[7px]">(9b1)</span></th>
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
                        <?php $no = 1;
                        foreach ($data as $r): ?>
                            <tr class="<?= $no % 2 === 0 ? 'bg-slate-50/60' : 'bg-white' ?>">
                                <td class="text-center font-bold text-slate-500"><?= $no++ ?></td>
                                <td class="font-mono text-center"><?= esc($r['nisn']) ?></td>
                                <td class="font-mono text-center font-semibold"><?= esc($r['nik']) ?></td>
                                <td class="font-bold text-slate-900 uppercase col-lebar-standar"><?= esc($r['nama_lengkap']) ?></td>
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
                                <td class="col-lebar-standar"><?= esc($r['alasan']) ?></td>
                                <td class="col-lebar-standar"><?= esc($r['sekolah_asal']) ?></td>
                                <td class="text-center font-semibold"><?= esc($r['menerima_kip']) ?></td>
                                <td class="uppercase font-medium"><?= esc($r['nama_ortu']) ?></td>
                                <td class="text-center font-bold col-lebar-standar <?= (strtoupper($r['keinginan']) === 'YA') ? 'text-emerald-700' : 'text-red-700' ?>">
                                    <?= esc($r['keinginan']) ?>
                                </td>
                                <td><?= esc($r['tujuan_formal']) ?></td>
                                <td><?= esc($r['tujuan_non_formal']) ?></td>
                                <td class="text-center"><?= esc($r['disabilitas']) ?></td>
                                <td class="font-mono text-center"><?= esc($r['no_hp']) ?></td>
                                <td class="text-center"><?= esc($r['ukuran_baju']) ?></td>
                                <td class="text-center"><?= esc($r['ukuran_sepatu']) ?></td>
                                <td><?= esc($r['keterampilan']) ?></td>
                                <td class="col-lebar-standar text-[8.5px]"><?= esc($r['keterangan']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Titimangsa & Tanda Tangan -->
        <div class="mt-4 pt-3 flex justify-between items-start text-[9.5px] text-slate-800" style="page-break-inside: avoid;">
            <div class="text-center w-60">
                <p>Mengetahui,</p>
                <p class="font-bold">Tim Pendamping Rekonfirmasi</p>
                <div class="h-16"></div>
                <p class="font-bold underline">................................................................</p>
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

    <script>
        let currentZoom = 100;
        function adjustZoom(delta) {
            currentZoom = Math.min(160, Math.max(70, currentZoom + delta * 10));
            const zoomLabel = document.getElementById('zoomLevel');
            if (zoomLabel) zoomLabel.innerText = currentZoom + '%';
            
            const baseFontSize = (currentZoom / 100) * 11;
            const thFontSize   = (currentZoom / 100) * 10;
            const subFontSize  = (currentZoom / 100) * 9;
            
            const tbl = document.querySelector('table');
            if (tbl) {
                tbl.style.fontSize = baseFontSize + 'px';
                tbl.querySelectorAll('td').forEach(td => td.style.fontSize = baseFontSize + 'px');
                tbl.querySelectorAll('th').forEach(th => th.style.fontSize = thFontSize + 'px');
                tbl.querySelectorAll('th span').forEach(sp => sp.style.fontSize = subFontSize + 'px');
            }
        }
    </script>
</body>

</html>