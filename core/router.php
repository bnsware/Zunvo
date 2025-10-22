<?php
/**
 * Zunvo Forum Sistemi
 * Router - URL Yönlendirme Sistemi
 * 
 * SEO dostu URL yapısı için routing
 */

/**
 * Mevcut URL'i parse et
 * @return array ['controller', 'action', 'params']
 */
function parse_url_route() {
    // URL'i al
    $request_uri = $_SERVER['REQUEST_URI'];
    
    // Query string'i kaldır
    $request_uri = strtok($request_uri, '?');
    
    // Site URL'ini kaldır
    $base_path = parse_url(SITE_URL, PHP_URL_PATH) ?? '/';
    $request_uri = substr($request_uri, strlen($base_path));
    
    // Başındaki ve sonundaki slash'leri temizle
    $request_uri = trim($request_uri, '/');
    
    // Boş ise ana sayfa
    if (empty($request_uri)) {
        return [
            'controller' => 'home',
            'action' => 'index',
            'params' => []
        ];
    }
    
    // URL'i parçala
    $parts = explode('/', $request_uri);
    
    // İlk parça controller
    $controller = $parts[0];
    
    // İkinci parça action veya ID
    $action = isset($parts[1]) ? $parts[1] : 'index';
    
    // Kalan parçalar parametreler
    $params = array_slice($parts, 2);
    
    return [
        'controller' => $controller,
        'action' => $action,
        'params' => $params
    ];
}

/**
 * Route'u işle ve ilgili controller'ı çalıştır
 */
function handle_route() {
    $route = parse_url_route();
    
    // Önce özel route'ları kontrol et
    $request_uri = $_SERVER['REQUEST_URI'];
    $base_path = parse_url(SITE_URL, PHP_URL_PATH) ?? '/';
    $request_uri = substr($request_uri, strlen($base_path));
    $request_uri = trim(strtok($request_uri, '?'), '/');
    
    $custom_route = match_custom_route($request_uri);
    if ($custom_route) {
        $route = $custom_route;
    }
    
    // Controller dosyasının yolu
    $controller_file = APP_PATH . '/controllers/' . $route['controller'] . '.php';
    
    // Controller dosyası var mı?
    if (!file_exists($controller_file)) {
        http_response_code(404);
        show_404_page();
        return;
    }
    
    // Controller'ı dahil et
    require_once $controller_file;
    
    // Controller fonksiyon adı
    $controller_function = $route['controller'] . '_' . $route['action'];
    
    // Fonksiyon var mı?
    if (!function_exists($controller_function)) {
        http_response_code(404);
        show_404_page();
        return;
    }
    
    // Fonksiyonu çalıştır
    call_user_func_array($controller_function, $route['params']);
}

/**
 * 404 Sayfası göster
 */
function show_404_page() {
    echo '<!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Sayfa Bulunamadı</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            .error-container {
                text-align: center;
            }
            .error-code {
                font-size: 120px;
                font-weight: bold;
                margin: 0;
            }
            .error-message {
                font-size: 24px;
                margin: 20px 0;
            }
            .error-link {
                color: white;
                text-decoration: none;
                border: 2px solid white;
                padding: 10px 30px;
                border-radius: 5px;
                display: inline-block;
                margin-top: 20px;
                transition: all 0.3s;
            }
            .error-link:hover {
                background: white;
                color: #667eea;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1 class="error-code">404</h1>
            <p class="error-message">Aradığınız sayfa bulunamadı!</p>
            <a href="' . url('/') . '" class="error-link">Ana Sayfaya Dön</a>
        </div>
    </body>
    </html>';
}

/**
 * Özel route tanımla
 * @param string $pattern URL pattern
 * @param string $controller Controller adı
 * @param string $action Action adı
 */
function add_route($pattern, $controller, $action) {
    if (!isset($GLOBALS['custom_routes'])) {
        $GLOBALS['custom_routes'] = [];
    }
    
    $GLOBALS['custom_routes'][$pattern] = [
        'controller' => $controller,
        'action' => $action
    ];
}

/**
 * Özel route'ları kontrol et
 * @param string $url Current URL
 * @return array|null Route bilgisi veya null
 */
function match_custom_route($url) {
    if (!isset($GLOBALS['custom_routes'])) {
        return null;
    }
    
    foreach ($GLOBALS['custom_routes'] as $pattern => $route) {
        // Pattern'i regex'e çevir
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^\/]+)', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        if (preg_match($pattern, $url, $matches)) {
            // Parametreleri al
            $params = array_filter($matches, function($key) {
                return !is_numeric($key);
            }, ARRAY_FILTER_USE_KEY);
            
            return [
                'controller' => $route['controller'],
                'action' => $route['action'],
                'params' => array_values($params)
            ];
        }
    }
    
    return null;
}

/**
 * Route URL'i oluştur
 * @param string $controller Controller adı
 * @param string $action Action adı
 * @param array $params Parametreler
 * @return string URL
 */
function route_url($controller, $action = 'index', $params = []) {
    $url = url($controller);
    
    if ($action !== 'index') {
        $url .= '/' . $action;
    }
    
    if (!empty($params)) {
        $url .= '/' . implode('/', $params);
    }
    
    return $url;
}

/**
 * View dosyasını yükle
 * @param string $view View dosya adı (layouts/header, topic/list vb.)
 * @param array $data View'a gönderilecek veri
 */
function load_view($view, $data = []) {
    // Veriyi değişkenlere çevir
    extract($data);
    
    // View dosyasının yolu
    $view_file = APP_PATH . '/views/' . $view . '.php';
    
    // Dosya var mı?
    if (!file_exists($view_file)) {
        die("View dosyası bulunamadı: {$view}");
    }
    
    // View'ı dahil et
    require $view_file;
}

/**
 * Layout ile view yükle
 * @param string $view View dosyası
 * @param array $data Veri
 * @param string $layout Layout dosyası (varsayılan: layout/master)
 */
function render($view, $data = [], $layout = 'layout/master') {
    // İçeriği buffer'a al
    ob_start();
    load_view($view, $data);
    $content = ob_get_clean();
    
    // Layout varsa layout'a gönder
    if ($layout) {
        $data['content'] = $content;
        load_view($layout, $data);
    } else {
        echo $content;
    }
}

/**
 * JSON response döndür
 * @param mixed $data Veri
 * @param int $status_code HTTP status code
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Hata response'u (JSON)
 * @param string $message Hata mesajı
 * @param int $status_code HTTP status code
 */
function error_response($message, $status_code = 400) {
    json_response([
        'success' => false,
        'error' => $message
    ], $status_code);
}

/**
 * Başarı response'u (JSON)
 * @param mixed $data Veri
 * @param string $message Mesaj
 */
function success_response($data = null, $message = 'İşlem başarılı') {
    json_response([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * GET parametresi al
 * @param string $key Parametre adı
 * @param mixed $default Varsayılan değer
 * @return mixed Parametre değeri
 */
function get_param($key, $default = null) {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

/**
 * POST parametresi al
 * @param string $key Parametre adı
 * @param mixed $default Varsayılan değer
 * @return mixed Parametre değeri
 */
function post_param($key, $default = null) {
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

/**
 * REQUEST parametresi al (GET veya POST)
 * @param string $key Parametre adı
 * @param mixed $default Varsayılan değer
 * @return mixed Parametre değeri
 */
function request_param($key, $default = null) {
    return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
}

/**
 * Tüm POST verilerini al
 * @return array POST veriler
 */
function post_data() {
    return $_POST;
}

/**
 * Request method'u al
 * @return string GET, POST, PUT, DELETE vb.
 */
function request_method() {
    return $_SERVER['REQUEST_METHOD'];
}

/**
 * AJAX request mi?
 * @return bool
 */
function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * POST request mi?
 * @return bool
 */
function is_post() {
    return request_method() === 'POST';
}

/**
 * GET request mi?
 * @return bool
 */
function is_get() {
    return request_method() === 'GET';
}