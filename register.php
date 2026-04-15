<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "koneksi.php";

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($username != "" && $password != "" && $confirm_password != "") {
<<<<<<< HEAD

        // Cek password dan konfirmasi cocok
        if ($password !== $confirm_password) {
            $error = "Password dan konfirmasi password tidak cocok.";
=======
        // Cek password dan konfirmasi cocok
        if ($password !== $confirm_password) {
            $error = "Password dan konfirmasi password tidak cocok.";
        } else {
        // Cek username sudah ada atau belum
        $cek = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $cek->execute([$username]);

        if ($cek->rowCount() > 0) {
            $error = "Username sudah digunakan.";
>>>>>>> 70ef77c2aadaf653149844ed1efcb7978398ed2f
        } else {
            // Cek username sudah ada atau belum
            $cek = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $cek->execute([$username]);

            if ($cek->rowCount() > 0) {
                $error = "Username sudah digunakan.";
            } else {
                // Hash password
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");

                if ($stmt->execute([$username, $hash])) {
                    $success = "Registrasi berhasil! Silakan login.";
                } else {
                    $error = "Registrasi gagal, coba lagi.";
                }
            }
        }

    } else {
        $error = "Semua field wajib diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@300;400;500&display=swap');

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #eaeff1;
            --card: #18171a;
            --accent: #8ce4c2;
            --accent-dim: #7a6230;
            --text: #f0ead8;
            --text-muted: #7a7060;
            --border: #2e2b24;
            --error: #e07070;
            --success: #70c490;
            --warn: #e0a870;
        }

        body {
            background-color: var(--bg);
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(201,168,76,0.06) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(201,168,76,0.04) 0%, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 52px 48px;
            width: 100%;
            max-width: 420px;
            box-shadow:
                0 0 0 1px rgba(201,168,76,0.05),
                0 32px 64px rgba(0,0,0,0.5);
            animation: fadeUp 0.5s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .logo-line {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 36px;
        }

        .logo-dot {
            width: 28px;
            height: 28px;
            background: var(--accent);
            border-radius: 50%;
            flex-shrink: 0;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 600;
            color: var(--text);
            letter-spacing: 0.01em;
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin-bottom: 32px;
        }

        .field {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }
<<<<<<< HEAD

=======
        
>>>>>>> 70ef77c2aadaf653149844ed1efcb7978398ed2f
        input[type="text"],
        input[type="password"] {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 3px;
            padding: 12px 40px 12px 16px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--text);
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: var(--accent-dim);
            background: rgba(201,168,76,0.04);
        }

        /* Match indicator state */
        input.match {
            border-color: var(--success) !important;
        }

        input.no-match {
            border-color: var(--error) !important;
        }

        input::placeholder {
            color: var(--text-muted);
        }

        /* Match icon inside input */
        .match-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .match-icon.visible {
            opacity: 1;
        }

        /* Hint text below confirm field */
        .hint {
            font-size: 11px;
            margin-top: 6px;
            height: 14px;
            transition: color 0.2s;
        }

        .hint.ok    { color: var(--success); }
        .hint.fail  { color: var(--error); }

        .error-msg {
            background: rgba(224,112,112,0.1);
            border: 1px solid rgba(224,112,112,0.3);
            border-radius: 3px;
            color: var(--error);
            font-size: 13px;
            padding: 10px 14px;
            margin-bottom: 20px;
        }

        .success-msg {
            background: rgba(112,196,144,0.1);
            border: 1px solid rgba(112,196,144,0.3);
            border-radius: 3px;
            color: var(--success);
            font-size: 13px;
            padding: 10px 14px;
            margin-bottom: 20px;
        }

        .btn {
            width: 100%;
            background: var(--accent);
            color: #0f0e0c;
            border: none;
            border-radius: 3px;
            padding: 13px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s, transform 0.1s, opacity 0.2s;
        }

<<<<<<< HEAD
        .btn:hover  { background: #a0edd0; }
=======
        .btn:hover { background: #a0edd0; }
>>>>>>> 70ef77c2aadaf653149844ed1efcb7978398ed2f
        .btn:active { transform: scale(0.98); }
        .btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .login-link a {
            color: var(--accent);
            text-decoration: none;
        }

        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo-line">
            <div class="logo-dot"></div>
            <h1>Buat Akun Baru</h1>
        </div>
        <div class="divider"></div>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-msg"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="registerForm">
            <div class="field">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Buat username"
                        required
                        autocomplete="username"
                        value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                    >
                </div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Buat password"
                        required
                        autocomplete="new-password"
                    >
                </div>
            </div>

            <div class="field">
                <label for="confirm_password">Konfirmasi Password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Ulangi password"
                        required
                        autocomplete="new-password"
                    >
                    <span class="match-icon" id="matchIcon"></span>
                </div>
                <div class="hint" id="matchHint"></div>
            </div>

            <button type="submit" class="btn" id="submitBtn">Daftar</button>
        </form>

        <div class="login-link">
            Sudah punya akun? <a href="login.php">Masuk di sini</a>
        </div>
    </div>

    <script>
        const passwordInput  = document.getElementById('password');
        const confirmInput   = document.getElementById('confirm_password');
        const matchIcon      = document.getElementById('matchIcon');
        const matchHint      = document.getElementById('matchHint');
        const submitBtn      = document.getElementById('submitBtn');

        function checkMatch() {
            const pw  = passwordInput.value;
            const cpw = confirmInput.value;

            if (cpw === '') {
                confirmInput.classList.remove('match', 'no-match');
                matchIcon.classList.remove('visible');
                matchIcon.textContent = '';
                matchHint.textContent = '';
                matchHint.className = 'hint';
                submitBtn.disabled = false;
                return;
            }

            if (pw === cpw) {
                confirmInput.classList.add('match');
                confirmInput.classList.remove('no-match');
                matchIcon.textContent = '✓';
                matchIcon.classList.add('visible');
                matchHint.textContent = 'Password cocok';
                matchHint.className = 'hint ok';
                submitBtn.disabled = false;
            } else {
                confirmInput.classList.add('no-match');
                confirmInput.classList.remove('match');
                matchIcon.textContent = '✕';
                matchIcon.classList.add('visible');
                matchHint.textContent = 'Password tidak cocok';
                matchHint.className = 'hint fail';
                submitBtn.disabled = true;
            }
        }

        confirmInput.addEventListener('input', checkMatch);
        passwordInput.addEventListener('input', checkMatch);
    </script>
</body>
</html>
