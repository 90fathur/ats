<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ATS Anak Tidak Sekolah | Kab. Polewali Mandar</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            background-color: #f1f5f9;
        }
        .compact-input {
            font-size: 11px;
            padding: 7px 11px;
            border-radius: 7px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            color: #0f172a;
            transition: all 0.15s ease-in-out;
        }
        .compact-input:focus {
            outline: none;
            background-color: #ffffff;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-100 via-indigo-50/50 to-slate-200 text-slate-800">
    <div class="w-full max-w-xs sm:max-w-sm">
        <!-- Logo & Title -->
        <div class="text-center mb-5">
            <div class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-indigo-600 text-white shadow-md mb-2 text-base font-bold">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h1 class="text-sm font-extrabold text-slate-900 tracking-wider uppercase">ATS - ANAK TIDAK SEKOLAH</h1>
            <p class="text-[10px] text-indigo-700 font-bold mt-0.5">Pemerintah Kabupaten Polewali Mandar</p>
            <p class="text-[9px] text-slate-500">Dinas Pendidikan dan Kebudayaan</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-xl">
            <div class="mb-4 pb-3 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold text-slate-800">Autentikasi Sistem</h2>
                    <p class="text-[10px] text-slate-500">Silakan masukkan akun Anda</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    ATS v1.0
                </span>
            </div>

            <!-- Error Box -->
            <div id="loginAlert" class="hidden mb-3 p-2 rounded-lg bg-red-50 border border-red-200 text-red-700 text-[10px] flex items-center gap-2">
                <i class="fas fa-circle-exclamation text-xs text-red-500 flex-shrink-0"></i>
                <span id="loginAlertText"></span>
            </div>

            <!-- Form -->
            <form id="loginForm" action="<?= base_url('login') ?>" method="POST" class="space-y-3.5">
                <?= csrf_field() ?>
                <div>
                    <label for="username" class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">
                        Username
                    </label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-2.5 text-slate-400 text-[11px]"></i>
                        <input type="text" id="username" name="username" required placeholder="Masukkan Username" class="compact-input w-full pl-8 font-medium">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-2.5 text-slate-400 text-[11px]"></i>
                        <input type="password" id="password" name="password" required placeholder="Masukkan Kata Sandi" class="compact-input w-full pl-8 pr-8 font-medium">
                        <button type="button" id="togglePassword" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                            <i class="fas fa-eye text-[11px]" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" id="btnSubmit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-xs rounded-lg shadow-sm transition flex items-center justify-center gap-1.5 mt-4">
                    <span id="btnText">Masuk ke Panel</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                    <i id="btnSpinner" class="fas fa-spinner fa-spin hidden"></i>
                </button>
            </form>
        </div>

        <p class="text-center text-[9px] text-slate-500 mt-4">
            &copy; <?= date('Y') ?> Dinas Pendidikan & Kebudayaan Kab. Polewali Mandar
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
