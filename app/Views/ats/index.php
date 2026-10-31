<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATS - Anak Tidak Sekolah | Admin Panel</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sidebar: '#ffffff',       /* Clean Light White */
                        sidebarHover: '#f1f5f9',  /* Light Slate-100 */
                        adminBrand: '#4f46e5',    /* Indigo-600 */
                    },
                    fontSize: {
                        '2xs': ['10px', '14px'],
                        'xs': ['11px', '16px'],
                        'sm': ['12px', '18px'],
                        'base': ['13px', '20px'],
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Chart.js & Datalabels -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
    <!-- SheetJS for instant client-side Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 11px;
            background-color: #f8fafc;
            color: #1e293b;
        }

        /* Compact Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .sidebar-item-active {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            box-shadow: 0 1px 3px rgba(79, 70, 229, 0.25);
        }
        .sidebar-item-inactive {
            color: #475569;
            font-weight: 500;
        }
        .sidebar-item-inactive:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .compact-input {
            font-size: 11px;
            padding: 5px 9px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            transition: all 0.15s ease-in-out;
        }
        .compact-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
        }

        .compact-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            margin-bottom: 3px;
            display: block;
        }
    </style>
</head>
<body class="min-h-screen flex antialiased bg-slate-50">

    <!-- ==================== SIDEBAR ==================== -->
    <aside id="adminSidebar" class="w-60 bg-white flex-shrink-0 flex flex-col z-30 transition-all duration-200 border-r border-slate-200 fixed lg:static inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 shadow-xs">
        <!-- Sidebar Brand -->
        <div class="h-14 px-4 flex items-center justify-between border-b border-slate-200/90 bg-white">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <h1 class="text-xs font-black text-slate-900 tracking-wide uppercase leading-tight">
                        ATS POLMAN
                    </h1>
                    <p class="text-[9px] text-indigo-600 font-bold leading-none">Kab. Polewali Mandar</p>
                </div>
            </div>
            <!-- Close button for mobile -->
            <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-slate-800 p-1">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <!-- Scope Badge -->
        <div class="mx-3 mt-3 px-2.5 py-2 rounded-lg bg-indigo-50/70 border border-indigo-100 text-[10px]">
            <div class="text-slate-400 flex items-center gap-1.5 font-medium">
                <i class="fas fa-map-location-dot text-indigo-600 text-[11px]"></i>
                <span class="uppercase tracking-wider text-[9px] text-indigo-700 font-bold">Skop Wilayah Kerja</span>
            </div>
            <div class="font-bold text-slate-800 text-[11px] mt-0.5">Kab. Polewali Mandar</div>
            <div class="text-[9px] text-emerald-700 font-semibold flex items-center gap-1 mt-0.5">
                <i class="fas fa-check-circle text-[8px]"></i> 16 Kecamatan &bull; Kel/Desa
            </div>
        </div>

        <!-- Sidebar Navigation Menu -->
        <div class="flex-1 overflow-y-auto px-3 py-3 space-y-4">
            <!-- Section: Menu Utama -->
            <div>
                <p class="px-2 text-[9px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Menu Utama</p>
                <div class="space-y-0.5">
                    <button type="button" onclick="navigateTab('dashboard')" id="navItemDashboard" class="sidebar-item-active w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-chart-line w-4 text-center"></i>
                            <span>Dashboard Analitik</span>
                        </div>
                    </button>
                    <button type="button" onclick="navigateTab('data')" id="navItemData" class="sidebar-item-inactive w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-table-list w-4 text-center"></i>
                            <span>Data Tersimpan</span>
                        </div>
                        <span id="sidebarCountBadge" class="bg-indigo-100 text-indigo-700 font-bold px-1.5 py-0.2 rounded text-[10px]">0</span>
                    </button>
                    <button type="button" onclick="navigateTab('form')" id="navItemForm" class="sidebar-item-inactive w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-user-plus w-4 text-center"></i>
                            <span>Entri Data Baru</span>
                        </div>
                        <span class="text-indigo-600 text-[10px] font-bold"><i class="fas fa-plus"></i></span>
                    </button>
                </div>
            </div>

            <!-- Section: Laporan & Cetak -->
            <div>
                <p class="px-2 text-[9px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Laporan & Output</p>
                <div class="space-y-0.5">
                    <button type="button" onclick="navigateTab('export')" id="navItemExport" class="sidebar-item-inactive w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-file-excel w-4 text-center text-emerald-600"></i>
                            <span>Pusat Export Excel</span>
                        </div>
                    </button>
                    <a href="<?= base_url('ats/print/report') ?>" target="_blank" class="sidebar-item-inactive w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-print w-4 text-center text-amber-600"></i>
                            <span>Cetak Rekap PDF</span>
                        </div>
                        <i class="fas fa-arrow-up-right-from-square text-[9px]"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Sidebar User Footer -->
        <div class="p-3 border-t border-slate-200/90 bg-slate-50/70">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 overflow-hidden">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 border border-indigo-200 flex items-center justify-center text-indigo-700 font-bold text-xs flex-shrink-0">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-slate-800 truncate leading-tight"><?= esc($currentUser['nama']) ?></p>
                        <p class="text-[9px] text-slate-500 font-medium"><?= esc($currentUser['role']) ?> &bull; <?= esc($currentUser['username']) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                    <button type="button" onclick="openChangePasswordModal()" class="text-slate-400 hover:text-indigo-600 p-1 rounded transition" title="Ganti Password">
                        <i class="fas fa-key text-xs"></i>
                    </button>
                    <a href="<?= base_url('logout') ?>" class="text-slate-400 hover:text-red-600 p-1 rounded transition" title="Keluar">
                        <i class="fas fa-sign-out-alt text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <!-- Sidebar Backdrop for Mobile -->
    <div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-20 hidden lg:hidden"></div>

    <!-- ==================== MAIN CONTENT WRAPPER ==================== -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

        <!-- Top Header Navigation -->
        <header class="h-14 bg-white border-b border-slate-200/90 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-10 shadow-xs">
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-600 hover:text-slate-900 p-1.5 rounded-md hover:bg-slate-100">
                    <i class="fas fa-bars text-xs"></i>
                </button>
                <!-- Breadcrumbs -->
                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="font-bold text-indigo-600">ATS</span>
                    <span class="text-slate-300">/</span>
                    <span class="font-medium text-slate-400">Polewali Mandar</span>
                    <span class="text-slate-300">/</span>
                    <span id="breadcrumbCurrent" class="font-bold text-slate-800">Dashboard Analitik</span>
                </div>
            </div>

            <!-- Top Header Right -->
            <div class="flex items-center gap-2">
                <!-- Ganti Password Button -->
                <button type="button" onclick="openChangePasswordModal()" class="px-2.5 py-1 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 rounded-md text-xs font-semibold shadow-xs flex items-center gap-1.5 transition" title="Ganti Password">
                    <i class="fas fa-key text-[10px] text-amber-500"></i>
                    <span class="hidden sm:inline">Ganti Password</span>
                </button>

                <!-- Quick Button New Data -->
                <button type="button" onclick="navigateTab('form')" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-semibold shadow-xs flex items-center gap-1.5 transition">
                    <i class="fas fa-plus text-[10px]"></i>
                    <span class="hidden sm:inline">Entri Baru</span>
                </button>

                <!-- Profile Badge -->
                <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                    <div class="text-right hidden md:block">
                        <span class="text-xs font-bold text-slate-800 block leading-tight"><?= esc($currentUser['nama']) ?></span>
                        <span class="text-[9px] text-slate-400 leading-none"><?= esc($currentUser['role']) ?> &bull; <?= esc($currentUser['username']) ?></span>
                    </div>
                    <a href="<?= base_url('logout') ?>" class="px-2 py-1 bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-600 rounded-md text-xs font-semibold transition flex items-center gap-1" title="Keluar">
                        <i class="fas fa-power-off text-[10px]"></i>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Body Area -->
        <main class="p-4 sm:p-5 flex-1 space-y-4">

            <!-- Toast Notification Box -->
            <div id="toastBox" class="fixed top-4 right-4 z-50 flex items-center gap-2.5 px-3 py-2 rounded-lg shadow-lg text-white text-xs font-semibold transition-all duration-200 opacity-0 pointer-events-none transform translate-y-[-8px]">
                <i id="toastIcon" class="fas fa-check-circle text-xs"></i>
                <span id="toastMessage">Pemberitahuan</span>
            </div>

            <!-- ============================================================== -->
            <!-- SECTION 1: DASHBOARD ANALITIK                                 -->
            <!-- ============================================================== -->
            <section id="panelDashboard" class="space-y-4">
                <!-- Page Top Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-tight flex items-center gap-2">
                            <i class="fas fa-chart-pie text-indigo-600"></i>
                            <span>Dashboard Analitik ATS - Kabupaten Polewali Mandar</span>
                        </h2>
                        <p class="text-[10px] text-slate-500 mt-0.5">Monitoring hasil pendataan dan rekonfirmasi ATS di 16 Kecamatan Kabupaten Polewali Mandar</p>
                    </div>

                    <!-- Filter Kecamatan -->
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Kecamatan:</span>
                        <select id="analyticsFilterKec" onchange="loadAnalyticsData()" class="compact-input font-semibold text-slate-700">
                            <option value="Semua">Semua Kecamatan</option>
                            <?php foreach ($kecamatanList as $kec): ?>
                                <option value="<?= esc($kec) ?>"><?= esc(strtoupper($kec)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" onclick="loadAnalyticsData()" class="compact-input bg-slate-50 hover:bg-slate-100 text-slate-600" title="Muat Ulang Analitik">
                            <i class="fas fa-sync-alt" id="analyticsRefreshIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- 8 Metric KPI Cards (Dense Grid) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5">
                    <!-- Total Terdata -->
                    <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs">
                        <span class="text-[9px] font-bold uppercase text-slate-500 tracking-wider block">Total Data</span>
                        <div class="flex items-baseline justify-between mt-1">
                            <span class="text-base font-extrabold text-slate-900" id="statTotalData">0</span>
                            <span class="text-[9px] text-indigo-600 font-semibold"><i class="fas fa-users"></i></span>
                        </div>
                    </div>
                    <!-- Total ATS -->
                    <div class="bg-white p-2.5 rounded-lg border border-red-200 bg-red-50/20 shadow-xs">
                        <span class="text-[9px] font-bold uppercase text-red-600 tracking-wider block">Total ATS</span>
                        <div class="flex items-baseline justify-between mt-1">
                            <span class="text-base font-extrabold text-red-700" id="statTotalAts">0</span>
                            <span class="text-[9px] text-red-500 font-semibold"><i class="fas fa-user-xmark"></i></span>
                        </div>
                    </div>
                    <!-- Masih Sekolah -->
                    <div class="bg-white p-2.5 rounded-lg border border-emerald-200 bg-emerald-50/20 shadow-xs">
                        <span class="text-[9px] font-bold uppercase text-emerald-600 tracking-wider block">Masih Sekolah</span>
                        <div class="flex items-baseline justify-between mt-1">
                            <span class="text-base font-extrabold text-emerald-700" id="statTotalSekolah">0</span>
                            <span class="text-[9px] text-emerald-500 font-semibold"><i class="fas fa-user-check"></i></span>
                        </div>
                    </div>
                    <!-- Gender L/P -->
                    <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs">
                        <span class="text-[9px] font-bold uppercase text-slate-500 tracking-wider block">Gender (L / P)</span>
                        <div class="flex items-baseline justify-between mt-1">
                            <span class="text-sm font-extrabold text-slate-800" id="statGender">0 / 0</span>
                            <span class="text-[9px] text-slate-400"><i class="fas fa-venus-mars"></i></span>
                        </div>
                    </div>
                    <!-- Rata-rata Usia -->
                    <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs">
                        <span class="text-[9px] font-bold uppercase text-slate-500 tracking-wider block">Rata-Rata Usia</span>
                        <div class="flex items-baseline justify-between mt-1">
                            <span class="text-base font-extrabold text-slate-800" id="statAvgAge">0</span>
                            <span class="text-[9px] text-slate-400">Thn</span>
                        </div>
                    </div>
                    <!-- Disabilitas -->
                    <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs">
                        <span class="text-[9px] font-bold uppercase text-slate-500 tracking-wider block">Disabilitas</span>
                        <div class="flex items-baseline justify-between mt-1">
                            <span class="text-base font-extrabold text-purple-700" id="statTotalDisabilitas">0</span>
                            <span class="text-[9px] text-purple-400"><i class="fas fa-wheelchair"></i></span>
                        </div>
                    </div>
                    <!-- Penerima KIP -->
                    <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs">
                        <span class="text-[9px] font-bold uppercase text-slate-500 tracking-wider block">Penerima KIP</span>
                        <div class="flex items-baseline justify-between mt-1">
                            <span class="text-base font-extrabold text-cyan-700" id="statTotalKip">0</span>
                            <span class="text-[9px] text-cyan-400"><i class="fas fa-id-badge"></i></span>
                        </div>
                    </div>
                    <!-- Top Ijazah -->
                    <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs">
                        <span class="text-[9px] font-bold uppercase text-slate-500 tracking-wider block">Ijazah Terbanyak</span>
                        <div class="mt-1">
                            <span class="text-[11px] font-bold text-amber-700 block truncate" id="statTopIjazah" title="-">-</span>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1: Kecamatan & Usia -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Chart 1: Kecamatan -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight flex items-center gap-1.5">
                                <i class="fas fa-map-location-dot text-indigo-600 text-[10px]"></i> Distribusi Anak Per Kecamatan
                            </span>
                            <span class="text-[10px] text-slate-400">Jumlah Terdata</span>
                        </div>
                        <div class="h-56 relative">
                            <canvas id="chartKec"></canvas>
                        </div>
                    </div>

                    <!-- Chart 2: Umur -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight flex items-center gap-1.5">
                                <i class="fas fa-chart-simple text-emerald-600 text-[10px]"></i> Distribusi Rentang Usia (6 - 25 Tahun)
                            </span>
                            <span class="text-[10px] text-slate-400">Frekuensi</span>
                        </div>
                        <div class="h-56 relative">
                            <canvas id="chartUmur"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2: Minat Kembali, Bentuk, Alasan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Chart 3: Minat Kembali -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight flex items-center gap-1.5">
                                <i class="fas fa-circle-check text-blue-600 text-[10px]"></i> Minat Kembali Bersekolah
                            </span>
                        </div>
                        <div class="h-48 relative">
                            <canvas id="chartKeinginan"></canvas>
                        </div>
                    </div>

                    <!-- Chart 4: Bentuk Pendidikan -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight flex items-center gap-1.5">
                                <i class="fas fa-school text-purple-600 text-[10px]"></i> Jalur Pendidikan Tujuan
                            </span>
                        </div>
                        <div class="h-48 relative">
                            <canvas id="chartBentuk"></canvas>
                        </div>
                    </div>

                    <!-- Chart 5: Alasan -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs sm:col-span-2 lg:col-span-1">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight flex items-center gap-1.5">
                                <i class="fas fa-triangle-exclamation text-amber-500 text-[10px]"></i> Alasan Utama Tidak Sekolah
                            </span>
                        </div>
                        <div class="h-48 relative">
                            <canvas id="chartAlasan"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 3: Keterampilan, Baju, Sepatu -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Chart 6: Keterampilan -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight flex items-center gap-1.5">
                                <i class="fas fa-wrench text-orange-500 text-[10px]"></i> Kebutuhan Keterampilan
                            </span>
                        </div>
                        <div class="h-44 relative">
                            <canvas id="chartKeterampilan"></canvas>
                        </div>
                    </div>

                    <!-- Chart 7: Baju -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight flex items-center gap-1.5">
                                <i class="fas fa-shirt text-indigo-500 text-[10px]"></i> Kebutuhan Ukuran Baju
                            </span>
                        </div>
                        <div class="h-44 relative">
                            <canvas id="chartBaju"></canvas>
                        </div>
                    </div>

                    <!-- Chart 8: Sepatu -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight flex items-center gap-1.5">
                                <i class="fas fa-shoe-prints text-cyan-600 text-[10px]"></i> Kebutuhan Ukuran Sepatu
                            </span>
                        </div>
                        <div class="h-44 relative">
                            <canvas id="chartSepatu"></canvas>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================== -->
            <!-- SECTION 2: DATA TERSIMPAN                                     -->
            <!-- ============================================================== -->
            <section id="panelData" class="hidden space-y-3">
                <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Search -->
                        <div class="relative w-48 sm:w-64">
                            <i class="fas fa-search absolute left-2.5 top-2 text-slate-400 text-[10px]"></i>
                            <input type="text" id="tableSearchInput" oninput="debounceFilterTable()" placeholder="Cari NIK, Nama, Desa, Kec..." class="compact-input w-full pl-7">
                        </div>

                        <!-- Filter Kecamatan -->
                        <select id="tableFilterKecamatan" onchange="loadTableData()" class="compact-input font-medium text-slate-700">
                            <option value="Semua">Semua Kecamatan</option>
                            <?php foreach ($kecamatanList as $kec): ?>
                                <option value="<?= esc($kec) ?>"><?= esc(strtoupper($kec)) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Refresh -->
                        <button type="button" onclick="loadTableData()" class="compact-input bg-slate-50 hover:bg-slate-100 text-slate-700" title="Muat Ulang">
                            <i class="fas fa-sync-alt" id="tableRefreshIcon"></i>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-slate-500 font-semibold">
                            Total: <strong class="text-slate-900" id="tableTotalDisplay">0</strong> baris
                        </span>
                        <button type="button" onclick="navigateTab('form')" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-semibold shadow-xs flex items-center gap-1 transition">
                            <i class="fas fa-plus text-[10px]"></i>
                            <span>Tambah Data</span>
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="py-2 px-3 text-center w-24">Aksi</th>
                                    <th class="py-2 px-3">Waktu Input</th>
                                    <th class="py-2 px-3">NIK Anak</th>
                                    <th class="py-2 px-3">Nama Lengkap</th>
                                    <th class="py-2 px-3 text-center">L/P & Umur</th>
                                    <th class="py-2 px-3">Wilayah (Desa & Kecamatan)</th>
                                    <th class="py-2 px-3 text-center">Status</th>
                                    <th class="py-2 px-3">Minat Intervensi</th>
                                </tr>
                            </thead>
                            <tbody id="tableDataBody" class="divide-y divide-slate-100 text-xs text-slate-700">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <div id="tableEmptyState" class="hidden p-8 text-center">
                        <i class="fas fa-folder-open text-slate-300 text-2xl mb-2 block"></i>
                        <span class="text-xs font-bold text-slate-600">Tidak ada data ditemukan</span>
                        <p class="text-[10px] text-slate-400 mt-0.5">Silakan tambahkan data baru melalui formulir entri.</p>
                    </div>
                </div>
            </section>

            <!-- ============================================================== -->
            <!-- SECTION 3: FORMULIR ENTRI DATA ATS                            -->
            <!-- ============================================================== -->
            <section id="panelForm" class="hidden space-y-3">
                <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-tight flex items-center gap-1.5" id="formHeaderTitle">
                            <i class="fas fa-edit text-indigo-600"></i>
                            <span>Formulir Pendataan ATS - Kabupaten Polewali Mandar</span>
                        </h2>
                        <p class="text-[10px] text-slate-400">Pilih Kecamatan (16 Kecamatan Polman) dan Kelurahan/Desa penugasan, lalu lengkapi biodata anak</p>
                    </div>

                    <div id="editBannerNotice" class="hidden items-center gap-1.5 px-2.5 py-1 bg-amber-50 border border-amber-300 text-amber-900 rounded-md text-[10px] font-bold">
                        <i class="fas fa-info-circle text-amber-600"></i>
                        <span>Mode Edit: <strong id="editNoticeNik" class="font-mono"></strong></span>
                        <button type="button" onclick="cancelEditMode()" class="text-red-600 hover:underline ml-1">Batal</button>
                    </div>
                </div>

                <form id="atsMainForm" onsubmit="handleFormSubmit(event)" class="space-y-3">
                    <input type="hidden" id="isEditMode" name="isEditMode" value="false">
                    <input type="hidden" id="editId" name="editId" value="">
                    <input type="hidden" id="editNik" name="editNik" value="">
                    <input type="hidden" id="fotoBase64" name="fotoBase64" value="">

                    <!-- Group 1: Jenis & Wilayah (Kecamatan -> Desa) -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                        <span class="text-[11px] font-bold text-slate-900 uppercase tracking-wider pb-2 mb-3 border-b border-slate-100 flex items-center gap-1.5">
                            <i class="fas fa-map-location-dot text-indigo-600"></i> 1. Jenis Pendataan & Wilayah Kecamatan
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            <div>
                                <label for="jenisInput" class="compact-label">Jenis Pendataan <span class="text-red-500">*</span></label>
                                <select id="jenisInput" name="jenisInput" required class="compact-input w-full font-semibold">
                                    <option value="DATA BARU">DATA BARU</option>
                                    <option value="REKONFIRMASI (DATA DASHBOARD ATS)">REKONFIRMASI (DATA DASHBOARD ATS)</option>
                                    <option value="DATA PENDAMPING DESA">DATA PENDAMPING DESA</option>
                                </select>
                            </div>
                            <div>
                                <label for="kecamatan" class="compact-label">Kecamatan <span class="text-red-500">*</span></label>
                                <select id="kecamatan" name="kecamatan" required onchange="handleKecamatanChange()" class="compact-input w-full font-semibold">
                                    <option value="">PILIH KECAMATAN...</option>
                                    <?php foreach ($kecamatanList as $kec): ?>
                                        <option value="<?= esc($kec) ?>"><?= esc(strtoupper($kec)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="desa" class="compact-label">Kelurahan / Desa <span class="text-red-500">*</span></label>
                                <select id="desa" name="desa" required class="compact-input w-full font-semibold">
                                    <option value="">PILIH DESA/KELURAHAN...</option>
                                </select>
                            </div>
                            <div>
                                <label for="hpPetugas" class="compact-label">No. HP Petugas</label>
                                <input type="text" id="hpPetugas" name="hpPetugas" value="<?= esc($currentUser['hp']) ?>" readonly class="compact-input w-full bg-slate-50 font-semibold text-slate-700">
                            </div>
                        </div>
                    </div>

                    <!-- Group 2: Identitas Anak -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                        <span class="text-[11px] font-bold text-slate-900 uppercase tracking-wider pb-2 mb-3 border-b border-slate-100 flex items-center gap-1.5">
                            <i class="fas fa-id-card text-blue-600"></i> 2. Identitas Anak & Orang Tua
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <!-- NIK -->
                            <div>
                                <div class="flex items-center justify-between mb-0.5">
                                    <label for="nik" class="compact-label mb-0">NIK (16 Digit) <span class="text-red-500">*</span></label>
                                    <span id="nikCounter" class="text-[9px] font-mono text-slate-400">0/16</span>
                                </div>
                                <input type="text" id="nik" name="nik" required maxlength="16" oninput="handleNikInput(this)" placeholder="16 digit NIK" class="compact-input w-full font-mono font-bold">
                            </div>
                            <!-- NISN -->
                            <div>
                                <label for="nisn" class="compact-label">NISN (Maks 10 Digit)</label>
                                <input type="text" id="nisn" name="nisn" maxlength="10" placeholder="Opsional" class="compact-input w-full">
                            </div>
                            <!-- Nama Lengkap -->
                            <div>
                                <label for="namaLengkap" class="compact-label">Nama Lengkap Anak <span class="text-red-500">*</span></label>
                                <input type="text" id="namaLengkap" name="namaLengkap" required style="text-transform:uppercase" placeholder="Sesuai Ijazah / KK" class="compact-input w-full font-bold">
                            </div>

                            <!-- Jenis Kelamin -->
                            <div>
                                <label for="jk" class="compact-label">Jenis Kelamin <span class="text-red-500">*</span></label>
                                <select id="jk" name="jk" required class="compact-input w-full">
                                    <option value="">PILIH...</option>
                                    <option value="LAKI-LAKI">LAKI-LAKI</option>
                                    <option value="PEREMPUAN">PEREMPUAN</option>
                                </select>
                            </div>
                            <!-- Tanggal Lahir -->
                            <div>
                                <label for="tglLahir" class="compact-label">Tanggal Lahir <span class="text-red-500">*</span></label>
                                <input type="date" id="tglLahir" name="tglLahir" required onchange="calculateAge()" class="compact-input w-full">
                            </div>
                            <!-- Umur -->
                            <div>
                                <label for="umur" class="compact-label">Umur (Otomatis)</label>
                                <input type="number" id="umur" name="umur" readonly required placeholder="Auto dihitung" class="compact-input w-full bg-slate-50 font-bold text-indigo-700">
                            </div>

                            <!-- Disabilitas -->
                            <div>
                                <label for="disabilitas" class="compact-label">Penyandang Disabilitas? <span class="text-red-500">*</span></label>
                                <select id="disabilitas" name="disabilitas" required class="compact-input w-full">
                                    <option value="TIDAK" selected>TIDAK</option>
                                    <option value="YA">YA</option>
                                </select>
                            </div>
                            <!-- No HP Ortu -->
                            <div>
                                <label for="hpOrtu" class="compact-label">No. HP Orang Tua / Wali <span class="text-red-500">*</span></label>
                                <input type="text" id="hpOrtu" name="hpOrtu" required placeholder="08..." class="compact-input w-full">
                            </div>
                            <!-- Nama Ibu Kandung -->
                            <div>
                                <label for="namaIbu" class="compact-label">Nama Ibu Kandung <span class="text-red-500">*</span></label>
                                <input type="text" id="namaIbu" name="namaIbu" required style="text-transform:uppercase" placeholder="Sesuai KK" class="compact-input w-full">
                            </div>
                        </div>
                    </div>

                    <!-- Group 3: Status Pendidikan Saat Ini -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                        <span class="text-[11px] font-bold text-slate-900 uppercase tracking-wider pb-2 mb-3 border-b border-slate-100 flex items-center gap-1.5">
                            <i class="fas fa-graduation-cap text-teal-600"></i> 3. Status Pendidikan Saat Ini
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="statusSekolah" class="compact-label">Apakah Anak Aktif Bersekolah? <span class="text-red-500">*</span></label>
                                <select id="statusSekolah" name="statusSekolah" required onchange="applySchoolLogic()" class="compact-input w-full font-bold">
                                    <option value="">PILIH STATUS...</option>
                                    <option value="YA, MASIH SEKOLAH">YA, MASIH SEKOLAH</option>
                                    <option value="TAMAT SMA/SMK SEDERAJAT">TAMAT SMA/SMK SEDERAJAT</option>
                                    <option value="TIDAK (ATS)">TIDAK (ANAK TIDAK SEKOLAH / ATS)</option>
                                </select>
                            </div>
                            <div id="containerSekolahAktif" class="hidden">
                                <label for="sekolahAktif" class="compact-label">Nama Sekolah Aktif Saat Ini <span class="text-red-500">*</span></label>
                                <input type="text" id="sekolahAktif" name="sekolahAktif" style="text-transform:uppercase" placeholder="Nama sekolah aktif..." class="compact-input w-full">
                            </div>
                        </div>
                    </div>

                    <!-- Group 4: Detail Anak Tidak Sekolah (ATS) Conditional -->
                    <div id="sectionAts" class="hidden bg-white p-3.5 rounded-xl border border-red-200 shadow-xs space-y-3">
                        <span class="text-[11px] font-bold text-red-700 uppercase tracking-wider pb-2 mb-1 border-b border-red-100 flex items-center gap-1.5">
                            <i class="fas fa-triangle-exclamation text-red-600"></i> 4. Detail Rekonfirmasi Anak Tidak Sekolah (ATS)
                        </span>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="pendidikanTertinggi" class="compact-label">Pendidikan Tertinggi <span class="text-red-500">*</span></label>
                                <select id="pendidikanTertinggi" name="pendidikanTertinggi" onchange="applySchoolLogic()" class="compact-input w-full">
                                    <option value="">PILIH IJAZAH...</option>
                                    <option value="BPB">BPB (BELUM PERNAH BERSEKOLAH)</option>
                                    <option value="TIDAK MEMILIKI IJASAH">TIDAK MEMILIKI IJASAH</option>
                                    <option value="SD/MI SEDERAJAT">SD/MI SEDERAJAT</option>
                                    <option value="SMP/MTS SEDERAJAT">SMP/MTS SEDERAJAT</option>
                                </select>
                            </div>
                            <div id="containerAsalSekolah">
                                <label for="asalSekolah" class="compact-label">Asal Sekolah <span class="text-red-500">*</span></label>
                                <input type="text" id="asalSekolah" name="asalSekolah" style="text-transform:uppercase" placeholder="Nama sekolah sebelumnya" class="compact-input w-full">
                            </div>
                            <div id="containerStatusTdkSekolah">
                                <label for="statusTdkSekolah" class="compact-label">Status Tidak Sekolah <span class="text-red-500">*</span></label>
                                <select id="statusTdkSekolah" name="statusTdkSekolah" class="compact-input w-full">
                                    <option value="">PILIH...</option>
                                    <option value="PUTUS SEKOLAH (DO)">PUTUS SEKOLAH (DO)</option>
                                    <option value="LULUS TIDAK MELANJUTKAN">LULUS TIDAK MELANJUTKAN</option>
                                </select>
                            </div>

                            <div id="containerAlasan" class="sm:col-span-2">
                                <label for="alasan" class="compact-label">Alasan Utama Tidak Sekolah <span class="text-red-500">*</span></label>
                                <select id="alasan" name="alasan" onchange="handleAlasanChange()" class="compact-input w-full">
                                    <option value="">PILIH ALASAN...</option>
                                    <option value="TIDAK ADA BIAYA">TIDAK ADA BIAYA</option>
                                    <option value="BEKERJA">BEKERJA / MEMBANTU ORANG TUA</option>
                                    <option value="MENIKAH/MENGURUS RUMAH TANGGA">MENIKAH / MENGURUS RUMAH TANGGA</option>
                                    <option value="SEKOLAH JAUH DARI RUMAH">SEKOLAH JAUH DARI RUMAH / GEOGRAFIS</option>
                                    <option value="MASALAH KESEHATAN / PENYANDANG DISABILITAS">MASALAH KESEHATAN / PENYANDANG DISABILITAS</option>
                                    <option value="TIDAK MAU BERSEKOLAH">TIDAK MAU BERSEKOLAH</option>
                                    <option value="CUKUP DENGAN TINGKAT PENDIDIKAN SAAT INI">CUKUP DENGAN TINGKAT PENDIDIKAN SAAT INI</option>
                                    <option value="MENGALAMI KEKERASAN/PERUNDUNGAN/TRAUMA DI SEKOLAH">MENGALAMI KEKERASAN/PERUNDUNGAN/TRAUMA DI SEKOLAH</option>
                                    <option value="PENGARUH LINGKUNGAN/TEMAN">PENGARUH LINGKUNGAN/TEMAN</option>
                                    <option value="BERANGGAPAN SEKOLAH TIDAK PENTING">BERANGGAPAN SEKOLAH TIDAK PENTING</option>
                                    <option value="TIDAK MEMILIKI SERAGAM SEKOLAH">TIDAK MEMILIKI SERAGAM SEKOLAH</option>
                                    <option value="TIDAK MEMILIKI AKTA KELAHIRAN">TIDAK MEMILIKI AKTA KELAHIRAN</option>
                                    <option value="DIKELUARKAN">DIKELUARKAN</option>
                                    <option value="MENGUNDURKAN DIRI">MENGUNDURKAN DIRI</option>
                                    <option value="PINDAH DOMISILI">PINDAH DOMISILI</option>
                                    <option value="LAINNYA...">LAINNYA...</option>
                                </select>
                            </div>
                            <div id="containerKip">
                                <label for="penerimaKip" class="compact-label">Penerima Program KIP? <span class="text-red-500">*</span></label>
                                <select id="penerimaKip" name="penerimaKip" class="compact-input w-full">
                                    <option value="">PILIH...</option>
                                    <option value="YA">YA</option>
                                    <option value="TIDAK">TIDAK</option>
                                </select>
                            </div>

                            <div id="containerAlasanLainnya" class="hidden sm:col-span-3">
                                <label for="alasanLainnya" class="compact-label">Detail Alasan Lainnya <span class="text-red-500">*</span></label>
                                <input type="text" id="alasanLainnya" name="alasanLainnya" placeholder="Tuliskan alasan spesifik..." class="compact-input w-full">
                            </div>
                        </div>

                        <!-- Minat Kembali -->
                        <div class="pt-2 border-t border-slate-100">
                            <label for="keinginan" class="compact-label">Minat Kembali Bersekolah <span class="text-red-500">*</span></label>
                            <select id="keinginan" name="keinginan" onchange="applySchoolLogic()" class="compact-input w-full sm:w-80 font-bold">
                                <option value="">PILIH MINAT...</option>
                                <option value="INGIN KEMBALI BERSEKOLAH">YA, INGIN KEMBALI BERSEKOLAH</option>
                                <option value="TIDAK INGIN">TIDAK INGIN</option>
                            </select>
                        </div>

                        <!-- Sub-section: Jika Ingin Kembali -->
                        <div id="sectionInginSekolah" class="hidden grid-cols-1 sm:grid-cols-4 gap-3 p-3 rounded-lg bg-slate-50 border border-slate-200">
                            <div>
                                <label for="bentukPendidikan" class="compact-label">Bentuk Pendidikan <span class="text-red-500">*</span></label>
                                <select id="bentukPendidikan" name="bentukPendidikan" onchange="handleBentukChange()" class="compact-input w-full font-semibold">
                                    <option value="">PILIH...</option>
                                    <option value="FORMAL">FORMAL (SD/SMP/SMA)</option>
                                    <option value="NON FORMAL">NON FORMAL (PAKET A/B/C)</option>
                                </select>
                            </div>
                            <div>
                                <label for="satuanPendidikan" class="compact-label">Nama Satuan Pendidikan <span class="text-red-500">*</span></label>
                                <input type="text" id="satuanPendidikan" name="satuanPendidikan" style="text-transform:uppercase" placeholder="Nama sekolah / PKBM" class="compact-input w-full">
                            </div>
                            <div>
                                <label for="baju" class="compact-label">Ukuran Baju Seragam</label>
                                <select id="baju" name="baju" class="compact-input w-full font-semibold">
                                    <option value="-">-</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                    <option value="XXL">XXL</option>
                                </select>
                            </div>
                            <div>
                                <label for="sepatu" class="compact-label">Ukuran Sepatu</label>
                                <input type="number" id="sepatu" name="sepatu" min="25" max="46" placeholder="Contoh: 38" class="compact-input w-full font-mono">
                            </div>
                        </div>

                        <!-- Sub-section: Jika Tidak Ingin -->
                        <div id="sectionTidakIngin" class="hidden p-3 rounded-lg bg-amber-50/50 border border-amber-200">
                            <label for="keterampilan" class="compact-label">Keterampilan / Pelatihan Kerja Yang Dibutuhkan <span class="text-red-500">*</span></label>
                            <input type="text" id="keterampilan" name="keterampilan" style="text-transform:uppercase" placeholder="Contoh: TEKNIK OTOMOTIF / TATA BOGA / PERTUKANGAN" class="compact-input w-full font-semibold">
                        </div>
                    </div>

                    <!-- Group 5: Foto -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                        <span class="text-[11px] font-bold text-slate-900 uppercase tracking-wider pb-2 mb-3 border-b border-slate-100 flex items-center gap-1.5">
                            <i class="fas fa-camera text-purple-600"></i> 5. Dokumentasi Foto Anak
                        </span>

                        <input type="file" id="fotoInput" accept="image/*" capture="environment" onchange="handlePhotoUpload(event)" class="hidden">

                        <div class="flex items-center gap-4">
                            <div id="photoPlaceholder" class="flex items-center gap-3">
                                <button type="button" onclick="document.getElementById('fotoInput').click()" class="compact-input bg-slate-50 hover:bg-slate-100 font-semibold text-slate-700 flex items-center gap-1.5 border-dashed border-slate-300">
                                    <i class="fas fa-camera text-purple-600"></i>
                                    <span>Ambil / Unggah Foto</span>
                                </button>
                                <span class="text-[10px] text-slate-400">Kamera HP / File gambar otomatis dikompres</span>
                            </div>

                            <div id="photoPreviewBox" class="hidden items-center gap-3">
                                <div class="relative w-16 h-16 rounded-lg overflow-hidden border border-slate-300 shadow-xs">
                                    <img id="imgPreview" src="" alt="Preview" class="w-full h-full object-cover">
                                    <button type="button" onclick="removePhoto()" class="absolute top-1 right-1 w-4 h-4 rounded-full bg-red-600 text-white flex items-center justify-center text-[9px]">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <button type="button" onclick="document.getElementById('fotoInput').click()" class="text-[10px] text-indigo-600 hover:underline font-semibold">
                                    Ganti Foto
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 pt-2">
                        <button type="submit" id="btnSubmitForm" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-xs flex items-center gap-1.5 transition">
                            <i class="fas fa-save" id="btnSubmitIcon"></i>
                            <span id="btnSubmitText">Simpan Data</span>
                        </button>
                        <button type="button" onclick="resetForm()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition">
                            Reset Form
                        </button>
                    </div>
                </form>
            </section>

            <!-- ============================================================== -->
            <!-- SECTION 4: PUSAT EXPORT & LAPORAN                             -->
            <!-- ============================================================== -->
            <section id="panelExport" class="hidden space-y-3">
                <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-xs">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-tight flex items-center gap-1.5">
                        <i class="fas fa-file-excel text-emerald-600"></i>
                        <span>Pusat Unduhan & Laporan Ekspor ATS - Kabupaten Polewali Mandar</span>
                    </h2>
                    <p class="text-[10px] text-slate-500 mt-0.5">Format file Microsoft Excel (.xlsx) per tingkatan target sasaran anak.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <!-- Export Semua -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-6 h-6 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">
                                    <i class="fas fa-database"></i>
                                </span>
                                <h3 class="text-xs font-bold text-slate-900">Semua Data Terverifikasi</h3>
                            </div>
                            <p class="text-[10px] text-slate-500">Seluruh basis data anak tanpa filter jenjang.</p>
                        </div>
                        <a href="<?= base_url('ats/export/excel?target=Semua') ?>" class="mt-3 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg text-center flex items-center justify-center gap-1.5 shadow-xs transition">
                            <i class="fas fa-download text-[10px]"></i>
                            <span>Unduh Excel Semua Data</span>
                        </a>
                    </div>

                    <!-- Target SD -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-6 h-6 rounded bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">
                                    <i class="fas fa-child"></i>
                                </span>
                                <h3 class="text-xs font-bold text-slate-900">Target Intervensi SD</h3>
                            </div>
                            <p class="text-[10px] text-slate-500">Usia 7-12 tahun atau BPB (belum pernah bersekolah).</p>
                        </div>
                        <a href="<?= base_url('ats/export/excel?target=SD') ?>" class="mt-3 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs rounded-lg text-center flex items-center justify-center gap-1.5 transition">
                            <i class="fas fa-download text-[10px]"></i>
                            <span>Unduh Target SD</span>
                        </a>
                    </div>

                    <!-- Target SMP -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-6 h-6 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                    <i class="fas fa-user-graduate"></i>
                                </span>
                                <h3 class="text-xs font-bold text-slate-900">Target Intervensi SMP</h3>
                            </div>
                            <p class="text-[10px] text-slate-500">Usia 13-15 tahun atau lulusan SD/MI.</p>
                        </div>
                        <a href="<?= base_url('ats/export/excel?target=SMP') ?>" class="mt-3 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs rounded-lg text-center flex items-center justify-center gap-1.5 transition">
                            <i class="fas fa-download text-[10px]"></i>
                            <span>Unduh Target SMP</span>
                        </a>
                    </div>

                    <!-- Target SMA -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-6 h-6 rounded bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold">
                                    <i class="fas fa-building-columns"></i>
                                </span>
                                <h3 class="text-xs font-bold text-slate-900">Target Intervensi SMA/SMK</h3>
                            </div>
                            <p class="text-[10px] text-slate-500">Usia 16-18 tahun atau lulusan SMP/MTs.</p>
                        </div>
                        <a href="<?= base_url('ats/export/excel?target=SMA') ?>" class="mt-3 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs rounded-lg text-center flex items-center justify-center gap-1.5 transition">
                            <i class="fas fa-download text-[10px]"></i>
                            <span>Unduh Target SMA</span>
                        </a>
                    </div>

                    <!-- Target Paket A/B/C -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-6 h-6 rounded bg-purple-50 text-purple-600 flex items-center justify-center text-xs font-bold">
                                    <i class="fas fa-book-open"></i>
                                </span>
                                <h3 class="text-xs font-bold text-slate-900">Target Non-Formal (PKBM)</h3>
                            </div>
                            <p class="text-[10px] text-slate-500">Jalur Kesetaraan Paket A, B, atau C.</p>
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-1">
                            <a href="<?= base_url('ats/export/excel?target=PAKET%20A') ?>" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[10px] rounded text-center">Paket A</a>
                            <a href="<?= base_url('ats/export/excel?target=PAKET%20B') ?>" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[10px] rounded text-center">Paket B</a>
                            <a href="<?= base_url('ats/export/excel?target=PAKET%20C') ?>" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[10px] rounded text-center">Paket C</a>
                        </div>
                    </div>

                    <!-- Rekap Cetak -->
                    <div class="bg-white p-3.5 rounded-xl border border-indigo-200 bg-indigo-50/20 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-6 h-6 rounded bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                                    <i class="fas fa-print"></i>
                                </span>
                                <h3 class="text-xs font-bold text-indigo-950">Rekapitulasi Dokumen Resmi</h3>
                            </div>
                            <p class="text-[10px] text-slate-500">Tampilan cetak landscape dokumen resmi.</p>
                        </div>
                        <a href="<?= base_url('ats/print/report') ?>" target="_blank" class="mt-3 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg text-center flex items-center justify-center gap-1.5 transition">
                            <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                            <span>Buka Dokumen PDF</span>
                        </a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- ==================== PHOTO MODAL ==================== -->
    <div id="photoModal" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-sm w-full overflow-hidden shadow-xl border border-slate-200">
            <div class="px-3.5 py-2.5 bg-slate-50 border-b border-slate-200 text-slate-800 flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-900" id="photoModalTitle">Foto Dokumentasi Anak</h4>
                    <p class="text-[9px] text-slate-500" id="photoModalSubtitle">ATS - Anak Tidak Sekolah</p>
                </div>
                <button type="button" onclick="closePhotoModal()" class="w-5 h-5 rounded hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center text-xs">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-3 flex items-center justify-center bg-slate-50 min-h-[220px]">
                <img id="photoModalImg" src="" alt="Foto Anak" class="max-h-64 rounded-lg object-contain border border-slate-200">
                <p id="photoModalNoImg" class="hidden text-xs text-slate-400 font-semibold">Foto belum dilampirkan.</p>
            </div>
            <div class="p-2.5 bg-slate-100 text-right border-t border-slate-200">
                <button type="button" onclick="closePhotoModal()" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 text-slate-700 font-semibold text-xs rounded">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== CHANGE PASSWORD MODAL ==================== -->
    <div id="changePasswordModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full overflow-hidden shadow-2xl border border-slate-200">
            <!-- Modal Header -->
            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 text-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs shadow-xs">
                        <i class="fas fa-key"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Ganti Password Akun</h4>
                        <p class="text-[9px] text-slate-500 font-medium">Akun: <strong class="text-indigo-600"><?= esc($currentUser['username']) ?></strong> (<?= esc($currentUser['nama']) ?>)</p>
                    </div>
                </div>
                <button type="button" onclick="closeChangePasswordModal()" class="w-6 h-6 rounded-lg hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center text-xs transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Form -->
            <form id="changePasswordForm" onsubmit="handleChangePasswordSubmit(event)" class="p-4 space-y-3">
                <?= csrf_field() ?>
                
                <!-- Error Alert Box -->
                <div id="cpAlert" class="hidden p-2.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-[10px] flex items-center gap-1.5">
                    <i class="fas fa-circle-exclamation text-xs text-red-500 flex-shrink-0"></i>
                    <span id="cpAlertText"></span>
                </div>

                <!-- Password Saat Ini -->
                <div>
                    <label for="cpOldPassword" class="compact-label">Password Saat Ini (Lama) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" id="cpOldPassword" name="oldPassword" required placeholder="Masukkan password saat ini" class="compact-input w-full pr-8">
                        <button type="button" onclick="togglePassVisibility('cpOldPassword', 'oldEye')" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600">
                            <i class="fas fa-eye text-[11px]" id="oldEye"></i>
                        </button>
                    </div>
                </div>

                <!-- Password Baru -->
                <div>
                    <label for="cpNewPassword" class="compact-label">Password Baru <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" id="cpNewPassword" name="newPassword" required minlength="4" placeholder="Min. 4 karakter" class="compact-input w-full pr-8">
                        <button type="button" onclick="togglePassVisibility('cpNewPassword', 'newEye')" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600">
                            <i class="fas fa-eye text-[11px]" id="newEye"></i>
                        </button>
                    </div>
                </div>

                <!-- Konfirmasi Password Baru -->
                <div>
                    <label for="cpConfirmPassword" class="compact-label">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" id="cpConfirmPassword" name="confirmPassword" required minlength="4" placeholder="Ketik ulang password baru" class="compact-input w-full pr-8">
                        <button type="button" onclick="togglePassVisibility('cpConfirmPassword', 'confEye')" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600">
                            <i class="fas fa-eye text-[11px]" id="confEye"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" onclick="closeChangePasswordModal()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitCP" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                        <span id="btnTextCP">Perbarui Password</span>
                        <i id="btnSpinnerCP" class="fas fa-spinner fa-spin hidden"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== SCRIPT ==================== -->
    <script>
        const BASE_URL = '<?= base_url() ?>';
        let currentUser = <?= json_encode($currentUser) ?>;
        let wilayahKecamatan = null;
        let globalData = [];
        let chartsObj = {};
        let debounceTimer = null;

        document.addEventListener('DOMContentLoaded', async () => {
            await loadWilayahData();
            await loadTableData();
            await loadAnalyticsData();
            applySchoolLogic();
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const isHidden = sidebar.classList.contains('-translate-x-full');

            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        function navigateTab(tab) {
            ['panelDashboard', 'panelData', 'panelForm', 'panelExport'].forEach(id => {
                document.getElementById(id).classList.add('hidden');
            });

            ['navItemDashboard', 'navItemData', 'navItemForm', 'navItemExport'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.className = 'sidebar-item-inactive w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all';
                }
            });

            const breadcrumb = document.getElementById('breadcrumbCurrent');

            if (tab === 'dashboard') {
                document.getElementById('panelDashboard').classList.remove('hidden');
                document.getElementById('navItemDashboard').className = 'sidebar-item-active w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all';
                breadcrumb.innerText = 'Dashboard Analitik';
                loadAnalyticsData();
            } else if (tab === 'data') {
                document.getElementById('panelData').classList.remove('hidden');
                document.getElementById('navItemData').className = 'sidebar-item-active w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all';
                breadcrumb.innerText = 'Data Tersimpan';
                loadTableData();
            } else if (tab === 'form') {
                document.getElementById('panelForm').classList.remove('hidden');
                document.getElementById('navItemForm').className = 'sidebar-item-active w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all';
                breadcrumb.innerText = document.getElementById('isEditMode').value === 'true' ? 'Edit Data ATS' : 'Entri Data Baru';
            } else if (tab === 'export') {
                document.getElementById('panelExport').classList.remove('hidden');
                document.getElementById('navItemExport').className = 'sidebar-item-active w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all';
                breadcrumb.innerText = 'Pusat Export Excel';
            }

            if (window.innerWidth < 1024) {
                document.getElementById('adminSidebar').classList.add('-translate-x-full');
                document.getElementById('sidebarBackdrop').classList.add('hidden');
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // 1. Load Wilayah Kecamatan JSON
        async function loadWilayahData() {
            try {
                const res = await fetch(`${BASE_URL}ats/getWilayah`);
                const json = await res.json();
                if (json.status === 'success') {
                    wilayahKecamatan = json.data;
                }
            } catch (err) {
                console.error("Gagal memuat wilayah kecamatan:", err);
            }
        }

        // 2. Cascade Dropdown: Kecamatan -> Desa/Kelurahan
        function handleKecamatanChange() {
            const kecSelect = document.getElementById('kecamatan');
            const desaSelect = document.getElementById('desa');

            desaSelect.innerHTML = '<option value="">PILIH DESA/KELURAHAN...</option>';

            const kec = kecSelect.value;
            if (!kec || !wilayahKecamatan || !wilayahKecamatan[kec]) return;

            const desaList = wilayahKecamatan[kec];
            desaList.forEach(d => {
                desaSelect.innerHTML += `<option value="${d}">${d}</option>`;
            });
        }

        // 3. NIK Sanitizer & Counter
        function handleNikInput(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
            const count = input.value.length;
            const counter = document.getElementById('nikCounter');
            counter.innerText = `${count}/16`;
            if (count === 16) {
                counter.className = "text-[9px] font-mono text-emerald-600 font-bold";
            } else {
                counter.className = "text-[9px] font-mono text-slate-400";
            }
        }

        // 4. Age Calculation
        function calculateAge() {
            const tgl = document.getElementById('tglLahir').value;
            const umurInput = document.getElementById('umur');
            if (!tgl) { umurInput.value = ''; return; }

            const birth = new Date(tgl);
            const today = new Date();
            let age = today.getFullYear() - birth.getFullYear();
            const m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            umurInput.value = age > 0 ? age : 0;
            if (age < 6 || age > 25) {
                showToast("Sasaran usia ATS adalah antara 6 hingga 25 tahun!", "error");
            }
        }

        // 5. School Status & Logic
        function applySchoolLogic() {
            const statusSekolah = document.getElementById('statusSekolah').value;
            const contAktif = document.getElementById('containerSekolahAktif');
            const secAts = document.getElementById('sectionAts');
            const sekolahAktif = document.getElementById('sekolahAktif');

            if (statusSekolah === 'YA, MASIH SEKOLAH' || statusSekolah === 'TAMAT SMA/SMK SEDERAJAT') {
                contAktif.classList.remove('hidden');
                sekolahAktif.required = true;
                secAts.classList.add('hidden');
            } else if (statusSekolah === 'TIDAK (ATS)') {
                contAktif.classList.add('hidden');
                sekolahAktif.required = false;
                secAts.classList.remove('hidden');
            } else {
                contAktif.classList.add('hidden');
                sekolahAktif.required = false;
                secAts.classList.add('hidden');
            }

            const pddk = document.getElementById('pendidikanTertinggi').value;
            const contAsal = document.getElementById('containerAsalSekolah');
            const contStatusTdk = document.getElementById('containerStatusTdkSekolah');
            const contAlasan = document.getElementById('containerAlasan');
            const contKip = document.getElementById('containerKip');
            const stTdk = document.getElementById('statusTdkSekolah');

            contAsal.classList.remove('hidden');
            contStatusTdk.classList.remove('hidden');
            contAlasan.classList.remove('hidden');
            contKip.classList.remove('hidden');

            const currentStatusVal = stTdk.value;
            stTdk.innerHTML = '<option value="">PILIH...</option>';

            if (pddk === 'BPB' || pddk === 'TIDAK MEMILIKI IJASAH') {
                contAsal.classList.add('hidden');
                contStatusTdk.classList.add('hidden');
                contKip.classList.add('hidden');
            } else if (pddk === 'SD/MI SEDERAJAT' || pddk === 'SMP/MTS SEDERAJAT') {
                stTdk.innerHTML += `<option value="PUTUS SEKOLAH (DO)">PUTUS SEKOLAH (DO)</option><option value="LULUS TIDAK MELANJUTKAN">LULUS TIDAK MELANJUTKAN</option>`;
                stTdk.value = currentStatusVal;
            }

            const keinginan = document.getElementById('keinginan').value;
            const secIngin = document.getElementById('sectionInginSekolah');
            const secTdkIngin = document.getElementById('sectionTidakIngin');

            if (keinginan === 'INGIN KEMBALI BERSEKOLAH') {
                secIngin.classList.remove('hidden');
                secIngin.classList.add('grid');
                secTdkIngin.classList.add('hidden');
                handleBentukChange();
            } else if (keinginan === 'TIDAK INGIN') {
                secIngin.classList.add('hidden');
                secIngin.classList.remove('grid');
                secTdkIngin.classList.remove('hidden');
            } else {
                secIngin.classList.add('hidden');
                secIngin.classList.remove('grid');
                secTdkIngin.classList.add('hidden');
            }
        }

        function handleBentukChange() {
            const bentuk = document.getElementById('bentukPendidikan').value;
            const baju = document.getElementById('baju');
            const sepatu = document.getElementById('sepatu');

            if (bentuk === 'FORMAL') {
                baju.disabled = false;
                sepatu.disabled = false;
            } else {
                baju.disabled = true;
                sepatu.disabled = true;
                baju.value = '-';
                sepatu.value = '';
            }
        }

        function handleAlasanChange() {
            const alasan = document.getElementById('alasan').value;
            const contLainnya = document.getElementById('containerAlasanLainnya');
            const inputLainnya = document.getElementById('alasanLainnya');

            if (alasan === 'LAINNYA...') {
                contLainnya.classList.remove('hidden');
                inputLainnya.required = true;
            } else {
                contLainnya.classList.add('hidden');
                inputLainnya.required = false;
                inputLainnya.value = '';
            }
        }

        // 6. Photo upload & client compression
        function handlePhotoUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    const MAX = 800;
                    let w = img.width, h = img.height;
                    if (w > h) { if (w > MAX) { h *= MAX / w; w = MAX; } }
                    else { if (h > MAX) { w *= MAX / h; h = MAX; } }

                    canvas.width = w; canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, w, h);

                    const compressed = canvas.toDataURL('image/jpeg', 0.7);
                    document.getElementById('fotoBase64').value = compressed;
                    document.getElementById('imgPreview').src = compressed;
                    document.getElementById('photoPlaceholder').classList.add('hidden');
                    document.getElementById('photoPreviewBox').classList.remove('hidden');
                    document.getElementById('photoPreviewBox').classList.add('flex');
                    showToast("Foto berhasil dimuat & dikompres!", "success");
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function removePhoto() {
            document.getElementById('fotoBase64').value = '';
            document.getElementById('imgPreview').src = '';
            document.getElementById('fotoInput').value = '';
            document.getElementById('photoPreviewBox').classList.add('hidden');
            document.getElementById('photoPreviewBox').classList.remove('flex');
            document.getElementById('photoPlaceholder').classList.remove('hidden');
        }

        // 7. Form Submission
        async function handleFormSubmit(e) {
            e.preventDefault();

            const umur = parseInt(document.getElementById('umur').value);
            if (isNaN(umur) || umur < 6 || umur > 25) {
                showToast("Gagal: Usia harus antara 6 hingga 25 tahun!", "error");
                document.getElementById('tglLahir').focus();
                return;
            }

            const nik = document.getElementById('nik').value.trim();
            if (nik.length !== 16) {
                showToast("Gagal: NIK wajib diisi tepat 16 digit angka!", "error");
                document.getElementById('nik').focus();
                return;
            }

            const btnSubmit = document.getElementById('btnSubmitForm');
            const btnText = document.getElementById('btnSubmitText');
            btnSubmit.disabled = true;
            btnText.innerText = "Menyimpan...";

            const form = document.getElementById('atsMainForm');
            const formData = new FormData(form);

            try {
                const response = await fetch(`${BASE_URL}ats/store`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showToast(result.message, "success");
                    resetForm();
                    await loadTableData();
                    await loadAnalyticsData();
                    navigateTab('data');
                } else {
                    showToast(result.message, "error");
                }
            } catch (err) {
                showToast("Terjadi kesalahan jaringan: " + err.message, "error");
            } finally {
                btnSubmit.disabled = false;
                btnText.innerText = document.getElementById('isEditMode').value === 'true' ? "Perbarui Data" : "Simpan Data";
            }
        }

        // 8. Reset & Cancel Edit Mode
        function resetForm() {
            document.getElementById('atsMainForm').reset();
            document.getElementById('isEditMode').value = 'false';
            document.getElementById('editId').value = '';
            document.getElementById('editNik').value = '';
            document.getElementById('editBannerNotice').classList.add('hidden');
            document.getElementById('editBannerNotice').classList.remove('flex');
            document.getElementById('btnSubmitText').innerText = "Simpan Data";
            document.getElementById('formHeaderTitle').innerHTML = '<i class="fas fa-edit text-indigo-600"></i><span>Formulir Pendataan ATS - Anak Tidak Sekolah</span>';
            document.getElementById('nikCounter').innerText = "0/16";
            removePhoto();

            document.getElementById('hpPetugas').value = currentUser.hp;

            applySchoolLogic();
        }

        function cancelEditMode() {
            resetForm();
            navigateTab('data');
            showToast("Mode edit dibatalkan.", "info");
        }

        // 9. Load Table Data
        async function loadTableData() {
            const refreshIcon = document.getElementById('tableRefreshIcon');
            refreshIcon.classList.add('fa-spin');

            const kec = document.getElementById('tableFilterKecamatan').value;
            const query = document.getElementById('tableSearchInput').value.trim();

            let url = `${BASE_URL}ats/getData?kecamatan=${encodeURIComponent(kec)}&q=${encodeURIComponent(query)}`;

            try {
                const response = await fetch(url);
                const result = await response.json();

                if (result.status === 'success') {
                    globalData = result.data;
                    renderTable(globalData);
                }
            } catch (err) {
                console.error("Gagal memuat tabel:", err);
            } finally {
                refreshIcon.classList.remove('fa-spin');
            }
        }

        function debounceFilterTable() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => { loadTableData(); }, 300);
        }

        function renderTable(data) {
            const tbody = document.getElementById('tableDataBody');
            const emptyState = document.getElementById('tableEmptyState');
            const totalDisplay = document.getElementById('tableTotalDisplay');
            const sidebarBadge = document.getElementById('sidebarCountBadge');

            totalDisplay.innerText = data.length;
            sidebarBadge.innerText = data.length;

            tbody.innerHTML = '';

            if (data.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            } else {
                emptyState.classList.add('hidden');
            }

            data.forEach((row) => {
                const isAts = String(row.status_sekolah).toUpperCase().includes('TIDAK');
                const badgeStatus = isAts
                    ? `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">ATS</span>`
                    : `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>`;

                const photoBtn = row.foto_anak
                    ? `<button type="button" onclick="viewPhoto('${row.nik}')" class="p-1 text-emerald-600 hover:text-emerald-800" title="Lihat Foto"><i class="fas fa-image"></i></button>`
                    : `<span class="p-1 text-slate-300 cursor-not-allowed"><i class="fas fa-image"></i></span>`;

                const printBtn = `<a href="${BASE_URL}ats/print/card/${row.nik}" target="_blank" class="p-1 text-purple-600 hover:text-purple-800" title="Cetak Kartu"><i class="fas fa-print"></i></a>`;
                const editBtn = `<button type="button" onclick="editRecord('${row.nik}')" class="p-1 text-blue-600 hover:text-blue-800" title="Edit"><i class="fas fa-pen-to-square"></i></button>`;
                const deleteBtn = `<button type="button" onclick="deleteRecord('${row.id}', '${row.nama_lengkap}')" class="p-1 text-red-500 hover:text-red-700" title="Hapus"><i class="fas fa-trash-can"></i></button>`;

                tbody.innerHTML += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-2 px-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1">
                                ${photoBtn}
                                ${printBtn}
                                ${editBtn}
                                ${deleteBtn}
                            </div>
                        </td>
                        <td class="py-2 px-3 text-slate-400 font-mono text-[10px] whitespace-nowrap">${row.waktu_input || '-'}</td>
                        <td class="py-2 px-3 font-mono font-bold text-slate-800">${row.nik}</td>
                        <td class="py-2 px-3 font-semibold text-slate-900">${row.nama_lengkap}</td>
                        <td class="py-2 px-3 text-center whitespace-nowrap">
                            <span class="font-bold text-slate-700">${row.jenis_kelamin === 'PEREMPUAN' ? 'P' : 'L'}</span> &bull; ${row.umur} Thn
                        </td>
                        <td class="py-2 px-3 text-[10px]">
                            <span class="font-semibold text-slate-800">${row.desa || '-'}</span>, Kec. ${row.kecamatan || '-'}
                        </td>
                        <td class="py-2 px-3 text-center whitespace-nowrap">${badgeStatus}</td>
                        <td class="py-2 px-3 text-[10px] font-medium text-slate-600 truncate max-w-[150px]" title="${row.keinginan || '-'}">${row.keinginan || '-'}</td>
                    </tr>
                `;
            });
        }

        // 10. Edit Record
        async function editRecord(nik) {
            try {
                const res = await fetch(`${BASE_URL}ats/getRecord/${nik}`);
                const json = await res.json();
                if (json.status !== 'success') {
                    showToast(json.message, "error");
                    return;
                }

                const d = json.data;

                document.getElementById('isEditMode').value = 'true';
                document.getElementById('editId').value = d.id;
                document.getElementById('editNik').value = d.nik;
                document.getElementById('editNoticeNik').innerText = d.nik;
                document.getElementById('editBannerNotice').classList.remove('hidden');
                document.getElementById('editBannerNotice').classList.add('flex');
                document.getElementById('btnSubmitText').innerText = "Perbarui Data";
                document.getElementById('formHeaderTitle').innerHTML = `<i class="fas fa-edit text-indigo-600"></i><span>Edit Data: ${d.nama_lengkap}</span>`;

                document.getElementById('jenisInput').value = d.jenis_input || 'DATA BARU';
                document.getElementById('kecamatan').value = d.kecamatan || '';
                handleKecamatanChange();

                document.getElementById('desa').value = d.desa || '';
                document.getElementById('hpPetugas').value = d.no_hp_petugas || currentUser.hp;

                document.getElementById('nisn').value = d.nisn === '-' ? '' : (d.nisn || '');
                document.getElementById('nik').value = d.nik || '';
                handleNikInput(document.getElementById('nik'));

                document.getElementById('namaLengkap').value = d.nama_lengkap || '';
                document.getElementById('jk').value = d.jenis_kelamin || '';
                document.getElementById('tglLahir').value = d.tanggal_lahir || '';
                document.getElementById('umur').value = d.umur || '';
                document.getElementById('disabilitas').value = d.disabilitas || 'TIDAK';
                document.getElementById('hpOrtu').value = d.no_hp_ortu || '';
                document.getElementById('namaIbu').value = d.nama_ibu || '';

                document.getElementById('statusSekolah').value = d.status_sekolah || '';
                document.getElementById('sekolahAktif').value = d.sekolah_aktif === '-' ? '' : (d.sekolah_aktif || '');

                document.getElementById('pendidikanTertinggi').value = d.pendidikan_tertinggi === '-' ? '' : (d.pendidikan_tertinggi || '');
                applySchoolLogic();

                document.getElementById('asalSekolah').value = d.asal_sekolah === '-' ? '' : (d.asal_sekolah || '');
                document.getElementById('statusTdkSekolah').value = d.status_tdk_sekolah === '-' ? '' : (d.status_tdk_sekolah || '');
                document.getElementById('alasan').value = d.alasan === '-' ? '' : (d.alasan || '');
                handleAlasanChange();
                document.getElementById('alasanLainnya').value = d.alasan_lainnya || '';
                document.getElementById('penerimaKip').value = d.menerima_kip === '-' ? '' : (d.menerima_kip || '');

                document.getElementById('keinginan').value = d.keinginan === '-' ? '' : (d.keinginan || '');
                applySchoolLogic();

                document.getElementById('bentukPendidikan').value = d.bentuk_pendidikan === '-' ? '' : (d.bentuk_pendidikan || '');
                document.getElementById('satuanPendidikan').value = d.satuan_pendidikan === '-' ? '' : (d.satuan_pendidikan || '');
                document.getElementById('baju').value = d.ukuran_baju || '-';
                document.getElementById('sepatu').value = d.ukuran_sepatu === '-' ? '' : (d.ukuran_sepatu || '');
                document.getElementById('keterampilan').value = d.keterampilan === '-' ? '' : (d.keterampilan || '');

                if (d.foto_anak && d.foto_anak.startsWith('data:image')) {
                    document.getElementById('fotoBase64').value = d.foto_anak;
                    document.getElementById('imgPreview').src = d.foto_anak;
                    document.getElementById('photoPlaceholder').classList.add('hidden');
                    document.getElementById('photoPreviewBox').classList.remove('hidden');
                    document.getElementById('photoPreviewBox').classList.add('flex');
                } else {
                    removePhoto();
                }

                navigateTab('form');
                showToast("Data siap disunting.", "info");
            } catch (err) {
                showToast("Gagal memuat data: " + err.message, "error");
            }
        }

        // 11. Delete Record
        async function deleteRecord(id, name) {
            if (!confirm(`Hapus data atas nama "${name}"?`)) return;

            try {
                const res = await fetch(`${BASE_URL}ats/delete/${id}`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await res.json();
                if (result.status === 'success') {
                    showToast(result.message, "success");
                    await loadTableData();
                    await loadAnalyticsData();
                } else {
                    showToast(result.message, "error");
                }
            } catch (err) {
                showToast("Gagal menghapus: " + err.message, "error");
            }
        }

        // 12. View Photo Modal
        function viewPhoto(nik) {
            const row = globalData.find(x => x.nik === nik);
            if (!row) return;

            document.getElementById('photoModalTitle').innerText = row.nama_lengkap;
            document.getElementById('photoModalSubtitle').innerText = `NIK: ${row.nik} - ${row.desa}, Kec. ${row.kecamatan}`;

            const imgEl = document.getElementById('photoModalImg');
            const noImg = document.getElementById('photoModalNoImg');

            if (row.foto_anak && row.foto_anak.startsWith('data:image')) {
                imgEl.src = row.foto_anak;
                imgEl.classList.remove('hidden');
                noImg.classList.add('hidden');
            } else {
                imgEl.src = '';
                imgEl.classList.add('hidden');
                noImg.classList.remove('hidden');
            }

            document.getElementById('photoModal').classList.remove('hidden');
            document.getElementById('photoModal').classList.add('flex');
        }

        function closePhotoModal() {
            document.getElementById('photoModal').classList.add('hidden');
            document.getElementById('photoModal').classList.remove('flex');
        }

        // Change Password Modal Controls
        function openChangePasswordModal() {
            document.getElementById('cpAlert').classList.add('hidden');
            document.getElementById('changePasswordForm').reset();
            document.getElementById('changePasswordModal').classList.remove('hidden');
            document.getElementById('changePasswordModal').classList.add('flex');
            setTimeout(() => document.getElementById('cpOldPassword').focus(), 100);
        }

        function closeChangePasswordModal() {
            document.getElementById('changePasswordModal').classList.add('hidden');
            document.getElementById('changePasswordModal').classList.remove('flex');
        }

        function togglePassVisibility(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            if (!field || !icon) return;
            const isPass = field.getAttribute('type') === 'password';
            field.setAttribute('type', isPass ? 'text' : 'password');
            icon.classList.toggle('fa-eye', !isPass);
            icon.classList.toggle('fa-eye-slash', isPass);
        }

        async function handleChangePasswordSubmit(e) {
            e.preventDefault();
            const alertBox = document.getElementById('cpAlert');
            const alertText = document.getElementById('cpAlertText');
            const btnSubmit = document.getElementById('btnSubmitCP');
            const btnText = document.getElementById('btnTextCP');
            const btnSpinner = document.getElementById('btnSpinnerCP');

            alertBox.classList.add('hidden');

            const oldPass = document.getElementById('cpOldPassword').value.trim();
            const newPass = document.getElementById('cpNewPassword').value.trim();
            const confPass = document.getElementById('cpConfirmPassword').value.trim();

            if (newPass.length < 4) {
                alertText.innerText = "Password baru minimal 4 karakter!";
                alertBox.classList.remove('hidden');
                return;
            }

            if (newPass !== confPass) {
                alertText.innerText = "Konfirmasi password baru tidak cocok!";
                alertBox.classList.remove('hidden');
                return;
            }

            btnSubmit.disabled = true;
            btnText.innerText = "Menyimpan...";
            btnSpinner.classList.remove('hidden');

            const formData = new FormData(document.getElementById('changePasswordForm'));

            try {
                const response = await fetch(`${BASE_URL}/ats/changePassword`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showToast(result.message, 'success');
                    closeChangePasswordModal();
                    document.getElementById('changePasswordForm').reset();
                } else {
                    alertText.innerText = result.message || 'Gagal mengubah password.';
                    alertBox.classList.remove('hidden');
                }
            } catch (err) {
                alertText.innerText = "Terjadi kesalahan jaringan atau server.";
                alertBox.classList.remove('hidden');
            } finally {
                btnSubmit.disabled = false;
                btnText.innerText = "Perbarui Password";
                btnSpinner.classList.add('hidden');
            }
        }

        // 13. Analytics Charts (Filtered by Kecamatan)
        async function loadAnalyticsData() {
            const kec = document.getElementById('analyticsFilterKec').value;
            const refreshIcon = document.getElementById('analyticsRefreshIcon');
            refreshIcon.classList.add('fa-spin');

            try {
                const res = await fetch(`${BASE_URL}ats/analyticsData?kecamatan=${encodeURIComponent(kec)}`);
                const json = await res.json();

                if (json.status !== 'success') return;

                const { summary, charts } = json;

                document.getElementById('statTotalData').innerText = summary.totalData;
                document.getElementById('statGender').innerText = `${summary.male} / ${summary.female}`;
                document.getElementById('statAvgAge').innerText = summary.avgAge;
                document.getElementById('statTopIjazah').innerText = summary.topIjazah;
                document.getElementById('statTopIjazah').title = summary.topIjazah;

                document.getElementById('statTotalAts').innerText = summary.totalAts;
                document.getElementById('statTotalSekolah').innerText = summary.totalSekolah;
                document.getElementById('statTotalDisabilitas').innerText = summary.disabilitas;
                document.getElementById('statTotalKip').innerText = summary.totalKip;

                renderAnalyticsCharts(charts);
            } catch (err) {
                console.error("Gagal analitik:", err);
            } finally {
                refreshIcon.classList.remove('fa-spin');
            }
        }

        function renderAnalyticsCharts(charts) {
            Object.keys(chartsObj).forEach(k => {
                if (chartsObj[k]) chartsObj[k].destroy();
            });

            const defaultOpts = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        color: '#ffffff',
                        font: { weight: 'bold', size: 9 },
                        formatter: (val) => val > 0 ? val : ''
                    }
                },
                scales: {
                    x: { ticks: { font: { size: 9 } }, grid: { display: false } },
                    y: { ticks: { font: { size: 9 }, stepSize: 1 }, grid: { color: '#f1f5f9' } }
                }
            };

            // Chart 1: Kecamatan
            chartsObj.cKec = new Chart(document.getElementById('chartKec'), {
                type: 'bar',
                data: {
                    labels: charts.kecamatan.labels,
                    datasets: [{
                        data: charts.kecamatan.values,
                        backgroundColor: '#4f46e5',
                        borderRadius: 4,
                    }]
                },
                options: defaultOpts
            });

            // Chart 2: Umur
            chartsObj.cUmur = new Chart(document.getElementById('chartUmur'), {
                type: 'bar',
                data: {
                    labels: charts.umur.labels,
                    datasets: [{
                        data: charts.umur.values,
                        backgroundColor: '#059669',
                        borderRadius: 4,
                    }]
                },
                options: defaultOpts
            });

            // Chart 3: Minat
            chartsObj.cKeinginan = new Chart(document.getElementById('chartKeinginan'), {
                type: 'doughnut',
                data: {
                    labels: charts.keinginan.labels,
                    datasets: [{
                        data: charts.keinginan.values,
                        backgroundColor: ['#4f46e5', '#f59e0b'],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } },
                        datalabels: { color: '#fff', font: { weight: 'bold', size: 9 } }
                    }
                }
            });

            // Chart 4: Bentuk
            chartsObj.cBentuk = new Chart(document.getElementById('chartBentuk'), {
                type: 'pie',
                data: {
                    labels: charts.bentuk.labels,
                    datasets: [{
                        data: charts.bentuk.values,
                        backgroundColor: ['#6366f1', '#ec4899'],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } },
                        datalabels: { color: '#fff', font: { weight: 'bold', size: 9 } }
                    }
                }
            });

            // Chart 5: Alasan (Horizontal)
            chartsObj.cAlasan = new Chart(document.getElementById('chartAlasan'), {
                type: 'bar',
                data: {
                    labels: charts.alasan.labels,
                    datasets: [{
                        data: charts.alasan.values,
                        backgroundColor: '#f59e0b',
                        borderRadius: 3,
                    }]
                },
                options: {
                    ...defaultOpts,
                    indexAxis: 'y',
                    scales: {
                        y: { ticks: { font: { size: 8 } }, grid: { display: false } },
                        x: { ticks: { font: { size: 9 } }, grid: { color: '#f1f5f9' } }
                    }
                }
            });

            // Chart 6: Keterampilan
            chartsObj.cKeterampilan = new Chart(document.getElementById('chartKeterampilan'), {
                type: 'bar',
                data: {
                    labels: charts.keterampilan.labels,
                    datasets: [{
                        data: charts.keterampilan.values,
                        backgroundColor: '#ea580c',
                        borderRadius: 4,
                    }]
                },
                options: defaultOpts
            });

            // Chart 7: Baju
            chartsObj.cBaju = new Chart(document.getElementById('chartBaju'), {
                type: 'bar',
                data: {
                    labels: charts.baju.labels,
                    datasets: [{
                        data: charts.baju.values,
                        backgroundColor: '#7c3aed',
                        borderRadius: 4,
                    }]
                },
                options: defaultOpts
            });

            // Chart 8: Sepatu
            chartsObj.cSepatu = new Chart(document.getElementById('chartSepatu'), {
                type: 'bar',
                data: {
                    labels: charts.sepatu.labels,
                    datasets: [{
                        data: charts.sepatu.values,
                        backgroundColor: '#0891b2',
                        borderRadius: 4,
                    }]
                },
                options: defaultOpts
            });
        }

        // 14. Toast Helper
        function showToast(message, type = "info") {
            const box = document.getElementById('toastBox');
            const icon = document.getElementById('toastIcon');
            const msg = document.getElementById('toastMessage');

            msg.innerText = message;
            box.className = "fixed top-4 right-4 z-50 flex items-center gap-2 px-3 py-2 rounded-lg shadow-lg text-white text-xs font-semibold transition-all duration-200";

            if (type === "success") {
                box.classList.add("bg-emerald-600");
                icon.className = "fas fa-check-circle text-xs";
            } else if (type === "error") {
                box.classList.add("bg-red-600");
                icon.className = "fas fa-triangle-exclamation text-xs";
            } else {
                box.classList.add("bg-indigo-700");
                icon.className = "fas fa-info-circle text-xs";
            }

            box.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-[-8px]');
            box.classList.add('opacity-100', 'translate-y-0');

            setTimeout(() => {
                box.classList.remove('opacity-100', 'translate-y-0');
                box.classList.add('opacity-0', 'pointer-events-none', 'translate-y-[-8px]');
            }, 3000);
        }
    </script>
</body>
</html>
