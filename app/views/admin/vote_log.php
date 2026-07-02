<div class="page-header"><h1>Oy Logları</h1></div>
<table class="admin-table">
    <thead>
        <tr><th>Oy Veren</th><th>Gönderi Sahibi</th><th>Konu</th><th>Tip</th><th>Tarih</th></tr>
    </thead>
    <tbody>
        <?php foreach ($votes as $v): ?>
        <tr>
            <td><?php echo escape($v['voter_username']); ?></td>
            <td><?php echo escape($v['post_author_username']); ?></td>
            <td><?php echo escape($v['topic_title']); ?></td>
            <td>
                <span class="badge-with-icon">
                    <?php echo $v['vote_type'] === 'up' ? icon('thumbs-up', 'icon icon-sm') . ' Beğeni' : icon('thumbs-down', 'icon icon-sm') . ' Beğenmeme'; ?>
                </span>
            </td>
            <td><?php echo time_ago($v['created_at']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
