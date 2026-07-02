<div class="admin-grid-2">
    <div class="admin-card">
        <div class="admin-card-header"><h2>Yeni Ödül</h2></div>
        <div class="admin-card-body">
            <form method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="add">
                <div class="admin-form-group">
                    <label>Ad</label>
                    <input type="text" name="name" class="admin-input" required>
                </div>
                <div class="admin-form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" class="admin-input" placeholder="otomatik">
                </div>
                <div class="admin-form-group">
                    <label>Açıklama</label>
                    <textarea name="description" class="admin-input"></textarea>
                </div>
                <div class="admin-form-group">
                    <label>Kriter</label>
                    <select name="criteria_type" class="admin-input">
                        <option value="manual">Manuel</option>
                        <option value="topic_count">Konu Sayısı</option>
                        <option value="reputation">İtibar</option>
                        <option value="solution_count">Çözüm Sayısı</option>
                        <option value="membership_days">Üyelik Günü</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label>Kriter Değeri</label>
                    <input type="number" name="criteria_value" class="admin-input" value="0">
                </div>
                <button type="submit" class="admin-btn admin-btn-primary">Ekle</button>
            </form>
        </div>
    </div>
    <div class="admin-card">
        <div class="admin-card-header"><h2>Ödüller</h2></div>
        <div class="admin-card-body admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Ad</th><th>Kriter</th><th>İşlem</th></tr></thead>
                <tbody>
                    <?php foreach ($awards as $award): ?>
                        <tr>
                            <td><?php echo escape($award['name']); ?></td>
                            <td><?php echo escape($award['criteria_type']); ?> (<?php echo (int)$award['criteria_value']; ?>)</td>
                            <td>
                                <form method="post" class="admin-inline-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="award_id" value="<?php echo (int)$award['id']; ?>">
                                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-outline">Sil</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="admin-card">
    <div class="admin-card-header"><h2>Manuel Ödül Ver</h2></div>
    <div class="admin-card-body">
        <form method="post" class="admin-inline-form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="grant">
            <select name="user_id" class="admin-input" required>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo (int)$u['id']; ?>"><?php echo escape($u['username']); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="award_id" class="admin-input" required>
                <?php foreach ($awards as $award): ?>
                    <option value="<?php echo (int)$award['id']; ?>"><?php echo escape($award['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="admin-btn admin-btn-primary">Ver</button>
        </form>
    </div>
</div>
