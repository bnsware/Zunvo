<?php
/**
 * Zunvo Forum Sistemi
 * Master Layout
 * 
 * Tüm sayfalar bu layout'u kullanır
 */

// Header dahil et
require_once APP_PATH . '/views/layout/header.php';

// İçerik (view'dan gelen)
echo $content;

// Footer dahil et
require_once APP_PATH . '/views/layout/footer.php';