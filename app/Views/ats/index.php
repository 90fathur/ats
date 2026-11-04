<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PANTAU ATS - Pemantauan dan Pemutakhiran Data Anak Tidak Sekolah | Kab. Polewali Mandar</title>

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

    <!-- Leaflet CSS & JS for WebGIS Polman -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

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

        /* WebGIS Custom Styles */
        #atsGisMap {
            width: 100%;
            height: 560px;
            border-radius: 12px;
            z-index: 1;
        }
        .gis-pulse-marker {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #ffffff;
            font-weight: 800;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .gis-pulse-marker:hover {
            transform: scale(1.18);
            z-index: 1000 !important;
        }
        .gis-radar-ring {
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            pointer-events: none;
            animation: gisRadar 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        @keyframes gisRadar {
            0% { transform: scale(0.6); opacity: 0.9; }
            100% { transform: scale(2.4); opacity: 0; }
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
                        PANTAU ATS
                    </h1>
                    <p class="text-[8.5px] text-indigo-600 font-bold leading-tight">Pemantauan & Pemutakhiran</p>
                    <p class="text-[8px] text-slate-400 font-medium leading-none">Kab. Polewali Mandar</p>
                </div>
            </div>
            <!-- Close button for mobile -->
            <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-slate-800 p-1">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <!-- Scope Badge -->
        <div class="mx-3 mt-3 px-2.5 py-2 rounded-lg <?= $isAdmin ? 'bg-indigo-50/70 border-indigo-100' : 'bg-emerald-50/70 border-emerald-100' ?> border text-[10px]">
            <div class="text-slate-500 flex items-center gap-1.5 font-medium">
                <i class="fas fa-map-location-dot <?= $isAdmin ? 'text-indigo-600' : 'text-emerald-600' ?> text-[11px]"></i>
                <span class="uppercase tracking-wider text-[9px] <?= $isAdmin ? 'text-indigo-700' : 'text-emerald-700' ?> font-bold">Skop Wilayah Kerja</span>
            </div>
            <div class="font-bold text-slate-800 text-[11px] mt-0.5">Kab. Polewali Mandar</div>
            <div class="text-[9px] <?= $isAdmin ? 'text-indigo-600' : 'text-emerald-700' ?> font-semibold flex items-center gap-1 mt-0.5">
                <?php if ($isAdmin): ?>
                    <i class="fas fa-shield-halved text-[8px] text-indigo-600"></i>
                    <span>Akses Penuh (16 Kecamatan se-Polman)</span>
                <?php elseif ($currentUser['isSingleDesa']): ?>
                    <i class="fas fa-check-circle text-[8px] text-emerald-600"></i>
                    <span>Tim Entri Desa: <strong><?= esc($currentUser['singleDesaName']) ?></strong></span>
                <?php else: ?>
                    <i class="fas fa-user-check text-[8px] text-emerald-600"></i>
                    <span>Tim Entri (<?= count($currentUser['allowedDesa']) ?> Desa)</span>
                <?php endif; ?>
            </div>
            <?php if (!$isAdmin): ?>
                <div class="text-[8.5px] text-slate-500 mt-1 leading-tight" title="<?= esc(implode(', ', $currentUser['allowedDesa'])) ?>">
                    Kec. <?= esc($currentUser['singleKecName'] ?: implode(', ', $kecamatanList)) ?>
                    <?php if (!$currentUser['isSingleDesa'] && !empty($currentUser['allowedDesa'])): ?>
                        &bull; Desa: <strong class="text-emerald-800"><?= esc(implode(', ', $currentUser['allowedDesa'])) ?></strong>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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
                    <button type="button" onclick="navigateTab('map')" id="navItemMap" class="sidebar-item-inactive w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-map-location-dot w-4 text-center text-teal-600"></i>
                            <span>Peta Sebaran WebGIS</span>
                        </div>
                        <span class="bg-teal-50 text-teal-700 font-bold px-1.5 py-0.2 rounded text-[9px] border border-teal-200">16 Kec</span>
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
                    <button type="button" onclick="navigateTab('logs')" id="navItemLogs" class="sidebar-item-inactive w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-clock-rotate-left w-4 text-center text-slate-500"></i>
                            <span>Riwayat & Log</span>
                        </div>
                        <span class="bg-slate-100 text-slate-600 font-bold px-1.5 py-0.2 rounded text-[9px]">Live</span>
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

            <?php if ($isAdmin): ?>
            <!-- Section: Administrasi & Hak Akses (Admin Only) -->
            <div>
                <p class="px-2 text-[9px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Pengaturan</p>
                <div class="space-y-0.5">
                    <button type="button" onclick="navigateTab('users')" id="navItemUsers" class="sidebar-item-inactive w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-users-gear w-4 text-center text-indigo-600"></i>
                            <span>Manajemen Akun</span>
                        </div>
                        <span class="bg-indigo-50 text-indigo-700 font-bold px-1.5 py-0.2 rounded text-[9px]">Level 2</span>
                    </button>
                </div>
            </div>
            <?php endif; ?>
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
                    <span class="font-bold text-indigo-600">PANTAU ATS</span>
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
                            <span>Dashboard Analitik PANTAU ATS - Kabupaten Polewali Mandar</span>
                        </h2>
                        <p class="text-[10px] text-slate-500 mt-0.5">Pemantauan & Pemutakhiran Data Hasil Rekonfirmasi Anak Tidak Sekolah (ATS) di 16 Kecamatan</p>
                    </div>

                    <!-- Filter Kecamatan & Desa -->
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Wilayah:</span>
                        <select id="analyticsFilterKec" onchange="handleAnalyticsKecChange()" class="compact-input font-semibold text-slate-700">
                            <option value="Semua"><?= $isAdmin ? 'Semua Kecamatan' : 'Kecamatan Tugas' ?></option>
                            <?php foreach ($kecamatanList as $kec): ?>
                                <option value="<?= esc($kec) ?>"><?= esc(strtoupper($kec)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="analyticsFilterDesa" onchange="loadAnalyticsData()" class="compact-input font-semibold text-slate-700">
                            <option value="Semua"><?= ($currentUser['isSingleDesa']) ? esc($currentUser['singleDesaName']) : 'Semua Desa/Kel' ?></option>
                            <?php if (!empty($desaList)): ?>
                                <?php foreach ($desaList as $d): ?>
                                    <option value="<?= esc($d) ?>"><?= esc(strtoupper($d)) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
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

                <!-- ==================== EXECUTIVE BANNER: TARGET SASARAN KEMBALI SEKOLAH ==================== -->
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-4 text-white shadow-md border border-indigo-800/40">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-indigo-800/50">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[10px] font-bold uppercase tracking-wider">
                                    <i class="fas fa-bullseye mr-1"></i> Rekapitulasi Rencana Kembali Bersekolah
                                </span>
                                <span class="text-[11px] text-indigo-300 font-medium">Kab. Polewali Mandar</span>
                            </div>
                            <h3 class="text-sm sm:text-base font-extrabold text-white mt-1">Data Anak ATS Yang Akan Kembali Bersekolah</h3>
                            <p class="text-[10px] sm:text-[11px] text-indigo-200/80">Distribusi target anak ke Jalur Formal (SD, SMP, SMA) dan Jalur Non-Formal Kesetaraan (Paket A, B, C & Lembaga SKB).</p>
                        </div>
                        <!-- Big Metric: Total Ingin Kembali -->
                        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-xs px-3.5 py-2 rounded-xl border border-white/15 shrink-0">
                            <div>
                                <span class="text-[9.5px] uppercase font-bold text-indigo-200 block tracking-wider">Total Ingin Kembali</span>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl font-black text-emerald-400 font-mono" id="statTotalInginKembali">0</span>
                                    <span class="text-xs font-semibold text-white">Anak</span>
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-lg shadow-sm">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                        </div>
                    </div>

                    <!-- 7 Metric Target Grid (SD, SMP, SMA, Paket A, Paket B, Paket C, SKB) -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2 pt-3">
                        <!-- Target SD -->
                        <div class="bg-white/10 hover:bg-white/15 transition p-2.5 rounded-xl border border-white/10 flex flex-col justify-between">
                            <div class="flex items-center justify-between text-indigo-200 text-[10px] font-bold">
                                <span>KE SD / MI</span>
                                <i class="fas fa-child text-sky-400"></i>
                            </div>
                            <div class="mt-2">
                                <div class="text-lg font-black text-white font-mono" id="statTargetSD">0</div>
                                <span class="text-[9px] text-sky-300 font-semibold">Usia 7-12 / BPB</span>
                            </div>
                        </div>

                        <!-- Target SMP -->
                        <div class="bg-white/10 hover:bg-white/15 transition p-2.5 rounded-xl border border-white/10 flex flex-col justify-between">
                            <div class="flex items-center justify-between text-indigo-200 text-[10px] font-bold">
                                <span>KE SMP / MTS</span>
                                <i class="fas fa-user-graduate text-indigo-300"></i>
                            </div>
                            <div class="mt-2">
                                <div class="text-lg font-black text-white font-mono" id="statTargetSMP">0</div>
                                <span class="text-[9px] text-indigo-300 font-semibold">Usia 13-15 / Tamat SD</span>
                            </div>
                        </div>

                        <!-- Target SMA -->
                        <div class="bg-white/10 hover:bg-white/15 transition p-2.5 rounded-xl border border-white/10 flex flex-col justify-between">
                            <div class="flex items-center justify-between text-indigo-200 text-[10px] font-bold">
                                <span>KE SMA</span>
                                <i class="fas fa-building-columns text-amber-400"></i>
                            </div>
                            <div class="mt-2">
                                <div class="text-lg font-black text-white font-mono" id="statTargetSMA">0</div>
                                <span class="text-[9px] text-amber-300 font-semibold">Usia 16-18 / Tamat SMP</span>
                            </div>
                        </div>

                        <!-- Target PAKET A -->
                        <div class="bg-emerald-950/40 hover:bg-emerald-950/60 transition p-2.5 rounded-xl border border-emerald-500/30 flex flex-col justify-between">
                            <div class="flex items-center justify-between text-emerald-200 text-[10px] font-bold">
                                <span>PAKET A (PKBM)</span>
                                <i class="fas fa-book-open text-emerald-400"></i>
                            </div>
                            <div class="mt-2">
                                <div class="text-lg font-black text-emerald-300 font-mono" id="statTargetPaketA">0</div>
                                <span class="text-[9px] text-emerald-200/90 font-semibold">Setara SD</span>
                            </div>
                        </div>

                        <!-- Target PAKET B -->
                        <div class="bg-teal-950/40 hover:bg-teal-950/60 transition p-2.5 rounded-xl border border-teal-500/30 flex flex-col justify-between">
                            <div class="flex items-center justify-between text-teal-200 text-[10px] font-bold">
                                <span>PAKET B (PKBM)</span>
                                <i class="fas fa-book-bookmark text-teal-400"></i>
                            </div>
                            <div class="mt-2">
                                <div class="text-lg font-black text-teal-300 font-mono" id="statTargetPaketB">0</div>
                                <span class="text-[9px] text-teal-200/90 font-semibold">Setara SMP</span>
                            </div>
                        </div>

                        <!-- Target PAKET C -->
                        <div class="bg-purple-950/40 hover:bg-purple-950/60 transition p-2.5 rounded-xl border border-purple-500/30 flex flex-col justify-between">
                            <div class="flex items-center justify-between text-purple-200 text-[10px] font-bold">
                                <span>PAKET C (PKBM)</span>
                                <i class="fas fa-certificate text-purple-400"></i>
                            </div>
                            <div class="mt-2">
                                <div class="text-lg font-black text-purple-300 font-mono" id="statTargetPaketC">0</div>
                                <span class="text-[9px] text-purple-200/90 font-semibold">Setara SMA</span>
                            </div>
                        </div>

                        <!-- Target SKB -->
                        <div class="bg-orange-950/40 hover:bg-orange-950/60 transition p-2.5 rounded-xl border border-orange-500/30 flex flex-col justify-between">
                            <div class="flex items-center justify-between text-orange-200 text-[10px] font-bold">
                                <span>LEMBAGA SKB</span>
                                <i class="fas fa-landmark text-orange-400"></i>
                            </div>
                            <div class="mt-2">
                                <div class="text-lg font-black text-orange-300 font-mono" id="statTargetSKB">0</div>
                                <span class="text-[9px] text-orange-200/90 font-semibold">SKB Polman</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WebGIS Quick Explorer Banner -->
                <div class="bg-gradient-to-r from-teal-800 via-indigo-900 to-slate-900 text-white p-3.5 rounded-xl shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur flex items-center justify-center text-teal-300 text-lg flex-shrink-0">
                            <i class="fas fa-map-location-dot"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-teal-200">Peta Geografis Sebaran ATS (WebGIS Polman)</h3>
                                <span class="px-1.5 py-0.2 bg-teal-500/20 text-teal-200 text-[9px] font-bold rounded border border-teal-400/30">16 Kecamatan</span>
                            </div>
                            <p class="text-[10px] text-slate-300 mt-0.5">Visualisasi spasial persebaran anak tidak sekolah dan konsentrasi wilayah intervensi di Kabupaten Polewali Mandar.</p>
                        </div>
                    </div>
                    <button type="button" onclick="navigateTab('map')" class="px-3.5 py-1.5 bg-teal-500 hover:bg-teal-400 text-teal-950 font-bold text-xs rounded-lg shadow-sm transition flex items-center gap-1.5 self-start sm:self-auto flex-shrink-0">
                        <i class="fas fa-earth-asia text-[11px]"></i>
                        <span>Buka Peta WebGIS</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </button>
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

                <!-- Charts Row 2: Rencana Kembali & Minat -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Chart: Target Program Kembali -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs lg:col-span-2">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight flex items-center gap-1.5">
                                <i class="fas fa-graduation-cap text-indigo-600 text-[10px]"></i> Distribusi Target Kembali Sekolah (SD, SMP, SMA, Paket A/B/C, SKB)
                            </span>
                            <span class="text-[10px] text-slate-400 font-semibold">Anak Ingin Kembali</span>
                        </div>
                        <div class="h-52 relative">
                            <canvas id="chartTargetKembali"></canvas>
                        </div>
                    </div>

                    <!-- Chart 3: Minat Kembali -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight flex items-center gap-1.5">
                                <i class="fas fa-circle-check text-blue-600 text-[10px]"></i> Minat Kembali Bersekolah
                            </span>
                        </div>
                        <div class="h-52 relative">
                            <canvas id="chartKeinginan"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 3: Bentuk & Alasan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Chart 4: Bentuk Pendidikan -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight flex items-center gap-1.5">
                                <i class="fas fa-school text-purple-600 text-[10px]"></i> Jalur Pendidikan Tujuan (Formal vs Non-Formal)
                            </span>
                        </div>
                        <div class="h-48 relative">
                            <canvas id="chartBentuk"></canvas>
                        </div>
                    </div>

                    <!-- Chart 5: Alasan -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
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
            <!-- SECTION 1.5: PETA GEOGRAFIS SEBARAN ATS (WEBGIS POLMAN)        -->
            <!-- ============================================================== -->
            <section id="panelMap" class="hidden space-y-3">
                <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-tight flex items-center gap-2">
                            <i class="fas fa-map-location-dot text-teal-600"></i>
                            <span>Peta Geografis Sebaran ATS (WebGIS Kabupaten Polewali Mandar)</span>
                        </h2>
                        <p class="text-[10px] text-slate-500 mt-0.5">Pemantauan visual spasial sebaran anak tidak sekolah dan target intervensi di 16 Kecamatan</p>
                    </div>

                    <!-- GIS Controls -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Target Filter on Map -->
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Target:</span>
                            <select id="gisFilterTarget" onchange="applyGisFilter()" class="compact-input font-semibold text-slate-700">
                                <option value="Semua">Semua Target</option>
                                <option value="SD">Target SD</option>
                                <option value="SMP">Target SMP</option>
                                <option value="SMA">Target SMA</option>
                                <option value="PAKET A">Target Paket A</option>
                                <option value="PAKET B">Target Paket B</option>
                                <option value="PAKET C">Target Paket C</option>
                                <option value="SKB">Target SKB</option>
                            </select>
                        </div>

                        <!-- Jump to Kecamatan -->
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Kecamatan:</span>
                            <select id="gisJumpKecamatan" onchange="handleGisKecJump()" class="compact-input font-semibold text-slate-700">
                                <option value="">Pilih / Sorot Kecamatan...</option>
                                <?php foreach ($allKecamatanList as $kec): ?>
                                    <option value="<?= esc(strtoupper($kec)) ?>"><?= esc(strtoupper($kec)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Basemap Switcher -->
                        <select id="gisBasemapSelect" onchange="changeGisBasemap()" class="compact-input font-semibold text-slate-700">
                            <option value="osm" selected>Peta Jalan (OpenStreetMap)</option>
                            <option value="topo">Peta Topografi (Esri Topo)</option>
                            <option value="street">Peta Wilayah (Esri Street)</option>
                            <option value="satellite">Citra Satelit (Esri World)</option>
                        </select>

                        <!-- Reset Zoom -->
                        <button type="button" onclick="resetGisMapView()" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold shadow-xs flex items-center gap-1 transition" title="Reset Zoom ke Seluruh Polman">
                            <i class="fas fa-expand text-[10px]"></i>
                            <span>Reset Zoom</span>
                        </button>
                    </div>
                </div>

                <!-- GIS Body Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
                    <!-- Map Container (3 cols on desktop) -->
                    <div class="lg:col-span-3 bg-white p-2 rounded-xl border border-slate-200 shadow-xs relative">
                        <div id="atsGisMap"></div>

                        <!-- Floating Legend Overlay -->
                        <div class="absolute bottom-5 left-5 z-[500] bg-white/95 backdrop-blur-xs p-2.5 rounded-lg border border-slate-200 shadow-md text-[10px] space-y-1.5 max-w-xs">
                            <span class="font-bold text-slate-800 uppercase tracking-wider block text-[9px] border-b border-slate-100 pb-1">
                                <i class="fas fa-layer-group text-teal-600 mr-1"></i> Tingkat Kerawanan ATS
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 border border-white shadow-2xs shrink-0"></span>
                                <span class="text-slate-600 font-medium">Rendah (&le; 5 Anak)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3.5 h-3.5 rounded-full bg-amber-500 border border-white shadow-2xs shrink-0"></span>
                                <span class="text-slate-600 font-medium">Sedang (6 - 20 Anak)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3.5 h-3.5 rounded-full bg-rose-600 border border-white shadow-2xs shrink-0 relative">
                                    <span class="gis-radar-ring bg-rose-500"></span>
                                </span>
                                <span class="text-slate-700 font-bold">Tinggi / Prioritas (> 20 Anak)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Side Info & Kecamatan Focus Card (1 col on desktop) -->
                    <div class="space-y-3">
                        <!-- Active Kecamatan Detail Card -->
                        <div id="gisKecCard" class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Kecamatan Terpilih</span>
                                    <h3 class="text-xs font-black text-slate-900 uppercase" id="gisCardKecName">Kec. Polewali</h3>
                                </div>
                                <span id="gisCardBadgeRisk" class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Sedang
                                </span>
                            </div>

                            <!-- Mini Metrics -->
                            <div class="grid grid-cols-2 gap-2 text-center">
                                <div class="p-2 rounded-lg bg-red-50 border border-red-100">
                                    <span class="text-[9px] text-red-600 font-bold block uppercase">Total ATS</span>
                                    <span class="text-lg font-black text-red-700 font-mono" id="gisCardTotalAts">0</span>
                                </div>
                                <div class="p-2 rounded-lg bg-emerald-50 border border-emerald-100">
                                    <span class="text-[9px] text-emerald-600 font-bold block uppercase">Masih Sekolah</span>
                                    <span class="text-lg font-black text-emerald-700 font-mono" id="gisCardTotalSekolah">0</span>
                                </div>
                            </div>

                            <!-- Target Program Breakdown -->
                            <div class="space-y-1.5 pt-1">
                                <span class="text-[9.5px] font-bold text-slate-700 uppercase tracking-wider block">Target Kembali Bersekolah</span>
                                
                                <div class="space-y-1 text-[10px]">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-600"><i class="fas fa-child text-sky-500 mr-1"></i> SD / MI</span>
                                        <span class="font-bold text-slate-800" id="gisTargetSD">0</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-600"><i class="fas fa-user-graduate text-indigo-500 mr-1"></i> SMP / MTs</span>
                                        <span class="font-bold text-slate-800" id="gisTargetSMP">0</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-600"><i class="fas fa-building-columns text-amber-500 mr-1"></i> SMA</span>
                                        <span class="font-bold text-slate-800" id="gisTargetSMA">0</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-600"><i class="fas fa-book-open text-emerald-500 mr-1"></i> Paket A (Setara SD)</span>
                                        <span class="font-bold text-slate-800" id="gisTargetPaketA">0</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-600"><i class="fas fa-book-bookmark text-teal-500 mr-1"></i> Paket B (Setara SMP)</span>
                                        <span class="font-bold text-slate-800" id="gisTargetPaketB">0</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-600"><i class="fas fa-certificate text-purple-500 mr-1"></i> Paket C (Setara SMA)</span>
                                        <span class="font-bold text-slate-800" id="gisTargetPaketC">0</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-600"><i class="fas fa-landmark text-orange-500 mr-1"></i> Lembaga SKB</span>
                                        <span class="font-bold text-slate-800" id="gisTargetSKB">0</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Alasan Terbanyak -->
                            <div class="pt-2 border-t border-slate-100">
                                <span class="text-[9px] text-slate-400 font-bold block uppercase tracking-wider">Alasan Dominan di Wilayah Ini:</span>
                                <p class="text-[10px] font-bold text-slate-800 mt-0.5 line-clamp-2" id="gisCardTopAlasan">-</p>
                            </div>

                            <!-- Button Jump to Table -->
                            <button type="button" onclick="filterTableFromGis()" class="w-full py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg shadow-xs flex items-center justify-center gap-1.5 transition">
                                <i class="fas fa-table-list text-[10px]"></i>
                                <span>Lihat Data Anak Kecamatan Ini</span>
                            </button>
                        </div>

                        <!-- Top 5 Highest ATS Ranking -->
                        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs space-y-2">
                            <span class="text-[10px] font-bold text-slate-800 uppercase tracking-wider block flex items-center gap-1.5">
                                <i class="fas fa-ranking-star text-amber-500"></i> 5 Kecamatan ATS Terbanyak
                            </span>
                            <div id="gisRankingList" class="space-y-1.5 text-[10px]">
                                <!-- Populated dynamically -->
                            </div>
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
                        <select id="tableFilterKecamatan" onchange="handleTableKecChange()" class="compact-input font-medium text-slate-700">
                            <option value="Semua"><?= $isAdmin ? 'Semua Kecamatan' : 'Kecamatan Tugas' ?></option>
                            <?php foreach ($kecamatanList as $kec): ?>
                                <option value="<?= esc($kec) ?>"><?= esc(strtoupper($kec)) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Filter Desa -->
                        <select id="tableFilterDesa" onchange="loadTableData()" class="compact-input font-medium text-slate-700">
                            <option value="Semua"><?= ($currentUser['isSingleDesa']) ? esc($currentUser['singleDesaName']) : 'Semua Desa/Kel' ?></option>
                            <?php if (!empty($desaList)): ?>
                                <?php foreach ($desaList as $d): ?>
                                    <option value="<?= esc($d) ?>"><?= esc(strtoupper($d)) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>

                        <!-- Filter Target Kembali -->
                        <select id="tableFilterTarget" onchange="filterLocalTable()" class="compact-input font-medium text-slate-700">
                            <option value="">Semua Target</option>
                            <option value="SD">Target SD</option>
                            <option value="SMP">Target SMP</option>
                            <option value="SMA">Target SMA</option>
                            <option value="PAKET A">Target Paket A</option>
                            <option value="PAKET B">Target Paket B</option>
                            <option value="PAKET C">Target Paket C</option>
                            <option value="SKB">Target SKB</option>
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
                                    <th class="py-2 px-3 text-center">Target Kembali</th>
                                    <th class="py-2 px-3">Minat / Rencana</th>
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
                            <span>Formulir PANTAU ATS - Pemantauan dan Pemutakhiran Data</span>
                        </h2>
                        <p class="text-[10px] text-slate-400">Pilih Kecamatan (16 Kecamatan Polman) dan Kelurahan/Desa penugasan, lalu lengkapi biodata anak</p>
                    </div>

                    <div id="editBannerNotice" class="hidden items-center gap-1.5 px-2.5 py-1 bg-amber-50 border border-amber-300 text-amber-900 rounded-md text-[10px] font-bold">
                        <i class="fas fa-info-circle text-amber-600"></i>
                        <span>Mode Edit: <strong id="editNoticeNik" class="font-mono"></strong></span>
                        <button type="button" onclick="cancelEditMode()" class="text-red-600 hover:underline ml-1">Batal</button>
                    </div>
                </div>

                <?php if (!$isAdmin && $currentUser['isSingleDesa']): ?>
                <!-- Single Desa Auto-Lock Notice -->
                <div class="px-3.5 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] shadow-2xs flex-shrink-0">
                            <i class="fas fa-location-dot"></i>
                        </span>
                        <div>
                            <span class="font-bold text-slate-900">Wilayah Penugasan: </span>
                            <span class="font-extrabold text-emerald-800">Desa/Kel. <?= esc($currentUser['singleDesaName']) ?>, Kec. <?= esc($currentUser['singleKecName']) ?></span>
                            <p class="text-[10px] text-emerald-700 mt-0.5">Kecamatan dan Desa otomatis terkunci ke wilayah tugas Anda. Silakan langsung menginput data anak di bawah.</p>
                        </div>
                    </div>
                    <span class="bg-emerald-200/80 text-emerald-900 font-bold px-2 py-0.5 rounded text-[10px] whitespace-nowrap">Otomatis Terkunci</span>
                </div>
                <?php endif; ?>

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
                                </select>
                            </div>
                            <div>
                                <label for="kecamatan" class="compact-label">Kecamatan <span class="text-red-500">*</span></label>
                                <select id="kecamatan" name="kecamatan" required onchange="handleKecamatanChange()" class="compact-input w-full font-semibold <?= ($currentUser['isSingleDesa'] ? 'bg-slate-100' : '') ?>">
                                    <option value="">PILIH KECAMATAN...</option>
                                    <?php foreach ($kecamatanList as $kec): ?>
                                        <option value="<?= esc($kec) ?>" <?= ($currentUser['isSingleDesa'] && strcasecmp($kec, $currentUser['singleKecName']) === 0) ? 'selected' : '' ?>><?= esc(strtoupper($kec)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="desa" class="compact-label">Kelurahan / Desa <span class="text-red-500">*</span></label>
                                <select id="desa" name="desa" required class="compact-input w-full font-semibold <?= ($currentUser['isSingleDesa'] ? 'bg-slate-100' : '') ?>">
                                    <option value="">PILIH DESA/KELURAHAN...</option>
                                    <?php if (!empty($desaList)): ?>
                                        <?php foreach ($desaList as $d): ?>
                                            <option value="<?= esc($d) ?>" <?= ($currentUser['isSingleDesa'] && strcasecmp($d, $currentUser['singleDesaName']) === 0) ? 'selected' : '' ?>><?= esc(strtoupper($d)) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
                                <label for="alasan" class="compact-label">Alasan & Klasifikasi ATS <span class="text-red-500">*</span></label>
                                <select id="alasan" name="alasan" onchange="handleAlasanChange()" class="compact-input w-full font-semibold">
                                    <option value="">PILIH ALASAN / KLASIFIKASI...</option>
                                    <optgroup label="Alasan Tidak Sekolah">
                                        <option value="Tidak mau bersekolah">Tidak mau bersekolah</option>
                                        <option value="Tidak ada biaya">Tidak ada biaya</option>
                                        <option value="Sekolah jauh dari rumah">Sekolah jauh dari rumah</option>
                                        <option value="Sudah cukup dengan tingkat pendidikan yang dimiliki saat ini">Sudah cukup dengan tingkat pendidikan yang dimiliki saat ini</option>
                                        <option value="Menikah / mengurus rumah tangga">Menikah / mengurus rumah tangga</option>
                                        <option value="Mengalami kekerasan/perundungan/trauma di sekolah">Mengalami kekerasan/perundungan/trauma di sekolah</option>
                                        <option value="Bekerja">Bekerja</option>
                                        <option value="Pengaruh lingkungan/teman">Pengaruh lingkungan/teman</option>
                                        <option value="Beranggapan sekolah tidak penting">Beranggapan sekolah tidak penting</option>
                                        <option value="Tidak memiliki seragam sekolah">Tidak memiliki seragam sekolah</option>
                                        <option value="Tidak memiliki Akta Kelahiran">Tidak memiliki Akta Kelahiran</option>
                                        <option value="Masalah Kesehatan / Penyandang Disabilitas">Masalah Kesehatan / Penyandang Disabilitas</option>
                                        <option value="Lainnya">Lainnya</option>
                                        <option value="Dikeluarkan">Dikeluarkan</option>
                                        <option value="Mengundurkan Diri">Mengundurkan Diri</option>
                                    </optgroup>
                                    <optgroup label="Hasil Rekonfirmasi / Pemutakhiran Lapangan">
                                        <option value="Anak Tidak Ditemukan">Anak Tidak Ditemukan</option>
                                        <option value="Melanjutkan ke Luar Negeri">Melanjutkan ke Luar Negeri</option>
                                        <option value="Melanjutkan ke Pondok Pesantren atau Lainnya">Melanjutkan ke Pondok Pesantren atau Lainnya</option>
                                        <option value="Meninggal Dunia">Meninggal Dunia</option>
                                        <option value="Bersekolah">Bersekolah</option>
                                        <option value="Pindah Domisili">Pindah Domisili</option>
                                        <option value="Sudah Tamat SMA/Sederajat">Sudah Tamat SMA/Sederajat</option>
                                        <option value="Bukan Penduduk Desa Tersebut">Bukan Penduduk Desa Tersebut</option>
                                        <option value="Bukan ATS Usia 6-18">Bukan ATS Usia 6-18</option>
                                    </optgroup>
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
                        <div id="sectionInginSekolah" class="hidden grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 p-3 rounded-lg bg-slate-50 border border-slate-200">
                            <div>
                                <label for="targetKembali" class="compact-label font-bold text-slate-800">Target Program Kembali <span class="text-red-500">*</span></label>
                                <select id="targetKembali" name="targetKembali" onchange="handleTargetKembaliChange()" class="compact-input w-full font-bold text-indigo-700">
                                    <option value="">PILIH TARGET...</option>
                                    <optgroup label="Jalur Formal">
                                        <option value="SD">SD / MI (Formal)</option>
                                        <option value="SMP">SMP / MTs (Formal)</option>
                                        <option value="SMA">SMA (Formal)</option>
                                    </optgroup>
                                    <optgroup label="Jalur Non-Formal / Kesetaraan">
                                        <option value="PAKET A">PAKET A (Setara SD)</option>
                                        <option value="PAKET B">PAKET B (Setara SMP)</option>
                                        <option value="PAKET C">PAKET C (Setara SMA)</option>
                                        <option value="SKB">SKB (Sanggar Kegiatan Belajar)</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div>
                                <label for="bentukPendidikan" class="compact-label">Bentuk Pendidikan <span class="text-red-500">*</span></label>
                                <select id="bentukPendidikan" name="bentukPendidikan" onchange="handleBentukChange()" class="compact-input w-full font-semibold">
                                    <option value="">PILIH...</option>
                                    <option value="FORMAL">FORMAL (SD/SMP/SMA)</option>
                                    <option value="NON FORMAL">NON FORMAL (PAKET/SKB)</option>
                                </select>
                            </div>
                            <div>
                                <label for="satuanPendidikan" class="compact-label">Nama Sekolah / PKBM / SKB <span class="text-red-500">*</span></label>
                                <input type="text" id="satuanPendidikan" name="satuanPendidikan" style="text-transform:uppercase" placeholder="Misal: SDN 005 / SKB Polman / PKBM" class="compact-input w-full font-semibold">
                            </div>
                            <div>
                                <label for="baju" class="compact-label" id="labelBaju">Ukuran Baju Seragam <span id="bajuReqMark" class="text-red-500 hidden">*</span></label>
                                <select id="baju" name="baju" class="compact-input w-full font-semibold">
                                    <option value="">PILIH UKURAN...</option>
                                    <option value="-">- (TIDAK ADA)</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                    <option value="XXL">XXL</option>
                                </select>
                                <span id="bajuNotice" class="text-[9px] text-slate-500 block mt-0.5 hidden"></span>
                            </div>
                            <div id="containerSepatu">
                                <label for="sepatu" class="compact-label" id="labelSepatu">Ukuran Sepatu <span id="sepatuReqMark" class="text-red-500 hidden">*</span></label>
                                <input type="number" id="sepatu" name="sepatu" min="25" max="46" placeholder="Contoh: 38" class="compact-input w-full font-mono">
                                <span id="sepatuNotice" class="text-[9px] text-slate-400 block mt-0.5 hidden"></span>
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
                <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-tight flex items-center gap-1.5">
                            <i class="fas fa-file-excel text-emerald-600"></i>
                            <span>Pusat Unduhan & Laporan Ekspor PANTAU ATS - Kabupaten Polewali Mandar</span>
                        </h2>
                        <p class="text-[10px] text-slate-500 mt-0.5">Format file Microsoft Excel (.xlsx) per tingkatan target sasaran anak.</p>
                    </div>

                    <!-- Filter Wilayah untuk Ekspor -->
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Kecamatan:</span>
                            <select id="exportFilterKecamatan" onchange="handleExportKecChange()" class="compact-input font-semibold text-slate-700">
                                <option value="Semua"><?= $isAdmin ? 'Semua (16 Kecamatan)' : 'Semua Wilayah Tugas' ?></option>
                                <?php foreach ($kecamatanList as $kec): ?>
                                    <option value="<?= esc($kec) ?>"><?= esc(strtoupper($kec)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Desa/Kel:</span>
                            <select id="exportFilterDesa" onchange="updateExportLinks()" class="compact-input font-semibold text-slate-700">
                                <option value="Semua"><?= $currentUser['isSingleDesa'] ? esc($currentUser['singleDesaName']) : 'Semua Desa/Kelurahan' ?></option>
                                <?php if (!empty($currentUser['desa_akses'])): ?>
                                    <?php foreach ($currentUser['allowedDesa'] as $d): ?>
                                        <option value="<?= esc($d) ?>"><?= esc($d) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
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
                        <a href="<?= base_url('ats/export/excel?target=Semua') ?>" data-base="<?= base_url('ats/export/excel?target=Semua') ?>" class="export-btn-link mt-3 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg text-center flex items-center justify-center gap-1.5 shadow-xs transition">
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
                        <a href="<?= base_url('ats/export/excel?target=SD') ?>" data-base="<?= base_url('ats/export/excel?target=SD') ?>" class="export-btn-link mt-3 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs rounded-lg text-center flex items-center justify-center gap-1.5 transition">
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
                        <a href="<?= base_url('ats/export/excel?target=SMP') ?>" data-base="<?= base_url('ats/export/excel?target=SMP') ?>" class="export-btn-link mt-3 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs rounded-lg text-center flex items-center justify-center gap-1.5 transition">
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
                                <h3 class="text-xs font-bold text-slate-900">Target Intervensi SMA</h3>
                            </div>
                            <p class="text-[10px] text-slate-500">Usia 16-18 tahun atau lulusan SMP/MTs.</p>
                        </div>
                        <a href="<?= base_url('ats/export/excel?target=SMA') ?>" data-base="<?= base_url('ats/export/excel?target=SMA') ?>" class="export-btn-link mt-3 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs rounded-lg text-center flex items-center justify-center gap-1.5 transition">
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
                        <div class="mt-3 grid grid-cols-4 gap-1">
                            <a href="<?= base_url('ats/export/excel?target=PAKET%20A') ?>" data-base="<?= base_url('ats/export/excel?target=PAKET%20A') ?>" class="export-btn-link px-1.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[10px] rounded text-center">Paket A</a>
                            <a href="<?= base_url('ats/export/excel?target=PAKET%20B') ?>" data-base="<?= base_url('ats/export/excel?target=PAKET%20B') ?>" class="export-btn-link px-1.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[10px] rounded text-center">Paket B</a>
                            <a href="<?= base_url('ats/export/excel?target=PAKET%20C') ?>" data-base="<?= base_url('ats/export/excel?target=PAKET%20C') ?>" class="export-btn-link px-1.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[10px] rounded text-center">Paket C</a>
                            <a href="<?= base_url('ats/export/excel?target=SKB') ?>" data-base="<?= base_url('ats/export/excel?target=SKB') ?>" class="export-btn-link px-1.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-semibold text-[10px] rounded text-center">SKB</a>
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
                        <a href="<?= base_url('ats/print/report') ?>" id="btnCetakReportLink" data-base="<?= base_url('ats/print/report') ?>" target="_blank" class="mt-3 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg text-center flex items-center justify-center gap-1.5 transition">
                            <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                            <span>Buka Dokumen PDF</span>
                        </a>
                    </div>
                </div>
            </section>

            <?php if ($isAdmin): ?>
            <!-- ============================================================== -->
            <!-- SECTION 5: MANAJEMEN PENGGUNA (ADMIN ONLY - LEVEL 2 ACCOUNTS)  -->
            <!-- ============================================================== -->
            <section id="panelUsers" class="hidden space-y-3">
                <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-tight flex items-center gap-2">
                            <i class="fas fa-users-gear text-indigo-600"></i>
                            <span>Manajemen Pengguna & Penugasan Wilayah Tim Entri</span>
                        </h2>
                        <p class="text-[10px] text-slate-500 mt-0.5">Kelola akun Level 2 (Tim Entri) dengan batasan akses ke satu atau beberapa kecamatan, serta Administrator (Level 1).</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="openUserModal()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs flex items-center gap-1.5 transition">
                            <i class="fas fa-user-plus text-[10px]"></i>
                            <span>Tambah Akun Baru</span>
                        </button>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="py-2.5 px-3 text-center w-12">No</th>
                                    <th class="py-2.5 px-3">Nama & Username</th>
                                    <th class="py-2.5 px-3">Level / Role</th>
                                    <th class="py-2.5 px-3">No. HP Petugas</th>
                                    <th class="py-2.5 px-3">Wilayah Tugas (Kecamatan & Desa)</th>
                                    <th class="py-2.5 px-3 text-center w-28">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody" class="divide-y divide-slate-100 text-xs text-slate-700">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                    <div id="usersLoading" class="py-8 text-center text-slate-400 text-xs font-medium">
                        <i class="fas fa-spinner fa-spin text-base text-indigo-600 mb-1 block"></i>
                        <span>Memuat data pengguna...</span>
                    </div>
                    <div id="usersEmptyState" class="hidden py-8 text-center text-slate-400 text-xs">
                        <i class="fas fa-users-slash text-2xl text-slate-300 mb-2 block"></i>
                        <span>Belum ada akun tim entri terdaftar.</span>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <!-- ============================================================== -->
            <!-- SECTION 6: RIWAYAT & LOG AKTIVITAS (AUDIT TRAIL CERDAS)        -->
            <!-- ============================================================== -->
            <section id="panelLogs" class="hidden space-y-3">
                <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-tight flex items-center gap-2">
                            <i class="fas fa-clock-rotate-left text-indigo-600"></i>
                            <span>Riwayat Aktivitas & Log Audit Sistem</span>
                        </h2>
                        <p class="text-[10px] text-slate-500 mt-0.5">
                            <?= $isAdmin ? 'Catatan rekam jejak lengkap (audit trail) aktivitas login, entri data, perbaikan, hapus data ATS, ekspor dokumen, dan manajemen pengguna se-Kabupaten.' : 'Catatan riwayat rekam jejak entri dan pembaruan data untuk desa/kelurahan wilayah tugas Anda.' ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="loadLogsData()" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold shadow-xs flex items-center gap-1.5 transition">
                            <i class="fas fa-arrows-rotate text-[10px]"></i>
                            <span>Segarkan Log</span>
                        </button>
                    </div>
                </div>

                <!-- Filter Log -->
                <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-2.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Aksi:</span>
                            <select id="logsFilterAction" onchange="loadLogsData()" class="compact-input font-semibold text-slate-700">
                                <option value="">Semua Aksi</option>
                                <option value="LOGIN">LOGIN</option>
                                <option value="LOGOUT">LOGOUT</option>
                                <option value="CREATE_DATA">ENTRI DATA (CREATE)</option>
                                <option value="UPDATE_DATA">UBAH DATA (UPDATE)</option>
                                <option value="DELETE_DATA">HAPUS DATA (DELETE)</option>
                                <option value="EXPORT_EXCEL">EKSPOR EXCEL</option>
                                <option value="PRINT_REPORT">CETAK LAPORAN</option>
                                <option value="CHANGE_PASSWORD">GANTI PASSWORD</option>
                                <option value="MANAGE_USER">MANAJEMEN PENGGUNA</option>
                            </select>
                        </div>
                    </div>
                    <div class="relative w-full sm:w-64">
                        <input type="text" id="logsSearchQuery" oninput="debounceFilterLogs()" placeholder="Cari NIK, Nama Anak, Petugas..." class="compact-input w-full pl-7 pr-3">
                        <i class="fas fa-search absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                    </div>
                </div>

                <!-- Logs Table -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="py-2.5 px-3 text-center w-12">No</th>
                                    <th class="py-2.5 px-3 w-36">Waktu</th>
                                    <th class="py-2.5 px-3 w-32">Aksi</th>
                                    <th class="py-2.5 px-3 w-44">Petugas</th>
                                    <th class="py-2.5 px-3">Deskripsi Aktivitas</th>
                                    <th class="py-2.5 px-3 w-48">Wilayah Terkait</th>
                                    <th class="py-2.5 px-3 text-right w-28">IP Address</th>
                                </tr>
                            </thead>
                            <tbody id="logsTableBody" class="divide-y divide-slate-100 text-xs text-slate-700">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                    <div id="logsLoading" class="py-8 text-center text-slate-400 text-xs font-medium">
                        <i class="fas fa-spinner fa-spin text-base text-indigo-600 mb-1 block"></i>
                        <span>Memuat riwayat log...</span>
                    </div>
                    <div id="logsEmptyState" class="hidden py-8 text-center text-slate-400 text-xs">
                        <i class="fas fa-clock text-2xl text-slate-300 mb-2 block"></i>
                        <span>Belum ada catatan aktivitas dalam kriteria ini.</span>
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
                    <p class="text-[9px] text-slate-500" id="photoModalSubtitle">PANTAU ATS - Pemantauan & Pemutakhiran Data</p>
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

    <?php if ($isAdmin): ?>
    <!-- ==================== MODAL TAMBAH / EDIT USER (ADMIN ONLY) ==================== -->
    <div id="userModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden max-h-[92vh] flex flex-col">
            <!-- Modal Header -->
            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 text-slate-800 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs shadow-xs">
                        <i class="fas fa-user-gear"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900" id="userModalTitle">Tambah Akun Pengguna</h4>
                        <p class="text-[9px] text-slate-500 font-medium">Pengaturan Hak Akses & Pembagian Kecamatan</p>
                    </div>
                </div>
                <button type="button" onclick="closeUserModal()" class="w-6 h-6 rounded-lg hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center text-xs transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Form -->
            <form id="userForm" onsubmit="handleUserSubmit(event)" class="p-4 space-y-3 overflow-y-auto flex-1">
                <?= csrf_field() ?>
                <input type="hidden" id="modalUserId" name="id" value="">

                <!-- Alert Box -->
                <div id="userModalAlert" class="hidden p-2.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-[10px] flex items-center gap-1.5">
                    <i class="fas fa-circle-exclamation text-xs text-red-500 flex-shrink-0"></i>
                    <span id="userModalAlertText"></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Username -->
                    <div>
                        <label for="modalUsername" class="compact-label">Username Login <span class="text-red-500">*</span></label>
                        <input type="text" id="modalUsername" name="username" required placeholder="misal: tim_polewali" class="compact-input w-full font-mono font-semibold lowercase">
                    </div>
                    <!-- Role -->
                    <div>
                        <label for="modalRole" class="compact-label">Tingkat Akses (Role) <span class="text-red-500">*</span></label>
                        <select id="modalRole" name="role" required onchange="handleModalRoleChange()" class="compact-input w-full font-semibold">
                            <option value="USER">Level 2: Tim Entri (Berdasarkan Kecamatan)</option>
                            <option value="ADMIN">Level 1: Administrator (Akses Penuh)</option>
                        </select>
                    </div>
                </div>

                <!-- Nama Petugas -->
                <div>
                    <label for="modalNama" class="compact-label">Nama Petugas / Tim <span class="text-red-500">*</span></label>
                    <input type="text" id="modalNama" name="nama" required placeholder="Contoh: Petugas Entri Kec. Campalagian" class="compact-input w-full font-semibold">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- No HP -->
                    <div>
                        <label for="modalHp" class="compact-label">Nomor WhatsApp / HP</label>
                        <input type="text" id="modalHp" name="hp" placeholder="08xxxxxxxxxx" class="compact-input w-full font-mono">
                    </div>
                    <!-- Password -->
                    <div>
                        <label for="modalPassword" class="compact-label">Password <span id="passReqNotice" class="text-red-500">*</span></label>
                        <input type="password" id="modalPassword" name="password" placeholder="Min. 4 karakter" class="compact-input w-full">
                        <span class="text-[9px] text-slate-400 block mt-0.5" id="passHelpText">Password awal akun.</span>
                    </div>
                </div>

                <!-- Wilayah Penugasan Tim Entri: Kecamatan & Desa/Kelurahan -->
                <div id="containerWilayahAkses" class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                    <div>
                        <label for="modalKecamatanSelect" class="compact-label font-bold text-slate-800">
                            <i class="fas fa-map-location-dot text-indigo-600 mr-1"></i> Pilih Kecamatan Tugas <span class="text-red-500">*</span>
                        </label>
                        <select id="modalKecamatanSelect" name="kecamatan_akses" onchange="handleModalKecamatanChange()" class="compact-input w-full font-semibold text-slate-800">
                            <option value="">-- PILIH KECAMATAN --</option>
                            <?php foreach ($allKecamatanList as $kec): ?>
                                <option value="<?= esc($kec) ?>"><?= esc(strtoupper($kec)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="text-[9px] text-slate-500 block mt-0.5">Pilih kecamatan terlebih dahulu untuk menampilkan daftar desa/kelurahan.</span>
                    </div>

                    <!-- Desa / Kelurahan Checkboxes -->
                    <div id="containerDesaAkses" class="hidden space-y-2 pt-2 border-t border-slate-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-slate-800 uppercase tracking-wider block">
                                    <i class="fas fa-map-pin text-emerald-600 mr-1"></i> Desa / Kelurahan Tugas <span class="text-red-500">*</span>
                                </span>
                                <span class="text-[9px] text-slate-500">Pilih satu atau beberapa desa wewenang tim entri ini</span>
                            </div>
                            <button type="button" onclick="toggleSelectAllModalDesa()" id="btnToggleAllDesa" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 px-2 py-0.5 rounded border border-indigo-200 bg-white shadow-2xs">
                                Pilih Semua Desa
                            </button>
                        </div>
                        <div id="modalDesaGrid" class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 max-h-48 overflow-y-auto pr-1">
                            <!-- Populated dynamically when Kecamatan selected -->
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 flex-shrink-0">
                    <button type="button" onclick="closeUserModal()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitUser" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                        <span id="btnTextUser">Simpan Akun</span>
                        <i id="btnSpinnerUser" class="fas fa-spinner fa-spin hidden"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- ==================== SCRIPT ==================== -->
    <script>
        const BASE_URL = (function() {
            let base = '<?= rtrim(base_url(), "/") ?>/';
            try {
                if (base.startsWith('http://') || base.startsWith('https://')) {
                    const u = new URL(base);
                    return window.location.origin + u.pathname + (u.pathname.endsWith('/') ? '' : '/');
                }
            } catch (e) {
                console.warn("BASE_URL parsing fallback:", e);
            }
            return base;
        })();
        let currentUser = <?= json_encode($currentUser) ?>;
        let wilayahKecamatan = null;
        let globalData = [];
        let globalUsersList = [];
        let chartsObj = {};
        let debounceTimer = null;

        // WebGIS State & 16 Kecamatan Coordinates
        const KECAMATAN_COORDS = {
            'POLEWALI':    { lat: -3.4326, lng: 119.3435 },
            'WONOMULYO':   { lat: -3.4712, lng: 119.2415 },
            'CAMPALAGIAN': { lat: -3.4685, lng: 119.2081 },
            'TINAMBUNG':   { lat: -3.5125, lng: 119.0512 },
            'BALANIPA':    { lat: -3.4981, lng: 119.1235 },
            'MAPILLI':     { lat: -3.4215, lng: 119.2781 },
            'MATAKALI':    { lat: -3.4412, lng: 119.3125 },
            'BINUANG':     { lat: -3.4682, lng: 119.4125 },
            'ANREAPI':     { lat: -3.3852, lng: 119.3621 },
            'TAPANGO':     { lat: -3.3512, lng: 119.2512 },
            'LUYO':        { lat: -3.3912, lng: 119.2135 },
            'LIMBORO':     { lat: -3.4512, lng: 119.0832 },
            'ALLU':        { lat: -3.3768, lng: 119.0435 },
            'BULO':        { lat: -3.2104, lng: 119.2312 },
            'MATANGNGA':   { lat: -3.1625, lng: 119.1425 },
            'TUTAR':       { lat: -3.2512, lng: 119.0125 },
        };
        const BASEMAP_TILES = {
            'osm':       'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            'topo':      'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
            'street':    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}',
            'satellite': 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'
        };
        let gisMap = null;
        let gisTileLayer = null;
        let gisMarkersLayer = null;
        let globalGeoStats = {};
        let selectedGisKec = 'POLEWALI';

        document.addEventListener('DOMContentLoaded', async () => {
            try { await loadWilayahData(); } catch (e) { console.error('Error loadWilayahData:', e); }
            try { await loadTableData(); } catch (e) { console.error('Error loadTableData:', e); }
            try { await loadAnalyticsData(); } catch (e) { console.error('Error loadAnalyticsData:', e); }
            try { applySchoolLogic(); } catch (e) { console.error('Error applySchoolLogic:', e); }
            try { updateExportLinks(); } catch (e) { console.error('Error updateExportLinks:', e); }

            // Auto-select kecamatan & desa if user is restricted to a single desa / single kecamatan
            const kecSelect = document.getElementById('kecamatan');
            const desaSelect = document.getElementById('desa');
            if (currentUser.role !== 'ADMIN') {
                if (currentUser.isSingleDesa) {
                    if (kecSelect && currentUser.singleKecName) {
                        kecSelect.value = currentUser.singleKecName;
                        handleKecamatanChange();
                    }
                    if (desaSelect && currentUser.singleDesaName) {
                        desaSelect.value = currentUser.singleDesaName;
                    }
                } else if (currentUser.allowedKecamatan && currentUser.allowedKecamatan.length === 1) {
                    if (kecSelect) {
                        kecSelect.value = currentUser.allowedKecamatan[0];
                        handleKecamatanChange();
                    }
                }
            }
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
            const panels = ['panelDashboard', 'panelMap', 'panelData', 'panelForm', 'panelExport', 'panelUsers', 'panelLogs'];
            panels.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.add('hidden');
            });

            const navs = ['navItemDashboard', 'navItemMap', 'navItemData', 'navItemForm', 'navItemExport', 'navItemUsers', 'navItemLogs'];
            navs.forEach(id => {
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
                try { loadAnalyticsData(); } catch(e) { console.error(e); }
            } else if (tab === 'map') {
                const panel = document.getElementById('panelMap');
                if (panel) {
                    panel.classList.remove('hidden');
                    const nav = document.getElementById('navItemMap');
                    if (nav) nav.className = 'sidebar-item-active w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all';
                    breadcrumb.innerText = 'Peta Sebaran WebGIS Polman';
                    try { initOrUpdateGisMap(); } catch(e) { console.error('Error initOrUpdateGisMap:', e); }
                }
            } else if (tab === 'data') {
                document.getElementById('panelData').classList.remove('hidden');
                document.getElementById('navItemData').className = 'sidebar-item-active w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all';
                breadcrumb.innerText = 'Data Tersimpan';
                try { loadTableData(); } catch(e) { console.error(e); }
            } else if (tab === 'form') {
                document.getElementById('panelForm').classList.remove('hidden');
                document.getElementById('navItemForm').className = 'sidebar-item-active w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all';
                breadcrumb.innerText = document.getElementById('isEditMode').value === 'true' ? 'Edit Data ATS' : 'Entri Data Baru';
            } else if (tab === 'export') {
                document.getElementById('panelExport').classList.remove('hidden');
                document.getElementById('navItemExport').className = 'sidebar-item-active w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all';
                breadcrumb.innerText = 'Pusat Export Excel';
                try { updateExportLinks(); } catch(e) { console.error(e); }
            } else if (tab === 'users') {
                const panel = document.getElementById('panelUsers');
                if (panel) {
                    panel.classList.remove('hidden');
                    const nav = document.getElementById('navItemUsers');
                    if (nav) nav.className = 'sidebar-item-active w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all';
                    breadcrumb.innerText = 'Manajemen Pengguna';
                    try { loadUsersData(); } catch(e) { console.error(e); }
                }
            } else if (tab === 'logs') {
                const panel = document.getElementById('panelLogs');
                if (panel) {
                    panel.classList.remove('hidden');
                    const nav = document.getElementById('navItemLogs');
                    if (nav) nav.className = 'sidebar-item-active w-full px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-all';
                    breadcrumb.innerText = 'Riwayat & Log Aktivitas';
                    try { loadLogsData(); } catch(e) { console.error(e); }
                }
            }

            if (window.innerWidth < 1024) {
                document.getElementById('adminSidebar').classList.add('-translate-x-full');
                document.getElementById('sidebarBackdrop').classList.add('hidden');
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function handleExportKecChange() {
            const kecSelect = document.getElementById('exportFilterKecamatan');
            const desaSelect = document.getElementById('exportFilterDesa');
            if (!desaSelect) return;

            const kec = kecSelect ? kecSelect.value : 'Semua';
            desaSelect.innerHTML = '<option value="Semua">Semua Desa/Kelurahan</option>';

            if (kec && kec !== 'Semua' && wilayahKecamatan && wilayahKecamatan[kec]) {
                const villages = wilayahKecamatan[kec];
                villages.forEach(d => {
                    if (currentUser.role === 'ADMIN' || !currentUser.allowedDesa || currentUser.allowedDesa.length === 0 || currentUser.allowedDesa.map(x => x.toUpperCase()).includes(d.toUpperCase())) {
                        desaSelect.innerHTML += `<option value="${escapeHtml(d)}">${escapeHtml(d)}</option>`;
                    }
                });
            }
            updateExportLinks();
        }

        function updateExportLinks() {
            const kecSelect = document.getElementById('exportFilterKecamatan');
            const desaSelect = document.getElementById('exportFilterDesa');
            const kec = kecSelect ? kecSelect.value : 'Semua';
            const desa = desaSelect ? desaSelect.value : 'Semua';
            
            document.querySelectorAll('.export-btn-link').forEach(a => {
                const base = a.getAttribute('data-base');
                a.href = `${base}&kecamatan=${encodeURIComponent(kec)}&desa=${encodeURIComponent(desa)}`;
            });

            const btnReport = document.getElementById('btnCetakReportLink');
            if (btnReport) {
                const base = btnReport.getAttribute('data-base');
                btnReport.href = `${base}?kecamatan=${encodeURIComponent(kec)}&desa=${encodeURIComponent(desa)}`;
            }
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

            if (statusSekolah === 'YA, MASIH SEKOLAH') {
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
            const bajuReqMark = document.getElementById('bajuReqMark');
            const sepatuReqMark = document.getElementById('sepatuReqMark');
            const bajuNotice = document.getElementById('bajuNotice');
            const sepatuNotice = document.getElementById('sepatuNotice');

            if (bentuk === 'FORMAL') {
                // FORMAL: Baju & Sepatu WAJIB
                baju.disabled = false;
                baju.required = true;
                if (baju.value === '-') baju.value = '';
                if (bajuReqMark) bajuReqMark.classList.remove('hidden');
                if (bajuNotice) {
                    bajuNotice.innerText = 'Wajib untuk jalur Formal';
                    bajuNotice.className = 'text-[9px] text-amber-600 font-semibold block mt-0.5';
                    bajuNotice.classList.remove('hidden');
                }

                sepatu.disabled = false;
                sepatu.required = true;
                sepatu.placeholder = 'Wajib diisi (misal: 38)';
                sepatu.classList.remove('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
                if (sepatuReqMark) sepatuReqMark.classList.remove('hidden');
                if (sepatuNotice) {
                    sepatuNotice.innerText = 'Wajib untuk jalur Formal';
                    sepatuNotice.className = 'text-[9px] text-amber-600 font-semibold block mt-0.5';
                    sepatuNotice.classList.remove('hidden');
                }
            } else if (bentuk === 'NON FORMAL') {
                // NON FORMAL: Tidak Perlu Ukuran Sepatu, Baju Opsional
                baju.disabled = false;
                baju.required = false;
                if (bajuReqMark) bajuReqMark.classList.add('hidden');
                if (bajuNotice) {
                    bajuNotice.innerText = 'Opsional untuk Non-Formal';
                    bajuNotice.className = 'text-[9px] text-slate-500 block mt-0.5';
                    bajuNotice.classList.remove('hidden');
                }

                sepatu.disabled = true;
                sepatu.required = false;
                sepatu.value = '';
                sepatu.placeholder = 'Tidak Perlu (Non-Formal)';
                sepatu.classList.add('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
                if (sepatuReqMark) sepatuReqMark.classList.add('hidden');
                if (sepatuNotice) {
                    sepatuNotice.innerText = 'Tidak perlu ukuran sepatu untuk jalur Non-Formal';
                    sepatuNotice.className = 'text-[9px] text-slate-400 italic block mt-0.5';
                    sepatuNotice.classList.remove('hidden');
                }
            } else {
                baju.disabled = true;
                baju.required = false;
                baju.value = '-';
                if (bajuReqMark) bajuReqMark.classList.add('hidden');
                if (bajuNotice) bajuNotice.classList.add('hidden');

                sepatu.disabled = true;
                sepatu.required = false;
                sepatu.value = '';
                sepatu.placeholder = 'Contoh: 38';
                sepatu.classList.remove('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
                if (sepatuReqMark) sepatuReqMark.classList.add('hidden');
                if (sepatuNotice) sepatuNotice.classList.add('hidden');
            }
        }

        function handleAlasanChange() {
            const alasan = document.getElementById('alasan').value;
            const contLainnya = document.getElementById('containerAlasanLainnya');
            const inputLainnya = document.getElementById('alasanLainnya');

            if (alasan.toUpperCase().includes('K_13') || alasan.toUpperCase().includes('LAINNYA')) {
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

            const statusSekolah = document.getElementById('statusSekolah').value;
            const keinginan = document.getElementById('keinginan').value;
            const bentukPendidikan = document.getElementById('bentukPendidikan').value;
            const baju = document.getElementById('baju');
            const sepatu = document.getElementById('sepatu');

            if (statusSekolah === 'TIDAK (ATS)' && keinginan === 'INGIN KEMBALI BERSEKOLAH') {
                if (bentukPendidikan === 'FORMAL') {
                    if (!baju.value || baju.value === '-') {
                        showToast("Gagal: Untuk jalur Formal, Ukuran Baju Seragam wajib dipilih!", "error");
                        baju.focus();
                        return;
                    }
                    if (!sepatu.value.trim() || isNaN(parseInt(sepatu.value))) {
                        showToast("Gagal: Untuk jalur Formal, Ukuran Sepatu wajib diisi!", "error");
                        sepatu.focus();
                        return;
                    }
                }
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

        function handleTargetKembaliChange() {
            const tk = document.getElementById('targetKembali').value;
            const bp = document.getElementById('bentukPendidikan');
            if (!bp) return;

            if (['SD', 'SMP', 'SMA'].includes(tk)) {
                bp.value = 'FORMAL';
            } else if (['PAKET A', 'PAKET B', 'PAKET C', 'SKB'].includes(tk)) {
                bp.value = 'NON FORMAL';
            }
            handleBentukChange();
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
            document.getElementById('formHeaderTitle').innerHTML = '<i class="fas fa-edit text-indigo-600"></i><span>Formulir PANTAU ATS - Pemantauan dan Pemutakhiran Data</span>';
            document.getElementById('nikCounter').innerText = "0/16";
            document.getElementById('targetKembali').value = '';
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
            if (refreshIcon) refreshIcon.classList.add('fa-spin');

            const kec = document.getElementById('tableFilterKecamatan')?.value || 'Semua';
            const desa = document.getElementById('tableFilterDesa')?.value || 'Semua';
            const query = document.getElementById('tableSearchInput')?.value.trim() || '';

            let url = `${BASE_URL}ats/getData?kecamatan=${encodeURIComponent(kec)}&desa=${encodeURIComponent(desa)}&q=${encodeURIComponent(query)}`;

            try {
                const response = await fetch(url);
                const result = await response.json();

                if (result.status === 'success') {
                    globalData = result.data;
                    filterLocalTable();
                }
            } catch (err) {
                console.error("Gagal memuat tabel:", err);
            } finally {
                if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            }
        }

        function debounceFilterTable() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => { loadTableData(); }, 300);
        }

        function filterLocalTable() {
            const targetFilter = document.getElementById('tableFilterTarget')?.value || '';
            if (!targetFilter) {
                renderTable(globalData);
                return;
            }
            const filtered = globalData.filter(row => {
                const tk = (row.target_kembali || '').toUpperCase();
                return tk === targetFilter.toUpperCase();
            });
            renderTable(filtered);
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

                let targetKembaliBadge = '<span class="text-slate-400 text-[10px]">-</span>';
                const tk = (row.target_kembali || '').toUpperCase();
                if (tk === 'SD') {
                    targetKembaliBadge = `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-sky-50 text-sky-700 border border-sky-200"><i class="fas fa-child text-[8.5px] mr-0.5"></i> SD</span>`;
                } else if (tk === 'SMP') {
                    targetKembaliBadge = `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200"><i class="fas fa-user-graduate text-[8.5px] mr-0.5"></i> SMP</span>`;
                } else if (tk === 'SMA' || tk === 'SMK') {
                    targetKembaliBadge = `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-amber-50 text-amber-700 border border-amber-200"><i class="fas fa-building-columns text-[8.5px] mr-0.5"></i> SMA</span>`;
                } else if (tk === 'PAKET A') {
                    targetKembaliBadge = `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fas fa-book-open text-[8.5px] mr-0.5"></i> Paket A</span>`;
                } else if (tk === 'PAKET B') {
                    targetKembaliBadge = `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-teal-50 text-teal-700 border border-teal-200"><i class="fas fa-book-bookmark text-[8.5px] mr-0.5"></i> Paket B</span>`;
                } else if (tk === 'PAKET C') {
                    targetKembaliBadge = `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-purple-50 text-purple-700 border border-purple-200"><i class="fas fa-certificate text-[8.5px] mr-0.5"></i> Paket C</span>`;
                } else if (tk === 'SKB') {
                    targetKembaliBadge = `<span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-orange-50 text-orange-700 border border-orange-200"><i class="fas fa-landmark text-[8.5px] mr-0.5"></i> SKB</span>`;
                }

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
                        <td class="py-2 px-3 text-center whitespace-nowrap">${targetKembaliBadge}</td>
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

                let stVal = d.status_sekolah || '';
                if (stVal.includes('TAMAT SMA')) {
                    stVal = 'TIDAK (ATS)';
                }
                document.getElementById('statusSekolah').value = stVal;
                document.getElementById('sekolahAktif').value = d.sekolah_aktif === '-' ? '' : (d.sekolah_aktif || '');

                document.getElementById('pendidikanTertinggi').value = d.pendidikan_tertinggi === '-' ? '' : (d.pendidikan_tertinggi || '');
                applySchoolLogic();

                document.getElementById('asalSekolah').value = d.asal_sekolah === '-' ? '' : (d.asal_sekolah || '');
                document.getElementById('statusTdkSekolah').value = d.status_tdk_sekolah === '-' ? '' : (d.status_tdk_sekolah || '');
                const alasanSelect = document.getElementById('alasan');
                if (d.alasan && d.alasan !== '-') {
                    const dVal = d.alasan.toUpperCase().trim();
                    const cleanD = dVal.replace(/^K_\d+\s*/i, '');
                    let matchedAlasan = false;
                    for (let opt of alasanSelect.options) {
                        if (!opt.value) continue;
                        const optVal = opt.value.toUpperCase().trim();
                        if (optVal === dVal || optVal === cleanD || optVal.includes(cleanD) || cleanD.includes(optVal)) {
                            alasanSelect.value = opt.value;
                            matchedAlasan = true;
                            break;
                        }
                    }
                    if (!matchedAlasan) {
                        const newOpt = document.createElement('option');
                        newOpt.value = d.alasan;
                        newOpt.text = d.alasan;
                        newOpt.selected = true;
                        alasanSelect.appendChild(newOpt);
                    }
                } else {
                    alasanSelect.value = '';
                }
                handleAlasanChange();
                document.getElementById('alasanLainnya').value = d.alasan_lainnya || '';
                document.getElementById('penerimaKip').value = d.menerima_kip === '-' ? '' : (d.menerima_kip || '');

                document.getElementById('keinginan').value = d.keinginan === '-' ? '' : (d.keinginan || '');
                applySchoolLogic();

                document.getElementById('targetKembali').value = d.target_kembali && d.target_kembali !== '-' ? d.target_kembali : '';
                document.getElementById('bentukPendidikan').value = d.bentuk_pendidikan === '-' ? '' : (d.bentuk_pendidikan || '');
                handleBentukChange();
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

        // 13. Analytics Charts (Filtered by Kecamatan & Desa)
        async function loadAnalyticsData() {
            const kec = document.getElementById('analyticsFilterKec')?.value || 'Semua';
            const desa = document.getElementById('analyticsFilterDesa')?.value || 'Semua';
            const refreshIcon = document.getElementById('analyticsRefreshIcon');
            if (refreshIcon) refreshIcon.classList.add('fa-spin');

            try {
                const res = await fetch(`${BASE_URL}ats/analyticsData?kecamatan=${encodeURIComponent(kec)}&desa=${encodeURIComponent(desa)}`);
                const json = await res.json();

                if (json.status !== 'success') return;

                const { summary, charts, geoStats } = json;
                if (geoStats) {
                    globalGeoStats = geoStats;
                    if (gisMap) {
                        renderGisMarkers();
                        renderGisRanking();
                        selectGisKecamatan(selectedGisKec);
                    }
                }

                document.getElementById('statTotalData').innerText = summary.totalData;
                document.getElementById('statGender').innerText = `${summary.male} / ${summary.female}`;
                document.getElementById('statAvgAge').innerText = summary.avgAge;
                document.getElementById('statTopIjazah').innerText = summary.topIjazah;
                document.getElementById('statTopIjazah').title = summary.topIjazah;

                document.getElementById('statTotalAts').innerText = summary.totalAts;
                document.getElementById('statTotalSekolah').innerText = summary.totalSekolah;
                document.getElementById('statTotalDisabilitas').innerText = summary.disabilitas;
                document.getElementById('statTotalKip').innerText = summary.totalKip;

                if (summary.targetKembali) {
                    const tk = summary.targetKembali;
                    const elTotal = document.getElementById('statTotalInginKembali');
                    if (elTotal) elTotal.innerText = tk.totalInginKembali;
                    const elSD = document.getElementById('statTargetSD');
                    if (elSD) elSD.innerText = tk.sd;
                    const elSMP = document.getElementById('statTargetSMP');
                    if (elSMP) elSMP.innerText = tk.smp;
                    const elSMA = document.getElementById('statTargetSMA');
                    if (elSMA) elSMA.innerText = tk.sma;
                    const elPaketA = document.getElementById('statTargetPaketA');
                    if (elPaketA) elPaketA.innerText = tk.paketA;
                    const elPaketB = document.getElementById('statTargetPaketB');
                    if (elPaketB) elPaketB.innerText = tk.paketB;
                    const elPaketC = document.getElementById('statTargetPaketC');
                    if (elPaketC) elPaketC.innerText = tk.paketC;
                    const elSKB = document.getElementById('statTargetSKB');
                    if (elSKB) elSKB.innerText = tk.skb;
                }

                renderAnalyticsCharts(charts);
            } catch (err) {
                console.error("Gagal analitik:", err);
            } finally {
                if (refreshIcon) refreshIcon.classList.remove('fa-spin');
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

            // Chart: Target Program Kembali
            const targetCanvas = document.getElementById('chartTargetKembali');
            if (targetCanvas && charts.targetKembali) {
                chartsObj.cTargetKembali = new Chart(targetCanvas, {
                    type: 'bar',
                    data: {
                        labels: charts.targetKembali.labels,
                        datasets: [{
                            label: 'Jumlah Anak',
                            data: charts.targetKembali.values,
                            backgroundColor: [
                                '#38bdf8', // SD (Sky)
                                '#6366f1', // SMP (Indigo)
                                '#f59e0b', // SMA (Amber)
                                '#10b981', // Paket A (Emerald)
                                '#14b8a6', // Paket B (Teal)
                                '#a855f7', // Paket C (Purple)
                                '#ea580c', // SKB (Orange)
                            ],
                            borderRadius: 4,
                        }]
                    },
                    options: defaultOpts
                });
            }

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

        // ==================== WEBGIS CONTROLLERS & LOGIC ====================
        function initOrUpdateGisMap() {
            if (typeof L === 'undefined') {
                console.error('Leaflet.js is not loaded yet');
                return;
            }

            const mapContainer = document.getElementById('atsGisMap');
            if (!mapContainer) return;

            if (!gisMap) {
                // Initialize Leaflet Map centered on Kabupaten Polewali Mandar
                gisMap = L.map('atsGisMap', {
                    zoomControl: true,
                    attributionControl: true
                }).setView([-3.41, 119.22], 10);

                // Default Basemap (OpenStreetMap Standard - 100% Free, No Watermark, No API Key)
                gisTileLayer = L.tileLayer(BASEMAP_TILES['osm'], {
                    maxZoom: 18,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors'
                }).addTo(gisMap);

                gisMarkersLayer = L.layerGroup().addTo(gisMap);
            }

            // Recalculate dimensions for hidden/shown tabs
            setTimeout(() => {
                if (gisMap) {
                    gisMap.invalidateSize();
                }
            }, 150);

            // Render markers and ranking
            renderGisMarkers();
            renderGisRanking();
            selectGisKecamatan(selectedGisKec || 'POLEWALI');
        }

        function changeGisBasemap() {
            const selectEl = document.getElementById('gisBasemapSelect');
            const type = selectEl ? selectEl.value : 'osm';
            if (!gisMap) return;

            if (gisTileLayer) {
                gisMap.removeLayer(gisTileLayer);
            }

            let attr = '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors';
            if (type === 'satellite') {
                attr = 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community';
            } else if (type === 'topo' || type === 'street') {
                attr = 'Tiles &copy; Esri &mdash; Sources: Esri, HERE, Garmin, Intermap, increment P Corp., GEBCO, USGS, FAO, NPS, NRCAN, GeoBase, IGN, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), (c) OpenStreetMap contributors, and the GIS User Community';
            }

            gisTileLayer = L.tileLayer(BASEMAP_TILES[type] || BASEMAP_TILES['osm'], {
                maxZoom: 18,
                attribution: attr
            }).addTo(gisMap);
        }

        function resetGisMapView() {
            if (gisMap) {
                gisMap.flyTo([-3.41, 119.22], 10, { duration: 1.2 });
            }
        }

        function handleGisKecJump() {
            const selectEl = document.getElementById('gisJumpKecamatan');
            if (!selectEl) return;
            const kec = selectEl.value;
            if (!kec) return;

            selectedGisKec = kec;
            selectGisKecamatan(kec);

            if (gisMap && KECAMATAN_COORDS[kec]) {
                const c = KECAMATAN_COORDS[kec];
                gisMap.flyTo([c.lat, c.lng], 12, { duration: 1.2 });
            }
        }

        function applyGisFilter() {
            renderGisMarkers();
            if (selectedGisKec) {
                selectGisKecamatan(selectedGisKec);
            }
        }

        function renderGisMarkers() {
            if (!gisMap || !gisMarkersLayer) return;

            gisMarkersLayer.clearLayers();

            const targetFilterEl = document.getElementById('gisFilterTarget');
            const targetFilter = targetFilterEl ? targetFilterEl.value : 'Semua';

            Object.keys(KECAMATAN_COORDS).forEach(kec => {
                const coords = KECAMATAN_COORDS[kec];
                const stats = globalGeoStats[kec] || {
                    name: kec,
                    total: 0,
                    ats: 0,
                    sekolah: 0,
                    sd: 0,
                    smp: 0,
                    sma: 0,
                    paketA: 0,
                    paketB: 0,
                    paketC: 0,
                    skb: 0,
                    tidakIngin: 0,
                    topAlasan: '-'
                };

                let displayCount = stats.ats;
                let filterLabel = 'ATS Total';
                if (targetFilter === 'SD') { displayCount = stats.sd; filterLabel = 'Target SD'; }
                else if (targetFilter === 'SMP') { displayCount = stats.smp; filterLabel = 'Target SMP'; }
                else if (targetFilter === 'SMA') { displayCount = stats.sma; filterLabel = 'Target SMA'; }
                else if (targetFilter === 'PAKET A') { displayCount = stats.paketA; filterLabel = 'Paket A'; }
                else if (targetFilter === 'PAKET B') { displayCount = stats.paketB; filterLabel = 'Paket B'; }
                else if (targetFilter === 'PAKET C') { displayCount = stats.paketC; filterLabel = 'Paket C'; }
                else if (targetFilter === 'SKB') { displayCount = stats.skb; filterLabel = 'Target SKB'; }

                let markerBg = 'bg-emerald-500';
                let hasPulse = false;
                let statusBadge = '<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Rendah</span>';

                if (stats.ats > 20) {
                    markerBg = 'bg-rose-600';
                    hasPulse = true;
                    statusBadge = '<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-100 text-rose-800">Prioritas</span>';
                } else if (stats.ats > 5) {
                    markerBg = 'bg-amber-500';
                    statusBadge = '<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">Sedang</span>';
                }

                const markerHtml = `
                    <div class="gis-pulse-marker ${markerBg}" style="width: 36px; height: 36px; font-size: 11px;">
                        ${hasPulse ? '<span class="gis-radar-ring bg-rose-500"></span>' : ''}
                        <span>${displayCount}</span>
                    </div>
                `;

                const customIcon = L.divIcon({
                    className: 'gis-custom-icon',
                    html: markerHtml,
                    iconSize: [36, 36],
                    iconAnchor: [18, 18],
                    popupAnchor: [0, -18]
                });

                const marker = L.marker([coords.lat, coords.lng], { icon: customIcon });

                const popupHtml = `
                    <div style="min-width: 210px; font-family: inherit; font-size: 11px;" class="p-1 space-y-2">
                        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px;">
                            <strong style="font-size: 12px; color: #0f172a; text-transform: uppercase;">Kec. ${kec}</strong>
                            ${statusBadge}
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px; text-align: center; margin: 4px 0;">
                            <div style="background: #fef2f2; padding: 4px; border-radius: 6px; border: 1px solid #fee2e2;">
                                <span style="font-size: 9px; color: #dc2626; font-weight: bold; display: block;">TOTAL ATS</span>
                                <strong style="font-size: 13px; color: #991b1b;">${stats.ats}</strong>
                            </div>
                            <div style="background: #ecfdf5; padding: 4px; border-radius: 6px; border: 1px solid #d1fae5;">
                                <span style="font-size: 9px; color: #059669; font-weight: bold; display: block;">MASIH SEKOLAH</span>
                                <strong style="font-size: 13px; color: #065f46;">${stats.sekolah}</strong>
                            </div>
                        </div>
                        <div style="font-size: 9.5px; color: #475569; line-height: 1.4;">
                            <div><b>SD:</b> ${stats.sd} | <b>SMP:</b> ${stats.smp} | <b>SMA:</b> ${stats.sma}</div>
                            <div><b>Paket A:</b> ${stats.paketA} | <b>B:</b> ${stats.paketB} | <b>C:</b> ${stats.paketC} | <b>SKB:</b> ${stats.skb}</div>
                            <div style="margin-top: 4px; color: #334155; font-size: 9px;"><b>Alasan Dominan:</b> ${stats.topAlasan || '-'}</div>
                        </div>
                        <button type="button" onclick="selectGisKecamatan('${kec}'); filterTableFromGis();" style="width: 100%; margin-top: 4px; padding: 5px; background: #4f46e5; color: #ffffff; border: none; border-radius: 6px; font-weight: 700; font-size: 10px; cursor: pointer; transition: background 0.2s;">
                            Lihat Data Anak Kecamatan Ini &rarr;
                        </button>
                    </div>
                `;

                marker.bindPopup(popupHtml);
                marker.on('click', () => {
                    selectGisKecamatan(kec);
                });

                gisMarkersLayer.addLayer(marker);
            });
        }

        function selectGisKecamatan(kec) {
            if (!kec) return;
            selectedGisKec = kec;

            const nameEl = document.getElementById('gisCardKecName');
            if (nameEl) nameEl.innerText = 'Kec. ' + kec;

            const jumpSelect = document.getElementById('gisJumpKecamatan');
            if (jumpSelect && jumpSelect.value !== kec) {
                jumpSelect.value = kec;
            }

            const stats = globalGeoStats[kec] || {
                name: kec,
                total: 0,
                ats: 0,
                sekolah: 0,
                sd: 0,
                smp: 0,
                sma: 0,
                paketA: 0,
                paketB: 0,
                paketC: 0,
                skb: 0,
                tidakIngin: 0,
                topAlasan: '-'
            };

            const badgeEl = document.getElementById('gisCardBadgeRisk');
            if (badgeEl) {
                if (stats.ats > 20) {
                    badgeEl.className = 'px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200';
                    badgeEl.innerText = 'Tinggi / Prioritas';
                } else if (stats.ats > 5) {
                    badgeEl.className = 'px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200';
                    badgeEl.innerText = 'Sedang';
                } else {
                    badgeEl.className = 'px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
                    badgeEl.innerText = 'Rendah';
                }
            }

            const setTxt = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.innerText = val;
            };

            setTxt('gisCardTotalAts', stats.ats);
            setTxt('gisCardTotalSekolah', stats.sekolah);
            setTxt('gisTargetSD', stats.sd);
            setTxt('gisTargetSMP', stats.smp);
            setTxt('gisTargetSMA', stats.sma);
            setTxt('gisTargetPaketA', stats.paketA);
            setTxt('gisTargetPaketB', stats.paketB);
            setTxt('gisTargetPaketC', stats.paketC);
            setTxt('gisTargetSKB', stats.skb);
            setTxt('gisCardTopAlasan', stats.topAlasan || '-');
        }

        function filterTableFromGis() {
            if (!selectedGisKec) return;
            navigateTab('data');

            const kecEl = document.getElementById('tableFilterKecamatan');
            if (kecEl) {
                kecEl.value = selectedGisKec;
                handleTableKecChange();
            }

            showToast('Menampilkan data anak di Kecamatan ' + selectedGisKec, 'info');
        }

        function renderGisRanking() {
            const listEl = document.getElementById('gisRankingList');
            if (!listEl) return;

            const entries = Object.entries(globalGeoStats);
            if (entries.length === 0) {
                listEl.innerHTML = '<p class="text-slate-400 text-[10px] italic">Belum ada data statistik.</p>';
                return;
            }

            entries.sort((a, b) => (b[1].ats || 0) - (a[1].ats || 0));
            const top5 = entries.slice(0, 5);

            let html = '';
            top5.forEach((item, idx) => {
                const kName = item[0];
                const kData = item[1];
                let badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                if ((kData.ats || 0) > 20) {
                    badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                } else if ((kData.ats || 0) > 5) {
                    badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                }

                html += `
                    <div onclick="selectGisKecamatan('${kName}'); if(KECAMATAN_COORDS['${kName}'] && gisMap) gisMap.flyTo([KECAMATAN_COORDS['${kName}'].lat, KECAMATAN_COORDS['${kName}'].lng], 12);"
                         class="flex items-center justify-between p-1.5 rounded-lg hover:bg-slate-50 cursor-pointer transition border border-transparent hover:border-slate-200 group">
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full bg-slate-100 group-hover:bg-teal-100 text-slate-600 group-hover:text-teal-800 text-[9px] font-bold flex items-center justify-center transition">${idx + 1}</span>
                            <span class="font-bold text-slate-700 text-[10px] uppercase">${kName}</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold border ${badgeClass}">${kData.ats || 0} ATS</span>
                    </div>
                `;
            });

            listEl.innerHTML = html;
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

        // ==================== USER MANAGEMENT (ADMIN ONLY) ====================
        async function loadUsersData() {
            const loading = document.getElementById('usersLoading');
            const empty = document.getElementById('usersEmptyState');
            const tbody = document.getElementById('usersTableBody');
            if (!tbody) return;

            loading.classList.remove('hidden');
            empty.classList.add('hidden');
            tbody.innerHTML = '';

            try {
                const res = await fetch(`${BASE_URL}ats/getUsers`);
                const json = await res.json();
                if (json.status === 'success') {
                    globalUsersList = json.data;
                    renderUsersTable(globalUsersList);
                } else {
                    showToast(json.message || "Gagal memuat pengguna", "error");
                }
            } catch (err) {
                showToast("Kesalahan jaringan saat memuat pengguna", "error");
            } finally {
                loading.classList.add('hidden');
            }
        }

        function renderUsersTable(users) {
            const tbody = document.getElementById('usersTableBody');
            const empty = document.getElementById('usersEmptyState');
            if (!tbody) return;

            tbody.innerHTML = '';
            if (!users || users.length === 0) {
                empty.classList.remove('hidden');
                return;
            }
            empty.classList.add('hidden');

            users.forEach((u, idx) => {
                const isAdminUser = (u.role || '').toUpperCase() === 'ADMIN';
                let roleBadge = isAdminUser
                    ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200"><i class="fas fa-shield-halved text-[9px]"></i> Level 1: Admin</span>`
                    : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fas fa-user-check text-[9px]"></i> Level 2: Tim Entri</span>`;

                let wilayahBadges = '';
                if (isAdminUser || (!u.kecamatan_akses && !u.desa_akses)) {
                    wilayahBadges = `<span class="text-[10px] font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100"><i class="fas fa-globe text-[9px] mr-1"></i> Seluruh 16 Kecamatan & Desa</span>`;
                } else {
                    const desaList = u.desa_akses ? u.desa_akses.split(',').map(s => s.trim()).filter(Boolean) : [];
                    wilayahBadges = `
                        <div class="space-y-1">
                            <div class="text-[10px] font-bold text-slate-800 flex items-center gap-1">
                                <i class="fas fa-map-location-dot text-indigo-600 text-[9px]"></i>
                                <span>Kec. ${escapeHtml(u.kecamatan_akses || '-')}</span>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                ${desaList.length > 0 ? desaList.map(d => `<span class="text-[9px] font-semibold bg-emerald-50 text-emerald-800 px-1.5 py-0.2 rounded border border-emerald-200">${escapeHtml(d)}</span>`).join('') : '<span class="text-[9px] text-slate-400 italic">Belum ada desa</span>'}
                            </div>
                        </div>
                    `;
                }

                const hp = u.hp ? `<span class="font-mono text-slate-700 text-xs">${escapeHtml(u.hp)}</span>` : `<span class="text-slate-400 text-xs">-</span>`;

                const tr = document.createElement('tr');
                tr.className = "hover:bg-slate-50/80 transition";
                tr.innerHTML = `
                    <td class="py-2.5 px-3 text-center font-bold text-slate-400 text-xs">${idx + 1}</td>
                    <td class="py-2.5 px-3">
                        <div class="font-bold text-slate-900 text-xs">${escapeHtml(u.nama)}</div>
                        <div class="text-[10px] font-mono text-slate-500">@${escapeHtml(u.username)}</div>
                    </td>
                    <td class="py-2.5 px-3">${roleBadge}</td>
                    <td class="py-2.5 px-3">${hp}</td>
                    <td class="py-2.5 px-3 max-w-sm">${wilayahBadges}</td>
                    <td class="py-2.5 px-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" onclick="openUserModal(${u.id})" class="px-2 py-1 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 font-semibold rounded text-[11px] transition" title="Edit Pengguna">
                                <i class="fas fa-edit"></i>
                            </button>
                            ${(currentUser.username !== u.username) ? `
                            <button type="button" onclick="deleteUserRecord(${u.id}, '${escapeHtml(u.username)}')" class="px-2 py-1 bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-700 font-semibold rounded text-[11px] transition" title="Hapus Pengguna">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            ` : ''}
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function handleModalKecamatanChange(preselectedDesas = []) {
            const kecSelect = document.getElementById('modalKecamatanSelect');
            const containerDesa = document.getElementById('containerDesaAkses');
            const grid = document.getElementById('modalDesaGrid');
            if (!containerDesa || !grid || !kecSelect) return;

            grid.innerHTML = '';
            const kec = kecSelect.value;
            if (!kec || !wilayahKecamatan || !wilayahKecamatan[kec]) {
                containerDesa.classList.add('hidden');
                return;
            }

            containerDesa.classList.remove('hidden');
            const villages = wilayahKecamatan[kec];
            const preselectedUpper = (Array.isArray(preselectedDesas) ? preselectedDesas : []).map(s => s.trim().toUpperCase());

            villages.forEach(d => {
                const isChecked = preselectedUpper.includes(d.toUpperCase());
                const label = document.createElement('label');
                label.className = "flex items-center gap-1.5 p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-emerald-50/50 cursor-pointer text-xs transition";
                label.innerHTML = `
                    <input type="checkbox" name="desa_akses[]" value="${escapeHtml(d)}" ${isChecked ? 'checked' : ''} class="desa-checkbox rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-3.5 w-3.5">
                    <span class="text-[11px] font-semibold text-slate-700">${escapeHtml(d)}</span>
                `;
                grid.appendChild(label);
            });
        }

        function toggleSelectAllModalDesa() {
            const checkboxes = document.querySelectorAll('.desa-checkbox');
            const allChecked = Array.from(checkboxes).length > 0 && Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => { cb.checked = !allChecked; });
            const btn = document.getElementById('btnToggleAllDesa');
            if (btn) btn.innerText = allChecked ? 'Pilih Semua Desa' : 'Batal Pilih Semua';
        }

        function openUserModal(userId = null) {
            const modal = document.getElementById('userModal');
            if (!modal) return;

            const form = document.getElementById('userForm');
            const alertBox = document.getElementById('userModalAlert');
            alertBox.classList.add('hidden');
            form.reset();

            const title = document.getElementById('userModalTitle');
            const passReq = document.getElementById('passReqNotice');
            const passHelp = document.getElementById('passHelpText');
            const passInput = document.getElementById('modalPassword');

            if (userId) {
                const user = globalUsersList.find(u => parseInt(u.id) === parseInt(userId));
                if (!user) return;

                title.innerText = `Edit Akun: ${user.username}`;
                document.getElementById('modalUserId').value = user.id;
                document.getElementById('modalUsername').value = user.username;
                document.getElementById('modalNama').value = user.nama;
                document.getElementById('modalHp').value = user.hp || '';
                document.getElementById('modalRole').value = (user.role || 'USER').toUpperCase();

                passReq.classList.add('hidden');
                passInput.required = false;
                passHelp.innerText = "Kosongkan jika tidak ingin mengubah password.";

                // Set kecamatan and populated desa checkboxes
                if (user.kecamatan_akses) {
                    document.getElementById('modalKecamatanSelect').value = user.kecamatan_akses;
                    const assignedDesa = user.desa_akses ? user.desa_akses.split(',') : [];
                    handleModalKecamatanChange(assignedDesa);
                } else {
                    document.getElementById('modalKecamatanSelect').value = '';
                    handleModalKecamatanChange();
                }
            } else {
                title.innerText = "Tambah Akun Pengguna Baru";
                document.getElementById('modalUserId').value = '';
                document.getElementById('modalRole').value = 'USER';
                document.getElementById('modalKecamatanSelect').value = '';
                handleModalKecamatanChange();
                passReq.classList.remove('hidden');
                passInput.required = true;
                passHelp.innerText = "Password awal untuk login (min. 4 karakter).";
            }

            handleModalRoleChange();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeUserModal() {
            const modal = document.getElementById('userModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function handleModalRoleChange() {
            const role = document.getElementById('modalRole').value;
            const container = document.getElementById('containerWilayahAkses');
            if (!container) return;

            if (role === 'ADMIN') {
                container.classList.add('hidden');
            } else {
                container.classList.remove('hidden');
            }
        }

        async function handleUserSubmit(e) {
            e.preventDefault();

            const form = document.getElementById('userForm');
            const role = document.getElementById('modalRole').value;
            const alertBox = document.getElementById('userModalAlert');
            const alertText = document.getElementById('userModalAlertText');

            if (role === 'USER') {
                const kec = document.getElementById('modalKecamatanSelect').value;
                if (!kec) {
                    alertBox.classList.remove('hidden');
                    alertText.innerText = "Pilih Kecamatan wilayah tugas terlebih dahulu!";
                    return;
                }
                const checked = document.querySelectorAll('.desa-checkbox:checked');
                if (checked.length === 0) {
                    alertBox.classList.remove('hidden');
                    alertText.innerText = "Pilih minimal satu Desa/Kelurahan wilayah tugas untuk Tim Entri!";
                    return;
                }
            }

            const btn = document.getElementById('btnSubmitUser');
            const btnText = document.getElementById('btnTextUser');
            const spinner = document.getElementById('btnSpinnerUser');

            btn.disabled = true;
            btnText.innerText = "Menyimpan...";
            spinner.classList.remove('hidden');

            const formData = new FormData(form);

            try {
                const res = await fetch(`${BASE_URL}ats/saveUser`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();

                if (json.status === 'success') {
                    showToast(json.message, "success");
                    closeUserModal();
                    await loadUsersData();
                } else {
                    alertBox.classList.remove('hidden');
                    alertText.innerText = json.message || "Gagal menyimpan akun.";
                }
            } catch (err) {
                alertBox.classList.remove('hidden');
                alertText.innerText = "Terjadi kesalahan jaringan: " + err.message;
            } finally {
                btn.disabled = false;
                btnText.innerText = "Simpan Akun";
                spinner.classList.add('hidden');
            }
        }

        // ==================== ACTIVITY AUDIT LOGS ====================
        let debounceLogTimer = null;
        function debounceFilterLogs() {
            clearTimeout(debounceLogTimer);
            debounceLogTimer = setTimeout(() => {
                loadLogsData();
            }, 350);
        }

        async function loadLogsData() {
            const loading = document.getElementById('logsLoading');
            const empty = document.getElementById('logsEmptyState');
            const tbody = document.getElementById('logsTableBody');
            if (!tbody) return;

            loading.classList.remove('hidden');
            empty.classList.add('hidden');
            tbody.innerHTML = '';

            const action = document.getElementById('logsFilterAction')?.value || '';
            const query = document.getElementById('logsSearchQuery')?.value || '';

            try {
                const params = new URLSearchParams();
                if (action) params.append('action', action);
                if (query) params.append('q', query);

                const res = await fetch(`${BASE_URL}ats/getLogs?${params.toString()}`);
                const json = await res.json();
                if (json.status === 'success') {
                    renderLogsTable(json.data);
                } else {
                    showToast(json.message || "Gagal memuat log aktivitas", "error");
                }
            } catch (err) {
                showToast("Kesalahan jaringan saat memuat log aktivitas", "error");
            } finally {
                loading.classList.add('hidden');
            }
        }

        function renderLogsTable(logs) {
            const tbody = document.getElementById('logsTableBody');
            const empty = document.getElementById('logsEmptyState');
            if (!tbody) return;

            tbody.innerHTML = '';
            if (!logs || logs.length === 0) {
                empty.classList.remove('hidden');
                return;
            }
            empty.classList.add('hidden');

            const actionBadges = {
                'LOGIN': '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fas fa-right-to-bracket text-[8.5px]"></i> LOGIN</span>',
                'LOGOUT': '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-slate-100 text-slate-700 border border-slate-200"><i class="fas fa-right-from-bracket text-[8.5px]"></i> LOGOUT</span>',
                'LOGIN_FAILED': '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-red-50 text-red-700 border border-red-200"><i class="fas fa-circle-exclamation text-[8.5px]"></i> GAGAL</span>',
                'CREATE_DATA': '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-blue-50 text-blue-700 border border-blue-200"><i class="fas fa-plus text-[8.5px]"></i> ENTRI</span>',
                'UPDATE_DATA': '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-amber-50 text-amber-700 border border-amber-200"><i class="fas fa-pen text-[8.5px]"></i> UBAH</span>',
                'DELETE_DATA': '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-rose-50 text-rose-700 border border-rose-200"><i class="fas fa-trash-can text-[8.5px]"></i> HAPUS</span>',
                'EXPORT_EXCEL': '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-teal-50 text-teal-700 border border-teal-200"><i class="fas fa-file-excel text-[8.5px]"></i> EXCEL</span>',
                'PRINT_REPORT': '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200"><i class="fas fa-print text-[8.5px]"></i> PDF</span>',
                'PRINT_CARD': '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-sky-50 text-sky-700 border border-sky-200"><i class="fas fa-id-card text-[8.5px]"></i> KARTU</span>',
                'CHANGE_PASSWORD': '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-purple-50 text-purple-700 border border-purple-200"><i class="fas fa-key text-[8.5px]"></i> PASS</span>',
                'MANAGE_USER': '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-violet-50 text-violet-700 border border-violet-200"><i class="fas fa-users-gear text-[8.5px]"></i> USER</span>'
            };

            logs.forEach((log, idx) => {
                const tr = document.createElement('tr');
                tr.className = "hover:bg-slate-50/80 transition";

                const badge = actionBadges[log.action] || `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-slate-100 text-slate-700 border border-slate-200">${escapeHtml(log.action)}</span>`;

                let targetInfo = '';
                if (log.nik_target || log.nama_anak) {
                    targetInfo = `<div class="mt-0.5 text-[10px] text-slate-500 font-mono">Sasaran: <strong class="text-slate-800">${escapeHtml(log.nama_anak || '-')}</strong> (${escapeHtml(log.nik_target || '-')})</div>`;
                }

                let wilayahInfo = '';
                if (log.desa || log.kecamatan) {
                    wilayahInfo = `
                        <div class="text-[11px] font-semibold text-slate-800">${escapeHtml(log.desa || '-')}</div>
                        <div class="text-[9.5px] text-slate-500 uppercase">Kec. ${escapeHtml(log.kecamatan || '-')}</div>
                    `;
                } else {
                    wilayahInfo = `<span class="text-slate-400 text-xs">-</span>`;
                }

                const dateObj = new Date(log.created_at);
                const timeStr = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' +
                                dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

                tr.innerHTML = `
                    <td class="py-2.5 px-3 text-center font-bold text-slate-400 text-xs">${idx + 1}</td>
                    <td class="py-2.5 px-3 font-mono text-[11px] text-slate-600">${escapeHtml(timeStr)}</td>
                    <td class="py-2.5 px-3">${badge}</td>
                    <td class="py-2.5 px-3">
                        <div class="font-bold text-slate-900 text-xs">${escapeHtml(log.nama || log.username || 'System')}</div>
                        <div class="text-[10px] font-mono text-slate-500">@${escapeHtml(log.username || '-')} <span class="text-[9px] text-indigo-600 font-bold">(${escapeHtml(log.role || '-')})</span></div>
                    </td>
                    <td class="py-2.5 px-3">
                        <div class="text-xs text-slate-800 leading-snug">${escapeHtml(log.description || '-')}</div>
                        ${targetInfo}
                    </td>
                    <td class="py-2.5 px-3">${wilayahInfo}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-[10px] text-slate-500">${escapeHtml(log.ip_address || '-')}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        async function deleteUserRecord(id, username) {
            if (!confirm(`Apakah Anda yakin ingin menghapus akun '${username}'? Akun yang dihapus tidak dapat login kembali.`)) {
                return;
            }

            try {
                const res = await fetch(`${BASE_URL}ats/deleteUser/${id}`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                if (json.status === 'success') {
                    showToast(json.message, "success");
                    await loadUsersData();
                } else {
                    showToast(json.message || "Gagal menghapus akun.", "error");
                }
            } catch (err) {
                showToast("Kesalahan jaringan saat menghapus akun: " + err.message, "error");
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>
