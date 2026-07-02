<?php $site_name = theme_shell_site_name(); ?>
    </div>
    <footer class="site-footer">
        <div class="site-footer-inner">
            <div class="site-footer-grid">
                <div>
                    <h3><?php echo escape($site_name); ?></h3>
                    <p>Modern ve kullanıcı dostu forum sistemi</p>
                </div>
                <div>
                    <h4>Hızlı Linkler</h4>
                    <ul>
                        <li><a href="<?php echo url('/'); ?>">Ana Sayfa</a></li>
                        <li><a href="<?php echo url('/kategoriler'); ?>">Kategoriler</a></li>
                        <li><a href="<?php echo url('/hakkimizda'); ?>">Hakkımızda</a></li>
                    </ul>
                </div>
                <div>
                    <h4>İletişim</h4>
                    <p>Forum Kuralları · Gizlilik Politikası</p>
                </div>
            </div>
            <div class="site-footer-bottom">
                <?php theme_shell_footer_picker(); ?>
                &copy; <?php echo date('Y'); ?> <?php echo escape($site_name); ?>. Tüm hakları saklıdır.
            </div>
        </div>
    </footer>
</body>
</html>
