<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/AuthModel.php';
require_once __DIR__ . '/../core/SmtpMailer.php';
class AuthController extends Controller {
    public function login(): void {
        if (!empty($_SESSION['user'])) $this->redirect('index.php?page=dashboard');
        if (is_post()) {
            csrf_check();
            if (!login_captcha_valid($_POST['captcha'] ?? '')) {
                flash('error', 'Captcha login tidak sesuai.');
                $this->redirect('index.php?page=login');
            }
            $model = new AuthModel();
            $user = $model->findByUsername(trim($_POST['username'] ?? ''));
            if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (int)$user['id'], 'umkm_id' => $user['umkm_id'] ? (int)$user['umkm_id'] : null, 'role_id' => (int)$user['role_id'],
                    'nama' => $user['nama'] ?: $user['username'], 'username' => $user['username'], 'role' => $user['nama_role'], 'nama_umkm' => $user['nama_umkm'] ?? '-',
                    'email' => $user['email'] ?? '',
                ];
                $model->updateLastLogin((int)$user['id']);
                flash('success', 'Login berhasil.');
                $this->redirect('index.php?page=dashboard');
            }
            flash('error', 'Username atau password salah.');
            $this->redirect('index.php?page=login');
        }
        refresh_login_captcha();
        $this->view('auth/login', ['title' => 'Login']);
    }
    public function forgotPassword(): void {
        if (is_post()) {
            csrf_check();
            $email = trim($_POST['email'] ?? '');
            if ($email === '') { flash('error','Email wajib diisi.'); $this->redirect('index.php?page=forgot-password'); }
            $model = new AuthModel();
            $user = $model->findByEmail($email);
            if ($user) {
                $token = bin2hex(random_bytes(24));
                $expires = date('Y-m-d H:i:s', time() + 3600);
                $model->saveResetToken((int)$user['id'], $token, $expires);
                $link = url('index.php?page=reset-password&token=' . $token);
                $mailer = new SmtpMailer(app_config()['smtp']);
                try {
                    $mailer->send($email, 'Reset Password Sistem UMKM', '<p>Halo ' . htmlspecialchars($user['nama']) . ',</p><p>Klik tautan berikut untuk mengatur ulang password:</p><p><a href="' . htmlspecialchars($link) . '">' . htmlspecialchars($link) . '</a></p><p>Tautan berlaku 1 jam.</p>');
                } catch (Throwable $e) {
                    flash('error', 'Gagal mengirim email reset password. Periksa konfigurasi SMTP. ' . $e->getMessage());
                    $this->redirect('index.php?page=forgot-password');
                }
            }
            flash('success', 'Jika email terdaftar, tautan reset password telah dikirim.');
            $this->redirect('index.php?page=forgot-password');
        }
        $this->view('auth/forgot_password', ['title' => 'Lupa Password']);
    }
    public function resetPassword(): void {
        $token = trim($_GET['token'] ?? $_POST['token'] ?? '');
        $model = new AuthModel();
        $user = $token ? $model->findByResetToken($token) : null;
        if (!$user) { flash('error', 'Token reset password tidak valid atau sudah kedaluwarsa.'); $this->redirect('index.php?page=login'); }
        if (is_post()) {
            csrf_check();
            $pass = (string)($_POST['password'] ?? '');
            $confirm = (string)($_POST['password_confirmation'] ?? '');
            if (strlen($pass) < 6) { flash('error','Password minimal 6 karakter.'); $this->redirect('index.php?page=reset-password&token='.$token); }
            if ($pass !== $confirm) { flash('error','Konfirmasi password tidak sama.'); $this->redirect('index.php?page=reset-password&token='.$token); }
            $model->updatePassword((int)$user['id'], password_hash($pass, PASSWORD_DEFAULT));
            flash('success', 'Password berhasil diperbarui. Silakan login.');
            $this->redirect('index.php?page=login');
        }
        $this->view('auth/reset_password', ['title' => 'Reset Password', 'token' => $token]);
    }
    public function changePassword(): void {
        require_roles('super_admin','admin_umkm','operator');
        if (is_post()) {
            csrf_check();
            $model = new AuthModel();
            $user = $model->findByUsername(current_user()['username']);
            if (!$user || !password_verify($_POST['current_password'] ?? '', $user['password_hash'])) {
                flash('error', 'Password lama salah.'); $this->redirect('index.php?page=change-password');
            }
            $pass = (string)($_POST['password'] ?? '');
            $confirm = (string)($_POST['password_confirmation'] ?? '');
            if (strlen($pass) < 6) { flash('error','Password baru minimal 6 karakter.'); $this->redirect('index.php?page=change-password'); }
            if ($pass !== $confirm) { flash('error','Konfirmasi password baru tidak sama.'); $this->redirect('index.php?page=change-password'); }
            $model->updatePassword((int)$user['id'], password_hash($pass, PASSWORD_DEFAULT));
            flash('success', 'Password berhasil diganti.'); $this->redirect('index.php?page=dashboard');
        }
        $this->view('auth/change_password', ['title' => 'Ganti Password']);
    }
    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool)($params['secure'] ?? false), (bool)($params['httponly'] ?? true));
        }
        session_destroy();
        header('Location: '.url('index.php?page=login'));
        exit;
    }
}
