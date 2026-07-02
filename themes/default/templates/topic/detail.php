<?php
$current_user = current_user();
$is_author = $current_user && $current_user['id'] === $topic['user_id'];
?>

<div class="breadcrumb">
    <a href="<?php echo url('/'); ?>">Ana Sayfa</a> /
    <a href="<?php echo url('/kategori/' . $topic['category_slug']); ?>">
        <?php echo escape($topic['category_name']); ?>
    </a> /
    <span><?php echo escape($topic['title']); ?></span>
</div>

<div class="topic-header">
    <div class="topic-title-area">
        <h1>
            <?php if ($topic['is_pinned']): ?><?php echo icon('pin', 'icon icon-sm'); ?> <?php endif; ?>
            <?php if ($topic['is_locked']): ?><?php echo icon('lock', 'icon icon-sm'); ?> <?php endif; ?>
            <?php echo escape($topic['title']); ?>
        </h1>

        <?php if ($is_author || is_moderator()): ?>
            <div class="topic-actions">
                <a href="<?php echo url('/konu/duzenle/' . $topic['slug']); ?>" class="btn btn-outline btn-small btn-with-icon">
                    <?php echo icon('edit', 'icon icon-sm'); ?> Düzenle
                </a>
            </div>
        <?php endif; ?>
        <?php if (is_moderator()): ?>
            <div class="topic-mod-actions">
                <form method="post" action="<?php echo url('/konu/' . $topic['slug'] . '/mod'); ?>" class="mod-action-form">
                    <?php echo csrf_field(); ?>
                    <?php if (can_mod('pin_topic')): ?>
                        <?php if ($topic['is_pinned']): ?>
                            <button type="submit" name="action" value="unpin" class="btn btn-outline btn-small">Sabiti Kaldır</button>
                        <?php else: ?>
                            <button type="submit" name="action" value="pin" class="btn btn-outline btn-small">Sabitle</button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (can_mod('lock_topic')): ?>
                        <?php if ($topic['is_locked']): ?>
                            <button type="submit" name="action" value="unlock" class="btn btn-outline btn-small">Kilidi Aç</button>
                        <?php else: ?>
                            <button type="submit" name="action" value="lock" class="btn btn-outline btn-small">Kilitle</button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (can_mod('delete_topic')): ?>
                        <button type="submit" name="action" value="delete" class="btn btn-outline btn-small btn-danger" onclick="return confirm('Konu silinsin mi?')">Sil</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="topic-info">
        <span><?php echo icon('eye', 'icon icon-sm'); ?> <?php echo format_number($topic['views']); ?> görüntülenme</span>
        <span><?php echo icon('message', 'icon icon-sm'); ?> <?php echo count($posts); ?> yanıt</span>
        <span><?php echo icon('clock', 'icon icon-sm'); ?> <?php echo time_ago($topic['created_at']); ?></span>
    </div>

    <?php if (!empty($tags)): ?>
        <div class="topic-tags">
            <?php foreach ($tags as $tag): ?>
                <a href="<?php echo url('/etiket/' . $tag['slug']); ?>" class="tag">
                    #<?php echo escape($tag['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="post-list">
    <?php foreach ($posts as $index => $post): ?>
        <div class="post-item <?php echo $post['is_solution'] ? 'solution' : ''; ?>" id="post-<?php echo $post['id']; ?>">
            <div class="post-sidebar">
                <img
                    src="<?php echo asset('uploads/avatars/' . $post['avatar']); ?>"
                    alt="<?php echo escape($post['username']); ?>"
                    class="post-avatar"
                    onerror="this.src='<?php echo asset('images/default-avatar.png'); ?>'"
                >
                <a href="<?php echo url('/profil/' . $post['username']); ?>" class="post-username">
                    <?php echo escape($post['username']); ?>
                </a>
                <div class="user-role">
                    <?php
                    $user = get_user_by_id($post['user_id']);
                    if ($user['role'] === 'admin') {
                        echo '<span class="role-badge">' . icon('shield', 'icon icon-sm') . ' Admin</span>';
                    } elseif ($user['role'] === 'moderator') {
                        echo '<span class="role-badge">' . icon('shield', 'icon icon-sm') . ' Moderatör</span>';
                    } else {
                        echo '<span class="role-badge">' . icon('user', 'icon icon-sm') . ' Üye</span>';
                    }
                    ?>
                </div>
                <div class="user-stats">
                    <div><?php echo icon('star', 'icon icon-sm'); ?> <?php echo $post['reputation']; ?> Reputasyon</div>
                    <div><?php echo icon('message', 'icon icon-sm'); ?> <?php echo $post['user_post_count']; ?> Gönderi</div>
                    <div><?php echo icon('calendar', 'icon icon-sm'); ?> <?php echo format_date($post['user_joined'], 'M Y'); ?></div>
                </div>
            </div>

            <div class="post-content-area">
                <div class="post-header">
                    <div class="post-date">
                        <?php echo format_date($post['created_at'], 'd.m.Y H:i'); ?>
                        <?php if ($post['updated_at'] && $post['updated_at'] !== $post['created_at']): ?>
                            <span class="post-edited">(düzenlendi)</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($current_user && ($current_user['id'] === $post['user_id'] || is_moderator())): ?>
                        <div class="post-actions">
                            <button type="button" class="btn btn-outline btn-small btn-edit-post btn-with-icon" data-post-id="<?php echo $post['id']; ?>">
                                <?php echo icon('edit', 'icon icon-sm'); ?> Düzenle
                            </button>
                            <button type="button" class="btn btn-outline btn-small btn-delete-post btn-with-icon" data-post-id="<?php echo $post['id']; ?>">
                                <?php echo icon('trash', 'icon icon-sm'); ?> Sil
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($post['is_solution']): ?>
                    <div class="solution-marker">
                        <span class="solution-badge"><?php echo icon('check', 'icon icon-sm'); ?> Çözüm</span>
                    </div>
                <?php endif; ?>

                <div class="post-content" data-raw-content="<?php echo escape($post['content']); ?>">
                    <?php echo parse_post_content($post['content']); ?>
                </div>

                <div class="post-footer">
                    <div class="vote-buttons">
                        <?php if ($current_user): ?>
                            <button class="vote-btn <?php echo isset($user_votes[$post['id']]) && $user_votes[$post['id']] === 'up' ? 'active-up' : ''; ?>"
                                    data-post-id="<?php echo $post['id']; ?>"
                                    data-type="up">
                                <?php echo icon('thumbs-up', 'icon icon-sm'); ?>
                                <span class="vote-count"><?php echo $post['upvotes']; ?></span>
                            </button>
                            <button class="vote-btn <?php echo isset($user_votes[$post['id']]) && $user_votes[$post['id']] === 'down' ? 'active-down' : ''; ?>"
                                    data-post-id="<?php echo $post['id']; ?>"
                                    data-type="down">
                                <?php echo icon('thumbs-down', 'icon icon-sm'); ?>
                                <span class="vote-count"><?php echo $post['downvotes']; ?></span>
                            </button>
                        <?php else: ?>
                            <span class="vote-display"><?php echo icon('thumbs-up', 'icon icon-sm'); ?> <?php echo $post['upvotes']; ?></span>
                            <span class="vote-display"><?php echo icon('thumbs-down', 'icon icon-sm'); ?> <?php echo $post['downvotes']; ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($index > 0 && $is_author && !$post['is_solution']): ?>
                        <button class="btn btn-primary btn-small btn-with-icon"
                                data-mark-solution="<?php echo $post['id']; ?>"
                                data-topic-id="<?php echo $topic['id']; ?>">
                            <?php echo icon('check', 'icon icon-sm'); ?> Çözüm Olarak İşaretle
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($pagination['total_pages'] > 1): ?>
    <div class="pagination mt-30">
        <?php if ($pagination['has_previous']): ?>
            <a href="<?php echo url('/konu/' . $topic['slug'] . '?page=' . ($pagination['current_page'] - 1)); ?>">
                Önceki
            </a>
        <?php endif; ?>

        <span class="active"><?php echo $pagination['current_page']; ?> / <?php echo $pagination['total_pages']; ?></span>

        <?php if ($pagination['has_next']): ?>
            <a href="<?php echo url('/konu/' . $topic['slug'] . '?page=' . ($pagination['current_page'] + 1)); ?>">
                Sonraki
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($current_user && !$topic['is_locked']): ?>
    <div class="reply-box">
        <h3>Yanıt Yaz</h3>
        <form id="reply-form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="topic_id" value="<?php echo $topic['id']; ?>">
            <?php echo bbcode_toolbar_html('reply-content', '', ['compact' => true, 'required' => true, 'minlength' => 10, 'placeholder' => 'Yanıtınızı buraya yazın... (@kullaniciadi ile bahsedebilirsiniz)', 'hint_id' => 'reply-content-hint']); ?>
            <div class="form-hint" id="reply-content-hint" data-default-text="En az 10 karakter. @ ile kullanıcı bahsedebilirsiniz.">
                En az 10 karakter. @ ile kullanıcı bahsedebilirsiniz.
            </div>
            <button type="submit" class="btn btn-primary btn-with-icon">
                <?php echo icon('send', 'icon icon-sm'); ?> Yanıt Gönder
            </button>
        </form>
    </div>
<?php elseif (!$current_user): ?>
    <div class="reply-box reply-box-center">
        <p>Yanıt yazmak için <a href="<?php echo url('/giris'); ?>">giriş yapın</a> veya
        <a href="<?php echo url('/kayit'); ?>">kayıt olun</a>.</p>
    </div>
<?php elseif ($topic['is_locked']): ?>
    <div class="reply-box reply-box-center">
        <p><?php echo icon('lock', 'icon icon-sm'); ?> Bu konu kilitlenmiştir. Yeni yanıt yazılamaz.</p>
    </div>
<?php endif; ?>

<template id="post-editor-template">
<?php echo bbcode_toolbar_html('post-edit-inline', '', ['compact' => true, 'required' => false, 'minlength' => 10, 'placeholder' => 'Gönderiyi düzenle... (@kullaniciadi)']); ?>
</template>
