<?php
$forum_tree = $forum_tree ?? [];
?>
<div class="page-header page-header-compact">
    <h1>Forumlar</h1>
    <p class="page-header-desc">Tüm bölüm ve alt forumlar</p>
</div>
<?php theme_partial('partials/forum_tree_table'); ?>
