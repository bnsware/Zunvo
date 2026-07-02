<?php

session_start();

require_once __DIR__ . '/core/detect.php';

define('INSTALL_ROOT', __DIR__);
define('INSTALL_STORAGE', INSTALL_ROOT . '/storage');
define('INSTALL_LOCK', INSTALL_STORAGE . '/install.lock');
define('INSTALL_SITE_URL', zunvo_detect_site_url());

if (file_exists(INSTALL_LOCK) && !isset($_GET['force'])) {
    header('Location: ' . rtrim(INSTALL_SITE_URL, '/') . '/');
    exit;
}

function install_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function install_redirect(int $step): void
{
    header('Location: install.php?step=' . $step);
    exit;
}

function install_pdo(array $db): PDO
{
    $dsn = 'mysql:host=' . $db['host'] . ';dbname=' . $db['name'] . ';charset=utf8mb4';
    return new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
    ]);
}

function install_execute_sql_file(PDO $pdo, string $path): void
{
    if (!is_readable($path)) {
        throw new RuntimeException('SQL dosyası okunamadı: ' . basename($path));
    }

    $sql = file_get_contents($path);
    $sql = preg_replace('/--.*$/m', '', $sql);
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }
}

function install_check_requirements(): array
{
    return [
        'php' => [
            'label' => 'PHP 8.0 veya üzeri',
            'ok' => version_compare(PHP_VERSION, '8.0.0', '>='),
            'detail' => 'Mevcut: ' . PHP_VERSION,
        ],
        'pdo' => [
            'label' => 'PDO eklentisi',
            'ok' => extension_loaded('pdo') && extension_loaded('pdo_mysql'),
            'detail' => extension_loaded('pdo_mysql') ? 'pdo_mysql yüklü' : 'pdo_mysql bulunamadı',
        ],
        'mbstring' => [
            'label' => 'mbstring eklentisi',
            'ok' => extension_loaded('mbstring'),
            'detail' => extension_loaded('mbstring') ? 'mbstring yüklü' : 'mbstring bulunamadı',
        ],
        'writable_storage' => [
            'label' => 'storage dizini yazılabilir',
            'ok' => is_dir(INSTALL_STORAGE) ? is_writable(INSTALL_STORAGE) : is_writable(INSTALL_ROOT),
            'detail' => is_dir(INSTALL_STORAGE)
                ? (is_writable(INSTALL_STORAGE) ? 'Yazılabilir' : 'Yazılamıyor')
                : 'Kurulum sırasında oluşturulacak',
        ],
        'writable_config' => [
            'label' => 'config dizini yazılabilir',
            'ok' => is_writable(INSTALL_ROOT . '/config'),
            'detail' => is_writable(INSTALL_ROOT . '/config') ? 'Yazılabilir' : 'Yazılamıyor',
        ],
    ];
}

function install_requirements_passed(array $checks): bool
{
    foreach ($checks as $check) {
        if (!$check['ok'] && $check['label'] !== 'storage dizini yazılabilir') {
            return false;
        }
    }

    return $checks['php']['ok'] && $checks['pdo']['ok'] && $checks['mbstring']['ok'] && $checks['writable_config']['ok'];
}

function install_validate_username(string $username): bool
{
    return preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username) === 1;
}

function install_validate_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function install_validate_password(string $password): array
{
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Şifre en az 8 karakter olmalıdır.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Şifre en az bir büyük harf içermelidir.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Şifre en az bir küçük harf içermelidir.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Şifre en az bir rakam içermelidir.';
    }

    return $errors;
}

function install_write_database_config(array $db): bool
{
    $content = "<?php\n\n";
    $content .= "define('DB_HOST', " . var_export($db['host'], true) . ");\n";
    $content .= "define('DB_NAME', " . var_export($db['name'], true) . ");\n";
    $content .= "define('DB_USER', " . var_export($db['user'], true) . ");\n";
    $content .= "define('DB_PASS', " . var_export($db['pass'], true) . ");\n";
    $content .= "define('DB_CHARSET', 'utf8mb4');\n\n";
    $content .= "define('DB_OPTIONS', [\n";
    $content .= "    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n";
    $content .= "    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n";
    $content .= "    PDO::ATTR_EMULATE_PREPARES => false,\n";
    $content .= "    PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES utf8mb4\"\n";
    $content .= "]);\n\n";
    $content .= "function create_database_connection() {\n";
    $content .= "    try {\n";
    $content .= "        \$dsn = \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=\" . DB_CHARSET;\n";
    $content .= "        \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, DB_OPTIONS);\n";
    $content .= "        return \$pdo;\n";
    $content .= "    } catch (PDOException \$e) {\n";
    $content .= "        if (defined('DEBUG_MODE') && DEBUG_MODE === true) {\n";
    $content .= "            die(\"Veritabanı Bağlantı Hatası: \" . \$e->getMessage());\n";
    $content .= "        }\n";
    $content .= "        die(\"Veritabanı bağlantısı kurulamadı. Lütfen sistem yöneticisiyle iletişime geçin.\");\n";
    $content .= "    }\n";
    $content .= "}\n";

    return file_put_contents(INSTALL_ROOT . '/config/database.php', $content) !== false;
}

function install_create_directories(): array
{
    $paths = [
        INSTALL_STORAGE,
        INSTALL_STORAGE . '/cache',
        INSTALL_STORAGE . '/logs',
        INSTALL_STORAGE . '/sessions',
        INSTALL_ROOT . '/public/uploads',
        INSTALL_ROOT . '/public/uploads/avatars',
        INSTALL_ROOT . '/public/uploads/attachments',
    ];

    $failed = [];

    foreach ($paths as $path) {
        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            $failed[] = $path;
        }
    }

    return $failed;
}

function install_render_header(int $step, string $title): void
{
    $steps = [
        1 => 'Gereksinimler',
        2 => 'Veritabanı',
        3 => 'Şema',
        4 => 'Yönetici',
        5 => 'Tamamla',
    ];

    echo '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . install_escape($title) . ' - Zunvo Kurulum</title>';
    echo '<style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f0f2f5; color: #1a1a2e; line-height: 1.6; }
        .container { max-width: 720px; margin: 40px auto; padding: 0 20px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 32px; }
        h1 { font-size: 1.75rem; margin-bottom: 8px; color: #16213e; }
        .subtitle { color: #666; margin-bottom: 24px; }
        .steps { display: flex; gap: 8px; margin-bottom: 28px; flex-wrap: wrap; }
        .step-item { flex: 1; min-width: 90px; text-align: center; padding: 10px 8px; border-radius: 8px; font-size: 0.85rem; background: #e9ecef; color: #666; }
        .step-item.active { background: #4361ee; color: #fff; font-weight: 600; }
        .step-item.done { background: #d8f3dc; color: #2d6a4f; }
        .check-list { list-style: none; margin: 20px 0; }
        .check-list li { padding: 12px 16px; border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; }
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .badge-ok { background: #d8f3dc; color: #2d6a4f; }
        .badge-fail { background: #ffe0e0; color: #c1121f; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 0.9rem; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px 14px; border: 1px solid #ced4da; border-radius: 8px; font-size: 1rem; }
        input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.15); }
        .btn { display: inline-block; padding: 12px 28px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; text-decoration: none; }
        .btn-primary { background: #4361ee; color: #fff; }
        .btn-primary:hover { background: #3a56d4; }
        .btn-secondary { background: #e9ecef; color: #333; }
        .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #ffe0e0; color: #c1121f; border: 1px solid #f5c2c7; }
        .alert-success { background: #d8f3dc; color: #2d6a4f; border: 1px solid #b7e4c7; }
        .alert-info { background: #e7f1ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .actions { margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap; }
        .detail { font-size: 0.85rem; color: #888; }
        .success-icon { font-size: 3rem; text-align: center; margin-bottom: 16px; }
        footer { text-align: center; margin-top: 24px; color: #999; font-size: 0.85rem; }
    </style></head><body><div class="container"><div class="card">';
    echo '<h1>Zunvo Forum Kurulumu</h1>';
    echo '<p class="subtitle">' . install_escape($title) . '</p>';
    echo '<div class="steps">';

    foreach ($steps as $num => $label) {
        $class = 'step-item';
        if ($num === $step) {
            $class .= ' active';
        } elseif ($num < $step) {
            $class .= ' done';
        }
        echo '<div class="' . $class . '">' . $num . '. ' . install_escape($label) . '</div>';
    }

    echo '</div>';
}

function install_render_footer(): void
{
    echo '<footer>Zunvo Forum Kurulum Sihirbazı</footer>';
    echo '</div></div></body></html>';
}

if (file_exists(INSTALL_LOCK)) {
    install_render_header(5, 'Kurulum Tamamlandı');
    echo '<div class="alert alert-info">Zunvo forum zaten kurulmuş. Güvenlik için <strong>install.php</strong> dosyasını sunucudan silmeniz önerilir.</div>';
    echo '<div class="actions"><a href="index.php" class="btn btn-primary">Foruma Git</a></div>';
    install_render_footer();
    exit;
}

$step = isset($_GET['step']) ? (int) $_GET['step'] : 1;
$step = max(1, min(5, $step));
$error = '';
$success = '';

if (!isset($_SESSION['install'])) {
    $_SESSION['install'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'step1_continue') {
        $checks = install_check_requirements();
        if (install_requirements_passed($checks)) {
            $_SESSION['install']['requirements_ok'] = true;
            install_redirect(2);
        }
        $error = 'Tüm gereksinimler karşılanmadan devam edilemez.';
        $step = 1;
    }

    if ($action === 'step2_test') {
        $db = [
            'host' => trim($_POST['db_host'] ?? 'localhost'),
            'name' => trim($_POST['db_name'] ?? ''),
            'user' => trim($_POST['db_user'] ?? ''),
            'pass' => $_POST['db_pass'] ?? '',
        ];

        if ($db['name'] === '' || $db['user'] === '') {
            $error = 'Veritabanı adı ve kullanıcı adı zorunludur.';
            $step = 2;
        } else {
            try {
                install_pdo($db)->query('SELECT 1');
                $_SESSION['install']['db'] = $db;
                $_SESSION['install']['requirements_ok'] = true;
                install_redirect(3);
            } catch (PDOException $e) {
                $error = 'Veritabanı bağlantısı başarısız: ' . $e->getMessage();
                $step = 2;
            }
        }
    }

    if ($action === 'step3_run') {
        if (empty($_SESSION['install']['db'])) {
            install_redirect(2);
        }

        try {
            $pdo = install_pdo($_SESSION['install']['db']);
            install_execute_sql_file($pdo, INSTALL_ROOT . '/database/schema.sql');
            install_execute_sql_file($pdo, INSTALL_ROOT . '/database/seed.sql');
            $_SESSION['install']['schema_done'] = true;
            install_redirect(4);
        } catch (Throwable $e) {
            $error = 'Veritabanı kurulumu başarısız: ' . $e->getMessage();
            $step = 3;
        }
    }

    if ($action === 'step4_create') {
        if (empty($_SESSION['install']['schema_done'])) {
            install_redirect(3);
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (!install_validate_username($username)) {
            $error = 'Kullanıcı adı 3-20 karakter olmalı ve yalnızca harf, rakam, tire ve alt çizgi içermelidir.';
        } elseif (!install_validate_email($email)) {
            $error = 'Geçerli bir e-posta adresi girin.';
        } elseif ($password !== $passwordConfirm) {
            $error = 'Şifreler eşleşmiyor.';
        } else {
            $passwordErrors = install_validate_password($password);
            if (!empty($passwordErrors)) {
                $error = implode(' ', $passwordErrors);
            }
        }

        if ($error === '') {
            try {
                $pdo = install_pdo($_SESSION['install']['db']);
                $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
                $stmt->execute([$username, $email]);
                if ($stmt->fetch()) {
                    $error = 'Bu kullanıcı adı veya e-posta zaten kayıtlı.';
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $insert = $pdo->prepare(
                        'INSERT INTO users (username, email, password, role, email_verified, created_at) VALUES (?, ?, ?, ?, 1, NOW())'
                    );
                    $insert->execute([$username, $email, $hash, 'admin']);
                    $_SESSION['install']['admin'] = [
                        'username' => $username,
                        'email' => $email,
                    ];
                    install_redirect(5);
                }
            } catch (Throwable $e) {
                $error = 'Yönetici hesabı oluşturulamadı: ' . $e->getMessage();
            }
        }

        $step = 4;
    }

    if ($action === 'step5_finish') {
        if (empty($_SESSION['install']['admin']) || empty($_SESSION['install']['db'])) {
            install_redirect(4);
        }

        $failedDirs = install_create_directories();
        if (!empty($failedDirs)) {
            $error = 'Dizinler oluşturulamadı: ' . implode(', ', $failedDirs);
            $step = 5;
        } elseif (!install_write_database_config($_SESSION['install']['db'])) {
            $error = 'config/database.php dosyası yazılamadı. Dizin izinlerini kontrol edin.';
            $step = 5;
        } elseif (file_put_contents(INSTALL_LOCK, date('Y-m-d H:i:s')) === false) {
            $error = 'install.lock dosyası oluşturulamadı.';
            $step = 5;
        } else {
            $_SESSION['install']['finished'] = true;
            $success = 'Kurulum başarıyla tamamlandı!';
            $step = 5;
        }
    }
}

if ($step > 1 && empty($_SESSION['install']['requirements_ok'])) {
    install_redirect(1);
}
if ($step > 2 && empty($_SESSION['install']['db'])) {
    install_redirect(2);
}
if ($step > 3 && empty($_SESSION['install']['schema_done'])) {
    install_redirect(3);
}
if ($step > 4 && empty($_SESSION['install']['admin'])) {
    install_redirect(4);
}

switch ($step) {
    case 1:
        install_render_header(1, 'Sistem Gereksinimleri');
        $checks = install_check_requirements();
        $allPassed = install_requirements_passed($checks);

        if ($error !== '') {
            echo '<div class="alert alert-error">' . install_escape($error) . '</div>';
        }

        echo '<p>Aşağıdaki gereksinimlerin karşılandığından emin olun:</p>';
        echo '<ul class="check-list">';
        foreach ($checks as $check) {
            $badgeClass = $check['ok'] ? 'badge-ok' : 'badge-fail';
            $badgeText = $check['ok'] ? 'Tamam' : 'Eksik';
            echo '<li><div><strong>' . install_escape($check['label']) . '</strong>';
            echo '<div class="detail">' . install_escape($check['detail']) . '</div></div>';
            echo '<span class="badge ' . $badgeClass . '">' . $badgeText . '</span></li>';
        }
        echo '</ul>';

        echo '<form method="post"><input type="hidden" name="action" value="step1_continue">';
        echo '<div class="actions">';
        if ($allPassed) {
            echo '<button type="submit" class="btn btn-primary">Devam Et</button>';
        } else {
            echo '<button type="button" class="btn btn-secondary" disabled>Devam Et</button>';
        }
        echo '</div></form>';
        break;

    case 2:
        install_render_header(2, 'Veritabanı Bağlantısı');
        $db = $_SESSION['install']['db'] ?? [
            'host' => 'localhost',
            'name' => '',
            'user' => '',
            'pass' => '',
        ];

        if ($error !== '') {
            echo '<div class="alert alert-error">' . install_escape($error) . '</div>';
        }

        echo '<p>MySQL veritabanı bilgilerinizi girin. Veritabanının önceden oluşturulmuş olması gerekir.</p>';
        echo '<form method="post">';
        echo '<input type="hidden" name="action" value="step2_test">';
        echo '<div class="form-group"><label for="db_host">Sunucu (Host)</label>';
        echo '<input type="text" id="db_host" name="db_host" value="' . install_escape($db['host']) . '" required></div>';
        echo '<div class="form-group"><label for="db_name">Veritabanı Adı</label>';
        echo '<input type="text" id="db_name" name="db_name" value="' . install_escape($db['name']) . '" required></div>';
        echo '<div class="form-group"><label for="db_user">Kullanıcı Adı</label>';
        echo '<input type="text" id="db_user" name="db_user" value="' . install_escape($db['user']) . '" required></div>';
        echo '<div class="form-group"><label for="db_pass">Şifre</label>';
        echo '<input type="password" id="db_pass" name="db_pass" value="' . install_escape($db['pass']) . '"></div>';
        echo '<div class="actions"><button type="submit" class="btn btn-primary">Bağlantıyı Test Et</button></div>';
        echo '</form>';
        break;

    case 3:
        install_render_header(3, 'Veritabanı Kurulumu');
        if ($error !== '') {
            echo '<div class="alert alert-error">' . install_escape($error) . '</div>';
        }

        echo '<p>Veritabanı tabloları ve başlangıç verileri yüklenecek:</p>';
        echo '<ul class="check-list">';
        echo '<li><div><strong>database/schema.sql</strong><div class="detail">Tablo yapıları</div></div></li>';
        echo '<li><div><strong>database/seed.sql</strong><div class="detail">Varsayılan kategoriler ve ayarlar</div></div></li>';
        echo '</ul>';
        echo '<form method="post"><input type="hidden" name="action" value="step3_run">';
        echo '<div class="actions"><button type="submit" class="btn btn-primary">Veritabanını Kur</button></div>';
        echo '</form>';
        break;

    case 4:
        install_render_header(4, 'Yönetici Hesabı');
        if ($error !== '') {
            echo '<div class="alert alert-error">' . install_escape($error) . '</div>';
        }

        echo '<p>Forum yöneticisi hesabını oluşturun:</p>';
        echo '<form method="post">';
        echo '<input type="hidden" name="action" value="step4_create">';
        echo '<div class="form-group"><label for="username">Kullanıcı Adı</label>';
        echo '<input type="text" id="username" name="username" value="' . install_escape($_POST['username'] ?? '') . '" required></div>';
        echo '<div class="form-group"><label for="email">E-posta</label>';
        echo '<input type="email" id="email" name="email" value="' . install_escape($_POST['email'] ?? '') . '" required></div>';
        echo '<div class="form-group"><label for="password">Şifre</label>';
        echo '<input type="password" id="password" name="password" required></div>';
        echo '<div class="form-group"><label for="password_confirm">Şifre Tekrar</label>';
        echo '<input type="password" id="password_confirm" name="password_confirm" required></div>';
        echo '<div class="actions"><button type="submit" class="btn btn-primary">Hesabı Oluştur</button></div>';
        echo '</form>';
        break;

    case 5:
        install_render_header(5, 'Kurulumu Tamamla');

        if (!empty($_SESSION['install']['finished'])) {
            $admin = $_SESSION['install']['admin'];
            echo '<div class="success-icon">✓</div>';
            echo '<div class="alert alert-success">Kurulum başarıyla tamamlandı!</div>';
            echo '<ul class="check-list">';
            echo '<li><div><strong>config/database.php</strong><div class="detail">Veritabanı ayarları kaydedildi</div></div><span class="badge badge-ok">Tamam</span></li>';
            echo '<li><div><strong>storage/</strong><div class="detail">cache, logs, sessions dizinleri oluşturuldu</div></div><span class="badge badge-ok">Tamam</span></li>';
            echo '<li><div><strong>public/uploads/</strong><div class="detail">avatars ve attachments dizinleri oluşturuldu</div></div><span class="badge badge-ok">Tamam</span></li>';
            echo '<li><div><strong>Yönetici</strong><div class="detail">' . install_escape($admin['username']) . ' (' . install_escape($admin['email']) . ')</div></div><span class="badge badge-ok">Tamam</span></li>';
            echo '</ul>';
            echo '<div class="alert alert-info">Güvenlik için <strong>install.php</strong> dosyasını sunucudan silmeniz önerilir.</div>';
            echo '<div class="actions"><a href="index.php" class="btn btn-primary">Foruma Git</a></div>';
            unset($_SESSION['install']);
        } else {
            if ($error !== '') {
                echo '<div class="alert alert-error">' . install_escape($error) . '</div>';
            }

            echo '<p>Son adımda yapılandırma dosyaları ve dizinler oluşturulacak:</p>';
            echo '<ul class="check-list">';
            echo '<li><div><strong>config/database.php</strong><div class="detail">Veritabanı bağlantı ayarları</div></div></li>';
            echo '<li><div><strong>storage/</strong><div class="detail">cache, logs, sessions</div></div></li>';
            echo '<li><div><strong>public/uploads/</strong><div class="detail">avatars, attachments</div></div></li>';
            echo '<li><div><strong>storage/install.lock</strong><div class="detail">Kurulum kilidi</div></div></li>';
            echo '</ul>';
            echo '<form method="post"><input type="hidden" name="action" value="step5_finish">';
            echo '<div class="actions"><button type="submit" class="btn btn-primary">Kurulumu Tamamla</button></div>';
            echo '</form>';
        }
        break;
}

install_render_footer();
