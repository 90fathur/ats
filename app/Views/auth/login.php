<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ATS Anak Tidak Sekolah</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            background-color: #0f172a; /* Slate 900 */
        }
        .compact-input {
            font-size: 11px;
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #334155;
            background-color: #1e293b;
            color: #f8fafc;
            transition: all 0.15s ease-in-out;
        }
        .compact-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-xs sm:max-w-sm">
        <!-- Logo & Title -->
        <div class="text-center mb-5">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg mb-2 text-base font-bold">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h1 class="text-sm font-extrabold text-white tracking-wider uppercase">ATS - ANAK TIDAK SEKOLAH</h1>
            <p class="text-[10px] text-indigo-400 font-semibold mt-0.5">Kabupaten Polewali Mandar</p>
            <p class="text-[9px] text-slate-400">Dinas Pendidikan dan Kebudayaan</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-2xl">
            <div class="mb-4 pb-3 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold text-slate-200">Autentikasi Petugas</h2>
                    <p class="text-[10px] text-slate-400">Silakan masukkan akun Anda</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[9px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    ATS v1.0
                </span>
            </div>

            <!-- Error Box -->
            <div id="loginAlert" class="hidden mb-3 p-2 rounded-md bg-red-950/60 border border-red-800 text-red-300 text-[10px] flex items-center gap-2">
                <i class="fas fa-circle-exclamation text-xs text-red-400 flex-shrink-0"></i>
                <span id="loginAlertText"></span>
            </div>

            <!-- Form -->
            <form id="loginForm" action="<?= base_url('login') ?>" method="POST" class="space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label for="username" class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">
                        Username / NIK
                    </label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-2.5 top-2 text-slate-500 text-[10px]"></i>
                        <input type="text" id="username" name="username" required value="ari" placeholder="Username" class="compact-input w-full pl-7">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-2.5 top-2 text-slate-500 text-[10px]"></i>
                        <input type="password" id="password" name="password" required value="ari" placeholder="Password" class="compact-input w-full pl-7 pr-7">
                        <button type="button" id="togglePassword" class="absolute right-2 top-2 text-slate-500 hover:text-slate-300">
                            <i class="fas fa-eye text-[10px]" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" id="btnSubmit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-xs rounded-md shadow transition flex items-center justify-center gap-1.5 mt-4">
                    <span id="btnText">Masuk ke Panel</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                    <i id="btnSpinner" class="fas fa-spinner fa-spin hidden"></i>
                </button>
            </form>

            <!-- Quick Demo Credentials -->
            <div class="mt-4 pt-3 border-t border-slate-800">
                <span class="block text-[9px] uppercase tracking-wider text-slate-400 font-bold mb-1.5 text-center">Akun Cepat (Klik)</span>
                <div class="grid grid-cols-2 gap-1.5">
                    <button type="button" onclick="fillCredentials('ari', 'ari')" class="p-1.5 rounded bg-slate-800/80 hover:bg-slate-800 border border-slate-700 text-left transition">
                        <span class="text-[10px] font-bold text-indigo-300 block">👤 Petugas</span>
                        <span class="text-[9px] text-slate-400 font-mono">ari / ari</span>
                    </button>
                    <button type="button" onclick="fillCredentials('admin', 'ari')" class="p-1.5 rounded bg-slate-800/80 hover:bg-slate-800 border border-slate-700 text-left transition">
                        <span class="text-[10px] font-bold text-amber-300 block">🛡️ Admin</span>
                        <span class="text-[9px] text-slate-400 font-mono">admin / ari</span>
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center text-[9px] text-slate-500 mt-4">
            &copy; <?= date('Y') ?> ATS - Anak Tidak Sekolah
        </p>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', () => {
            const isPass = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPass ? 'text' : 'password');
            eyeIcon.classList.toggle('fa-eye', !isPass);
            eyeIcon.classList.toggle('fa-eye-slash', isPass);
        });

        function fillCredentials(u, p) {
            document.getElementById('username').value = u;
            document.getElementById('password').value = p;
            document.getElementById('loginForm').dispatchEvent(new Event('submit'));
        }

        const loginForm = document.getElementById('loginForm');
        const loginAlert = document.getElementById('loginAlert');
        const loginAlertText = document.getElementById('loginAlertText');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            loginAlert.classList.add('hidden');
            btnSubmit.disabled = true;
            btnText.innerText = "Memverifikasi...";
            btnSpinner.classList.remove('hidden');

            const formData = new FormData(loginForm);

            try {
                const response = await fetch('<?= base_url('login') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();

                if (result.status === 'success') {
                    btnText.innerText = "Mengalihkan...";
                    window.location.href = result.redirect || '<?= base_url('ats') ?>';
                } else {
                    loginAlertText.innerText = result.message || 'Login gagal.';
                    loginAlert.classList.remove('hidden');
                    btnSubmit.disabled = false;
                    btnText.innerText = "Masuk ke Panel";
                    btnSpinner.classList.add('hidden');
                }
            } catch (err) {
                loginForm.submit();
            }
        });
    </script>
</body>
</html>
