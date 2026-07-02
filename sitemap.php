<?php
require_once __DIR__ . '/config/config.php';
require_once CORE_PATH . '/functions.php';

redirect_to_install_if_needed();

require_once CORE_PATH . '/database.php';

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
$base = htmlspecialchars(rtrim(SITE_URL, '/'), ENT_XML1);
echo '<url><loc>' . $base . '/</loc><changefreq>daily</changefreq></url>';
if (file_exists(STORAGE_PATH . '/install.lock')) {
    $topics = db_query_all("SELECT slug, updated_at FROM topics ORDER BY updated_at DESC LIMIT 500");
    foreach ($topics as $t) {
        $loc = htmlspecialchars($base . '/konu/' . $t['slug'], ENT_XML1);
        echo '<url><loc>' . $loc . '</loc><lastmod>' . date('Y-m-d', strtotime($t['updated_at'])) . '</lastmod></url>';
    }
}
echo '</urlset>';
