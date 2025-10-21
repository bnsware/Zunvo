<?php
/**
 * Zunvo Forum Sistemi
 * Database Helper Fonksiyonları
 * 
 * PDO için yardımcı fonksiyonlar ve wrapper'lar
 */

// Global PDO bağlantısı
$GLOBALS['db_connection'] = null;

/**
 * Veritabanı bağlantısını al (Singleton pattern)
 * @return PDO
 */
function get_db() {
    if ($GLOBALS['db_connection'] === null) {
        $GLOBALS['db_connection'] = create_database_connection();
    }
    return $GLOBALS['db_connection'];
}

/**
 * SELECT sorgusu çalıştır (tek satır)
 * @param string $query SQL sorgusu
 * @param array $params Parametreler
 * @return array|false Sonuç satırı veya false
 */
function db_query_row($query, $params = []) {
    try {
        $db = get_db();
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        log_error("Database Query Error: " . $e->getMessage());
        return false;
    }
}

/**
 * SELECT sorgusu çalıştır (çoklu satır)
 * @param string $query SQL sorgusu
 * @param array $params Parametreler
 * @return array Sonuç satırları
 */
function db_query_all($query, $params = []) {
    try {
        $db = get_db();
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        log_error("Database Query Error: " . $e->getMessage());
        return [];
    }
}

/**
 * INSERT/UPDATE/DELETE sorgusu çalıştır
 * @param string $query SQL sorgusu
 * @param array $params Parametreler
 * @return bool Başarı durumu
 */
function db_execute($query, $params = []) {
    try {
        $db = get_db();
        $stmt = $db->prepare($query);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        log_error("Database Execute Error: " . $e->getMessage());
        return false;
    }
}

/**
 * INSERT sorgusu ve son eklenen ID'yi döndür
 * @param string $query SQL sorgusu
 * @param array $params Parametreler
 * @return int|false Son eklenen ID veya false
 */
function db_insert($query, $params = []) {
    try {
        $db = get_db();
        $stmt = $db->prepare($query);
        if ($stmt->execute($params)) {
            return $db->lastInsertId();
        }
        return false;
    } catch (PDOException $e) {
        log_error("Database Insert Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Tek bir değer döndür (COUNT, SUM vb. için)
 * @param string $query SQL sorgusu
 * @param array $params Parametreler
 * @return mixed Değer veya false
 */
function db_query_value($query, $params = []) {
    try {
        $db = get_db();
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        log_error("Database Query Value Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Transaction başlat
 * @return bool
 */
function db_begin_transaction() {
    try {
        return get_db()->beginTransaction();
    } catch (PDOException $e) {
        log_error("Database Transaction Begin Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Transaction commit
 * @return bool
 */
function db_commit() {
    try {
        return get_db()->commit();
    } catch (PDOException $e) {
        log_error("Database Commit Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Transaction rollback
 * @return bool
 */
function db_rollback() {
    try {
        return get_db()->rollBack();
    } catch (PDOException $e) {
        log_error("Database Rollback Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Tablo var mı kontrol et
 * @param string $table_name Tablo adı
 * @return bool
 */
function db_table_exists($table_name) {
    $query = "SHOW TABLES LIKE ?";
    return db_query_row($query, [$table_name]) !== false;
}

/**
 * Sütun var mı kontrol et
 * @param string $table_name Tablo adı
 * @param string $column_name Sütun adı
 * @return bool
 */
function db_column_exists($table_name, $column_name) {
    $query = "SHOW COLUMNS FROM `{$table_name}` LIKE ?";
    return db_query_row($query, [$column_name]) !== false;
}

/**
 * Satır sayısını al
 * @param string $table Tablo adı
 * @param string $where WHERE koşulu (opsiyonel)
 * @param array $params Parametreler
 * @return int Satır sayısı
 */
function db_count($table, $where = '', $params = []) {
    $query = "SELECT COUNT(*) FROM `{$table}`";
    if (!empty($where)) {
        $query .= " WHERE {$where}";
    }
    $count = db_query_value($query, $params);
    return $count !== false ? (int)$count : 0;
}

/**
 * Son hatayı al
 * @return string|null Hata mesajı
 */
function db_last_error() {
    $error = get_db()->errorInfo();
    return isset($error[2]) ? $error[2] : null;
}

/**
 * Basit hata loglama fonksiyonu
 * @param string $message Hata mesajı
 */
function log_error($message) {
    if (defined('LOG_ENABLED') && LOG_ENABLED && defined('LOG_FILE')) {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[{$timestamp}] ERROR: {$message}\n";
        error_log($log_message, 3, LOG_FILE);
    }
    
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo "<div style='background:#ffcccc;padding:10px;border:1px solid #cc0000;margin:10px;'>";
        echo "<strong>Database Error:</strong> " . htmlspecialchars($message);
        echo "</div>";
    }
}
