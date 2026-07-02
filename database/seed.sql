INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Zunvo Forum'),
('site_description', 'Modern Forum Sistemi'),
('registration_enabled', '1'),
('posts_require_approval', '0'),
('homepage_widget_enabled', '1'),
('db_version', '2');

INSERT INTO categories (name, slug, description, icon, color, forum_type, order_num, parent_id) VALUES
('Genel Bölüm', 'genel-bolum', 'Genel tartışma alanı', 'folder', '#0d9488', 'section', 1, NULL);

SET @section_id = LAST_INSERT_ID();

INSERT INTO categories (name, slug, description, icon, color, forum_type, order_num, parent_id) VALUES
('Genel', 'genel', 'Genel tartışmalar', 'chat', '#0d9488', 'forum', 1, @section_id),
('Duyurular', 'duyurular', 'Resmi duyurular', 'megaphone', '#115e59', 'forum', 2, @section_id);

INSERT INTO categories (name, slug, description, icon, color, forum_type, order_num, parent_id) VALUES
('Teknoloji', 'teknoloji-bolum', 'Teknoloji ve yazılım', 'code', '#0f766e', 'section', 2, NULL);

SET @tech_id = LAST_INSERT_ID();

INSERT INTO categories (name, slug, description, icon, color, forum_type, order_num, parent_id) VALUES
('Yazılım', 'yazilim', 'Yazılım geliştirme', 'code', '#0f766e', 'forum', 1, @tech_id),
('Yardım', 'yardim', 'Sorular ve cevaplar', 'help', '#14b8a6', 'forum', 2, @tech_id);

INSERT INTO awards (slug, name, description, icon, criteria_type, criteria_value) VALUES
('first_topic', 'İlk Konu', 'İlk konunuzu açtınız', 'message', 'topic_count', 1),
('first_solution', 'İlk Çözüm', 'İlk çözümünüz işaretlendi', 'check', 'solution_count', 1),
('hundred_upvotes', '100 Beğeni', 'Gönderileriniz 100 beğeni aldı', 'thumbs-up', 'reputation', 100),
('veteran', 'Veteran', '500+ itibar puanı', 'star', 'reputation', 500),
('legend', 'Efsane', '1000+ itibar puanı', 'award', 'reputation', 1000);
