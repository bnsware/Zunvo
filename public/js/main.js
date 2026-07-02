const ICON_PATHS = {
    'bell': '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
    'thumbs-up': '<path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>',
    'thumbs-down': '<path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17"/>',
    'mention': '<circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"/>',
    'reply': '<polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/>',
    'check': '<polyline points="20 6 9 17 4 12"/>',
    'user': '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'message': '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
    'alert': '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
    'loader': '<line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/>'
};

function iconSvg(name, className, size) {
    const body = ICON_PATHS[name] || ICON_PATHS['message'];
    const cls = className || 'icon';
    const sz = size || 20;
    return '<svg class="' + cls + '" width="' + sz + '" height="' + sz + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + body + '</svg>';
}

function voteButtonHtml(type, count) {
    const iconName = type === 'up' ? 'thumbs-up' : 'thumbs-down';
    return iconSvg(iconName, 'icon icon-sm') + '<span class="vote-count">' + count + '</span>';
}

document.addEventListener('DOMContentLoaded', function() {
    initThemeToggle();
    initFooterThemePicker();
    initUserMenu();
    initMobileNav();
    initVoteSystem();
    initReplyForm();
    initPostActions();
    initSolutionMarking();
    initNotifications();
    initSearchSuggestions();
});

function initThemeToggle() {
    const toggle = document.getElementById('theme-toggle');
    if (!toggle) return;

    toggle.addEventListener('click', function() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const next = isDark ? 'light' : 'dark';
        if (next === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }
        localStorage.setItem('zunvo-theme', next);
    });
}

function initFooterThemePicker() {
    const select = document.getElementById('footer-theme-select');
    if (!select) return;

    select.addEventListener('change', function() {
        const base = this.dataset.baseUrl || '';
        const redirect = encodeURIComponent(window.location.pathname + window.location.search);
        let target = base + '?redirect=' + redirect;
        if (this.value) {
            target += '&slug=' + encodeURIComponent(this.value);
        }
        window.location.href = target;
    });
}

function initUserMenu() {
    const trigger = document.getElementById('nav-user-trigger');
    const menu = document.getElementById('user-menu');
    if (!trigger || !menu) return;

    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        const isOpen = menu.classList.contains('show');
        menu.classList.toggle('show', !isOpen);
        trigger.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
        const notificationDropdown = document.getElementById('notification-dropdown');
        if (notificationDropdown) {
            notificationDropdown.classList.remove('show');
        }
    });

    document.addEventListener('click', function(e) {
        if (!menu.contains(e.target) && !trigger.contains(e.target)) {
            menu.classList.remove('show');
            trigger.setAttribute('aria-expanded', 'false');
        }
    });
}

function initMobileNav() {
    const toggle = document.getElementById('navbar-toggle');
    const menu = document.getElementById('navbar-menu');
    if (!toggle || !menu) return;
    toggle.addEventListener('click', function() {
        menu.classList.toggle('open');
    });
}

function initVoteSystem() {
    const voteButtons = document.querySelectorAll('.vote-btn');
    if (voteButtons.length === 0) return;

    voteButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const postId = parseInt(this.dataset.postId);
            const voteType = this.dataset.type;
            if (this.disabled) return;

            const originalHtml = this.innerHTML;
            this.disabled = true;
            this.innerHTML = iconSvg('loader', 'icon icon-sm icon-spin');

            try {
                const result = await VoteAPI.vote(postId, voteType);
                if (result.success) {
                    updateVoteButtons(postId, result.data);
                    Utils.showToast(result.data.message || 'Oyunuz kaydedildi', 'success');
                } else {
                    Utils.showToast(result.error || 'Bir hata oluştu', 'error');
                }
            } catch (error) {
                Utils.showToast(error.message || 'Bir hata oluştu', 'error');
            } finally {
                this.disabled = false;
                if (!this.classList.contains('active-up') && !this.classList.contains('active-down')) {
                    this.innerHTML = originalHtml;
                }
            }
        });
    });
}

function updateVoteButtons(postId, data) {
    const container = document.querySelector('[data-post-id="' + postId + '"]')?.closest('.post-footer');
    if (!container) return;

    const upButton = container.querySelector('.vote-btn[data-type="up"]');
    const downButton = container.querySelector('.vote-btn[data-type="down"]');

    if (upButton) {
        upButton.innerHTML = voteButtonHtml('up', data.upvotes);
        upButton.classList.toggle('active-up', data.user_vote === 'up');
    }

    if (downButton) {
        downButton.innerHTML = voteButtonHtml('down', data.downvotes);
        downButton.classList.toggle('active-down', data.user_vote === 'down');
    }
}

function initReplyForm() {
    const replyForm = document.getElementById('reply-form');
    if (!replyForm) return;

    replyForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitButton = this.querySelector('button[type="submit"]');
        const textarea = this.querySelector('.editor-bbcode-store') || this.querySelector('#reply-content') || this.querySelector('textarea[name="content"]') || this.querySelector('textarea[name="reply-content"]');
        const topicId = this.querySelector('input[name="topic_id"]').value;
        const content = textarea.value.trim();

        if (!content) {
            Utils.showToast('Lütfen bir yorum yazın', 'error');
            return;
        }

        if (content.length < 10) {
            Utils.showToast('Yorum en az 10 karakter olmalı', 'error');
            return;
        }

        Utils.showLoading(submitButton);

        try {
            const result = await PostAPI.create(topicId, content);
            if (result.success) {
                textarea.value = '';
                Utils.showToast('Yorumunuz eklendi!', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                Utils.showToast(result.error || 'Bir hata oluştu', 'error');
            }
        } catch (error) {
            Utils.showToast(error.message || 'Bir hata oluştu', 'error');
        } finally {
            Utils.hideLoading(submitButton);
        }
    });

    const textarea = replyForm.querySelector('textarea');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.classList.add('textarea-autosize');
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
}

function initPostActions() {
    document.querySelectorAll('.btn-edit-post').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const postContent = this.closest('.post-content-area').querySelector('.post-content');
            enablePostEditing(postId, postContent);
        });
    });

    document.querySelectorAll('.btn-delete-post').forEach(button => {
        button.addEventListener('click', async function() {
            if (!Utils.confirm('Bu gönderiyi silmek istediğinizden emin misiniz?')) {
                return;
            }

            const postId = this.dataset.postId;
            Utils.showLoading(this);

            try {
                const result = await PostAPI.delete(postId);
                if (result.success) {
                    Utils.showToast('Gönderi silindi', 'success');
                    const postElement = document.getElementById('post-' + postId);
                    if (postElement) {
                        postElement.classList.add('is-fading');
                        setTimeout(() => postElement.remove(), 300);
                    }
                } else {
                    Utils.showToast(result.error || 'Bir hata oluştu', 'error');
                }
            } catch (error) {
                Utils.showToast(error.message || 'Bir hata oluştu', 'error');
            } finally {
                Utils.hideLoading(this);
            }
        });
    });
}

function enablePostEditing(postId, contentElement) {
    const originalContent = contentElement.dataset.rawContent || '';

    const editorHost = document.createElement('div');
    editorHost.className = 'post-edit-editor-host';

    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'post-edit-actions';

    const saveButton = document.createElement('button');
    saveButton.className = 'btn btn-primary btn-small';
    saveButton.type = 'button';
    saveButton.textContent = 'Kaydet';

    const cancelButton = document.createElement('button');
    cancelButton.className = 'btn btn-outline btn-small';
    cancelButton.type = 'button';
    cancelButton.textContent = 'İptal';

    buttonContainer.appendChild(saveButton);
    buttonContainer.appendChild(cancelButton);

    contentElement.classList.add('is-hidden');
    contentElement.parentNode.insertBefore(editorHost, contentElement.nextSibling);
    contentElement.parentNode.insertBefore(buttonContainer, editorHost.nextSibling);

    const editorId = 'edit-post-' + postId;
    const editorApi = window.ZunvoEditor && window.ZunvoEditor.mountFromTemplate(
        'post-editor-template',
        editorId,
        originalContent,
        editorHost
    );

    cancelButton.addEventListener('click', function() {
        contentElement.classList.remove('is-hidden');
        editorHost.remove();
        buttonContainer.remove();
    });

    saveButton.addEventListener('click', async function() {
        const newContent = editorApi ? editorApi.getValue().trim() : '';
        if (!newContent || newContent.length < 10) {
            Utils.showToast('İçerik en az 10 karakter olmalı', 'error');
            return;
        }

        Utils.showLoading(this);

        try {
            const result = await PostAPI.update(postId, newContent);
            if (result.success) {
                contentElement.dataset.rawContent = newContent;
                if (result.data && result.data.html) {
                    contentElement.innerHTML = result.data.html;
                } else {
                    contentElement.textContent = newContent;
                }
                contentElement.classList.remove('is-hidden');
                editorHost.remove();
                buttonContainer.remove();
                Utils.showToast('Gönderi güncellendi', 'success');
            } else {
                Utils.showToast(result.error || 'Bir hata oluştu', 'error');
            }
        } catch (error) {
            Utils.showToast(error.message || 'Bir hata oluştu', 'error');
        } finally {
            Utils.hideLoading(this);
        }
    });

    if (editorApi) {
        editorApi.focus();
    }
}

function initSolutionMarking() {
    document.querySelectorAll('[data-mark-solution]').forEach(button => {
        button.addEventListener('click', async function() {
            if (!Utils.confirm('Bu gönderiyi çözüm olarak işaretlemek istiyor musunuz?')) {
                return;
            }

            const postId = this.dataset.markSolution;
            const topicId = this.dataset.topicId || document.querySelector('input[name="topic_id"]')?.value;

            Utils.showLoading(this);

            try {
                const result = await PostAPI.markSolution(postId, topicId);
                if (result.success) {
                    Utils.showToast('Çözüm olarak işaretlendi!', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    Utils.showToast(result.error || 'Bir hata oluştu', 'error');
                }
            } catch (error) {
                Utils.showToast(error.message || 'Bir hata oluştu', 'error');
            } finally {
                Utils.hideLoading(this);
            }
        });
    });
}

function initNotifications() {
    const notificationBell = document.getElementById('notification-bell');
    if (!notificationBell) return;

    updateNotificationCount();
    setInterval(updateNotificationCount, 30000);

    notificationBell.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('notification-dropdown');
        const userMenu = document.getElementById('user-menu');
        const userTrigger = document.getElementById('nav-user-trigger');
        if (userMenu) userMenu.classList.remove('show');
        if (userTrigger) userTrigger.setAttribute('aria-expanded', 'false');
        if (dropdown) {
            const wasShown = dropdown.classList.contains('show');
            document.querySelectorAll('.notification-dropdown.show').forEach(el => {
                if (el !== dropdown) el.classList.remove('show');
            });
            dropdown.classList.toggle('show');
            if (!wasShown && dropdown.classList.contains('show')) {
                loadNotifications();
                // #region agent log
                var bellRect = notificationBell.getBoundingClientRect();
                var dropRect = dropdown.getBoundingClientRect();
                fetch('http://127.0.0.1:7413/ingest/3ab16eef-dff9-4a32-bb37-cf4b77ade7a2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'8d0e9d'},body:JSON.stringify({sessionId:'8d0e9d',location:'main.js:initNotifications:dropdownOpen',message:'Dropdown position check',data:{bellRight:Math.round(bellRect.right),dropRight:Math.round(dropRect.right),delta:Math.round(dropRect.right-bellRect.right),parentIsBell:dropdown.parentElement===notificationBell},timestamp:Date.now(),hypothesisId:'A',runId:'post-fix'})}).catch(function(){});
                // #endregion
            }
        }
    });

    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown && !dropdown.contains(e.target) && !notificationBell.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    const markAllReadBtn = document.getElementById('mark-all-read');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            try {
                const result = await NotificationAPI.markAllRead();
                if (result.success) {
                    document.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                    });
                    const badge = document.getElementById('notification-badge');
                    if (badge) badge.classList.add('is-hidden');
                    Utils.showToast('Tüm bildirimler okundu', 'success');
                }
            } catch (error) {
                Utils.showToast('Hata oluştu', 'error');
            }
        });
    }
}

async function updateNotificationCount() {
    try {
        const result = await NotificationAPI.getUnreadCount();
        if (result.success) {
            const count = result.data.count;
            const badge = document.getElementById('notification-badge');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.classList.remove('is-hidden');
                } else {
                    badge.classList.add('is-hidden');
                }
            }
        }
    } catch (error) {
        console.error('Notification count error:', error);
    }
}

async function loadNotifications() {
    const container = document.getElementById('notification-list');
    if (!container) return;

    container.innerHTML = '<div class="notification-empty">Yükleniyor...</div>';

    try {
        const result = await NotificationAPI.get(true);
        if (result.success && result.data.notifications.length > 0) {
            container.innerHTML = '';
            result.data.notifications.forEach(notif => {
                container.appendChild(createNotificationItem(notif));
            });
        } else {
            container.innerHTML = '<div class="notification-empty">Yeni bildirim yok</div>';
        }
    } catch (error) {
        console.error('Load notifications error:', error);
        container.innerHTML = '<div class="notification-empty notification-error">Hata oluştu</div>';
    }
}

function createNotificationItem(notif) {
    const item = document.createElement('div');
    item.className = 'notification-item ' + (notif.is_read ? 'read' : 'unread');
    item.dataset.id = notif.id;

    const iconEl = document.createElement('div');
    iconEl.className = 'notification-icon';
    iconEl.dataset.icon = notif.icon || 'bell';
    iconEl.innerHTML = iconSvg(notif.icon || 'bell', 'icon');

    const content = document.createElement('div');
    content.className = 'notification-content';

    if (notif.link) {
        const link = document.createElement('a');
        link.href = notif.link;
        link.textContent = notif.message;
        content.appendChild(link);
    } else {
        const span = document.createElement('span');
        span.textContent = notif.message;
        content.appendChild(span);
    }

    const time = document.createElement('div');
    time.className = 'notification-time';
    time.textContent = notif.time_ago;
    content.appendChild(time);

    item.appendChild(iconEl);
    item.appendChild(content);

    item.addEventListener('click', async function(e) {
        if (!notif.is_read) {
            try {
                await NotificationAPI.markRead(notif.id);
                item.classList.remove('unread');
                item.classList.add('read');
                updateNotificationCount();
            } catch (error) {
                console.error('Mark read error:', error);
            }
        }
        if (notif.link && !e.target.closest('a')) {
            window.location.href = notif.link;
        }
    });

    return item;
}

function initSearchSuggestions() {
    const searchInput = document.querySelector('input[name="search"]');
    if (!searchInput) return;

    const debouncedSearch = Utils.debounce(async function(query) {
        if (query.length < 3) return;
    }, 300);

    searchInput.addEventListener('input', function() {
        debouncedSearch(this.value);
    });
}

document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) searchInput.focus();
    }

    if (e.key === 'Escape') {
        document.querySelectorAll('.dropdown.show, .notification-dropdown.show').forEach(el => {
            el.classList.remove('show');
        });
        const menu = document.getElementById('navbar-menu');
        if (menu) menu.classList.remove('open');
    }
});

window.addEventListener('scroll', function() {
    const scrollBtn = document.getElementById('scroll-to-top');
    if (scrollBtn) {
        scrollBtn.classList.toggle('is-visible', window.pageYOffset > 300);
    }
});

const scrollTopBtn = document.getElementById('scroll-to-top');
if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });

    document.querySelectorAll('img.lazy').forEach(img => {
        imageObserver.observe(img);
    });
}
