<?php
$widget_tabs = get_homepage_widget_tabs();
$active_tab = $widget_tabs[0]['key'] ?? 'recent';
?>
<div class="activity-widget" id="activity-widget" data-base-url="<?php echo url('/home/widget'); ?>">
    <div class="activity-widget-tabs">
        <?php foreach ($widget_tabs as $tab): ?>
            <?php if (!empty($tab['login_required']) && !is_logged_in()) continue; ?>
            <button type="button"
                    class="activity-tab<?php echo $tab['key'] === $active_tab ? ' active' : ''; ?>"
                    data-tab="<?php echo escape($tab['key']); ?>">
                <?php echo escape($tab['label']); ?>
            </button>
        <?php endforeach; ?>
    </div>
    <div class="activity-widget-table-wrap">
        <table class="activity-widget-table">
            <thead>
                <tr>
                    <th>Konu Başlığı</th>
                    <th>Cevap / Görüntülenme</th>
                    <th>Son Cevap</th>
                    <th>Kategori</th>
                </tr>
            </thead>
            <tbody id="activity-widget-body">
                <tr><td colspan="4" class="activity-loading">Yükleniyor...</td></tr>
            </tbody>
        </table>
    </div>
    <button type="button" class="btn btn-outline activity-load-more" id="activity-load-more" style="display:none;">
        Daha Fazla İçerik Göster
    </button>
</div>
<script src="<?php echo asset('js/widget.js'); ?>" defer></script>
