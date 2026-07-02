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
    $request_uri = get_request_uri_path();
    
    if ($request_uri === '') {
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
    
    $request_uri = get_request_uri_path();
    
    $custom_route = match_custom_route($request_uri);
    if ($custom_route) {
        $route = $custom_route;
    }
    
    // Controller dosyasının yolu
    $controller_file = APP_PATH . '/controllers/' . $route['controller'] . '.php';
    
    // Controller dosyası var mı?
    if (!file_exists($controller_file)) {
        if (is_ajax()) {
            // #region agent log
            @file_put_contents(ROOT_PATH . '/debug-8d0e9d.log', json_encode(['sessionId'=>'8d0e9d','location'=>'router.php:missing_controller','message'=>'AJAX 404 missing controller','data'=>['uri'=>$request_uri,'controller'=>$route['controller']],'timestamp'=>round(microtime(true)*1000),'hypothesisId'=>'E'])."\n", FILE_APPEND);
            // #endregion
            error_response('Sayfa bulunamadı', 404);
            return;
        }
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
        if (is_ajax()) {
            // #region agent log
            @file_put_contents(ROOT_PATH . '/debug-8d0e9d.log', json_encode(['sessionId'=>'8d0e9d','location'=>'router.php:missing_action','message'=>'AJAX 404 missing action','data'=>['uri'=>$request_uri,'function'=>$controller_function],'timestamp'=>round(microtime(true)*1000),'hypothesisId'=>'E'])."\n", FILE_APPEND);
            // #endregion
            error_response('Sayfa bulunamadı', 404);
            return;
        }
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
    if (function_exists('zunvo_set_view_vars')) {
        zunvo_set_view_vars($data);
    }
    extract($data);
    if (!function_exists('resolve_view_path')) {
        require_once APP_PATH . '/models/theme.php';
    }
    $view_file = resolve_view_path($view);
    if (!file_exists($view_file)) {
        die("View dosyası bulunamadı: {$view}");
    }
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
    if (is_ajax()) {
        // #region agent log
        @file_put_contents(ROOT_PATH . '/debug-8d0e9d.log', json_encode(['sessionId'=>'8d0e9d','location'=>'router.php:error_response','message'=>'AJAX error response','data'=>['status'=>$status_code,'error'=>$message,'uri'=>get_request_uri_path()],'timestamp'=>round(microtime(true)*1000),'hypothesisId'=>'B'])."\n", FILE_APPEND);
        // #endregion
    }
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

function get_json_body() {
    static $parsed = null;
    if ($parsed !== null) {
        return $parsed;
    }
    $content_type = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    if (stripos($content_type, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        $parsed = is_array($decoded) ? $decoded : [];
        return $parsed;
    }
    $parsed = $_POST;
    return $parsed;
}

function post_param($key, $default = null) {
    $data = get_json_body();
    return array_key_exists($key, $data) ? $data[$key] : $default;
}

function request_param($key, $default = null) {
    if (is_post()) {
        $value = post_param($key, null);
        if ($value !== null) {
            return $value;
        }
    }
    return get_param($key, $default);
}

function post_data() {
    return get_json_body();
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