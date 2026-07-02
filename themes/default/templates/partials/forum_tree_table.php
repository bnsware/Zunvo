<?php if (empty($forum_tree)): ?>
    <div class="empty-state">
        <h2>Henüz forum yok</h2>
        <p>Admin panelinden forum bölümleri oluşturabilirsiniz.</p>
    </div>
<?php else: ?>
    <div class="forum-table forum-table-compact">
        <div class="forum-table-head">
            <span class="forum-col-forum">Forum</span>
            <span class="forum-col-last">Son Mesaj</span>
            <span class="forum-col-stats">Konu / Mesaj</span>
        </div>
        <div class="forum-section-list">
            <?php foreach ($forum_tree as $section): ?>
                <?php
                $section_color = $section['color'] ?? '#0d9488';
                $child_count = count($section['children'] ?? []);
                ?>
                <div class="forum-section-block" style="--section-color: <?php echo escape($section_color); ?>">
                    <a href="<?php echo url('/kategori/' . $section['slug']); ?>" class="forum-section-head">
                        <span class="forum-section-icon"><?php echo category_icon($section['icon'] ?? 'layers'); ?></span>
                        <div class="forum-section-info">
                            <span class="forum-section-label">Bölüm</span>
                            <h3 class="forum-section-name"><?php echo escape($section['name']); ?></h3>
                            <?php if (!empty($section['description'])): ?>
                                <p class="forum-section-desc"><?php echo escape($section['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($child_count > 0): ?>
                            <span class="forum-section-count"><?php echo (int)$child_count; ?> alt forum</span>
                        <?php endif; ?>
                    </a>
                    <?php if (!empty($section['children'])): ?>
                        <div class="forum-section-body">
                            <?php foreach ($section['children'] as $forum): ?>
                                <div class="forum-row forum-row-child">
                                    <div class="forum-col-forum">
                                        <div class="forum-row-icon"><?php echo category_icon($forum['icon']); ?></div>
                                        <div class="forum-row-info">
                                            <a href="<?php echo url('/kategori/' . $forum['slug']); ?>" class="forum-row-title">
                                                <?php echo escape($forum['name']); ?>
                                            </a>
                                            <?php if (!empty($forum['description'])): ?>
                                                <p class="forum-row-desc"><?php echo escape($forum['description']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="forum-col-last">
                                        <?php if (!empty($forum['last_topic'])): ?>
                                            <a href="<?php echo url('/konu/' . $forum['last_topic']['slug']); ?>" class="forum-last-topic">
                                                <?php echo escape(mb_strimwidth($forum['last_topic']['title'], 0, 36, '...')); ?>
                                            </a>
                                            <span class="forum-last-meta">
                                                <?php echo escape($forum['last_topic']['username']); ?> &bull;
                                                <?php echo time_ago($forum['last_topic']['updated_at']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="forum-last-meta">Henüz konu yok</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="forum-col-stats">
                                        <span><?php echo format_number($forum['topic_count']); ?></span>
                                        <span class="forum-stat-sep">/</span>
                                        <span><?php echo format_number($forum['post_count']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="forum-section-body">
                            <div class="forum-row forum-row-empty">
                                <span>Bu bölümde alt forum yok</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
