document.addEventListener('DOMContentLoaded', function() {
    var widget = document.getElementById('activity-widget');
    if (!widget) return;

    var baseUrl = widget.dataset.baseUrl;
    var body = document.getElementById('activity-widget-body');
    var loadMore = document.getElementById('activity-load-more');
    var currentTab = widget.querySelector('.activity-tab.active');
    var tab = currentTab ? currentTab.dataset.tab : 'recent';
    var page = 1;
    var loading = false;

    function escapeHtml(str) {
        var d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }

    function renderTopics(topics) {
        if (!topics.length) {
            return '<tr><td colspan="4" class="activity-empty">İçerik bulunamadı</td></tr>';
        }
        return topics.map(function(t) {
            var replies = Math.max(0, parseInt(t.reply_count, 10) || 0);
            var views = parseInt(t.views, 10) || 0;
            var avatar = window.ZUNVO_CONFIG && window.ZUNVO_CONFIG.baseUrl
                ? window.ZUNVO_CONFIG.baseUrl + '/public/uploads/avatars/' + (t.avatar || 'default.png')
                : '/public/uploads/avatars/' + (t.avatar || 'default.png');
            var topicUrl = window.ZUNVO_CONFIG ? window.ZUNVO_CONFIG.baseUrl + '/konu/' + t.slug : '/konu/' + t.slug;
            var catUrl = window.ZUNVO_CONFIG ? window.ZUNVO_CONFIG.baseUrl + '/kategori/' + t.category_slug : '/kategori/' + t.category_slug;
            return '<tr>' +
                '<td class="activity-col-title">' +
                    '<img src="' + escapeHtml(avatar) + '" alt="" class="activity-avatar" onerror="this.src=\'' + (window.ZUNVO_CONFIG ? window.ZUNVO_CONFIG.baseUrl : '') + '/public/images/default-avatar.png\'">' +
                    '<a href="' + escapeHtml(topicUrl) + '">' + escapeHtml(t.title) + '</a>' +
                '</td>' +
                '<td class="activity-col-stats">' + replies + ' <span class="activity-dot">&bull;</span> ' + views + '</td>' +
                '<td class="activity-col-last">' + escapeHtml(t.last_poster || t.username) + '</td>' +
                '<td class="activity-col-cat"><a href="' + escapeHtml(catUrl) + '">' + escapeHtml(t.category_name) + '</a></td>' +
            '</tr>';
        }).join('');
    }

    function loadTopics(reset) {
        if (loading) return;
        loading = true;
        if (reset) {
            page = 1;
            body.innerHTML = '<tr><td colspan="4" class="activity-loading">Yükleniyor...</td></tr>';
        }
        fetch(baseUrl + '?tab=' + encodeURIComponent(tab) + '&page=' + page, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) return;
            if (reset) {
                body.innerHTML = renderTopics(data.topics);
            } else {
                body.insertAdjacentHTML('beforeend', renderTopics(data.topics));
            }
            loadMore.style.display = data.has_more ? 'block' : 'none';
            page++;
        })
        .catch(function() {
            body.innerHTML = '<tr><td colspan="4" class="activity-empty">Yüklenemedi</td></tr>';
        })
        .finally(function() { loading = false; });
    }

    widget.querySelectorAll('.activity-tab').forEach(function(btn) {
        btn.addEventListener('click', function() {
            widget.querySelectorAll('.activity-tab').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            tab = btn.dataset.tab;
            loadTopics(true);
        });
    });

    if (loadMore) {
        loadMore.addEventListener('click', function() { loadTopics(false); });
    }

    loadTopics(true);
});
