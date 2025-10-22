<?php
/**
 * Zunvo Forum Sistemi
 * Footer Layout
 */
$site_name = get_setting('site_name', SITE_NAME);
$current_year = date('Y');
?>
    </div> <!-- container end -->
    
    <footer style="background: #2c3e50; color: white; margin-top: 50px; padding: 30px 0;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                <div>
                    <h3 style="margin-bottom: 15px;"><?php echo escape($site_name); ?></h3>
                    <p style="color: #bdc3c7;">Modern ve kullanıcı dostu forum sistemi</p>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 15px;">Hızlı Linkler</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 8px;">
                            <a href="<?php echo url('/'); ?>" style="color: #bdc3c7; text-decoration: none;">Ana Sayfa</a>
                        </li>
                        <li style="margin-bottom: 8px;">
                            <a href="<?php echo url('/kategoriler'); ?>" style="color: #bdc3c7; text-decoration: none;">Kategoriler</a>
                        </li>
                        <li style="margin-bottom: 8px;">
                            <a href="<?php echo url('/hakkimizda'); ?>" style="color: #bdc3c7; text-decoration: none;">Hakkımızda</a>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 15px;">İletişim</h4>
                    <p style="color: #bdc3c7;">
                        Email: info@zunvo.com<br>
                        Forum Kuralları<br>
                        Gizlilik Politikası
                    </p>
                </div>
            </div>
            
            <div style="border-top: 1px solid #34495e; margin-top: 30px; padding-top: 20px; text-align: center; color: #bdc3c7;">
                &copy; <?php echo $current_year; ?> <?php echo escape($site_name); ?>. Tüm hakları saklıdır.
            </div>
        </div>
    </footer>
    
    <script src="<?php echo asset('js/main.js'); ?>"></script>
</body>
</html>