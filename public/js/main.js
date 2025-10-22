/**
 * Zunvo Forum Sistemi
 * Ana JavaScript Dosyası
 * 
 * Sayfa interaktivitesi ve event handler'lar
 */

// DOM yüklendiğinde çalıştır
document.addEventListener('DOMContentLoaded', function() {
    // Vote sistemini başlat
    initVoteSystem();
    
    // Reply formunu başlat
    initReplyForm();
    
    // Post düzenleme/silme butonlarını başlat
    initPostActions();
    
    // Çözüm işaretleme butonlarını başlat
    initSolutionMarking();
    
    // Bildirim sistemini başlat
    initNotifications();
    
    // Arama önerilerini başlat
    initSearchSuggestions();
});

/**
 * Vote sistemi
 */
function initVoteSystem() {
    const voteButtons = document.querySelectorAll('.vote-btn');
    
    if (voteButtons.length === 0) return;
    
    voteButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const postId = parseInt(this.dataset.postId);
            const voteType = this.dataset.type;
            
            // Disabled durumda ise işlem yapma
            if (this.disabled) return;
            
            // Loading state
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '⏳';
            
            try {
                const result = await VoteAPI.vote(postId, voteType);
                
                if (result.success) {
                    // Butonları güncelle
                    updateVoteButtons(postId, result.data);
                    
                    // Toast göster
                    Utils.showToast(result.data.message || 'Oyunuz kaydedildi', 'success');
                } else {
                    Utils.showToast(result.error || 'Bir hata oluştu', 'error');
                }
            } catch (error) {
                Utils.showToast(error.message || 'Bir hata oluştu', 'error');
            } finally {
                this.disabled = false;
                this.innerHTML = originalText;
            }
        });
    });
}

/**
 * Vote butonlarını güncelle
 */
function updateVoteButtons(postId, data) {
    const container = document.querySelector(`[data-post-id="${postId}"]`)?.closest('.post-footer');
    if (!container) return;
    
    const upButton = container.querySelector('.vote-btn[data-type="up"]');
    const downButton = container.querySelector('.vote-btn[data-type="down"]');
    
    if (upButton) {
        upButton.innerHTML = `👍 ${data.upvotes}`;
        upButton.classList.toggle('active-up', data.user_vote === 'up');
    }
    
    if (downButton) {
        downButton.innerHTML = `👎 ${data.downvotes}`;
        downButton.classList.toggle('active-down', data.user_vote === 'down');
    }
}

/**
 * Reply formu
 */
function initReplyForm() {
    const replyForm = document.getElementById('reply-form');
    
    if (!replyForm) return;
    
    replyForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitButton = this.querySelector('button[type="submit"]');
        const textarea = this.querySelector('textarea[name="content"]');
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
        
        // Loading state
        Utils.showLoading(submitButton);
        
        try {
            const result = await PostAPI.create(topicId, content);
            
            if (result.success) {
                // Formu temizle
                textarea.value = '';
                
                // Toast göster
                Utils.showToast('Yorumunuz eklendi!', 'success');
                
                // Sayfayı yenile (veya dinamik olarak post ekle)
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                Utils.showToast(result.error || 'Bir hata oluştu', 'error');
            }
        } catch (error) {
            Utils.showToast(error.message || 'Bir hata oluştu', 'error');
        } finally {
            Utils.hideLoading(submitButton);
        }
    });
    
    // Textarea auto-resize
    const textarea = replyForm.querySelector('textarea');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
}

/**
 * Post düzenleme/silme
 */
function initPostActions() {
    // Düzenleme butonları
    document.querySelectorAll('.btn-edit-post').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const postContent = this.closest('.post-content-area').querySelector('.post-content');
            
            // Düzenleme moduna geç
            enablePostEditing(postId, postContent);
        });
    });
    
    // Silme butonları
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
                    
                    // Post'u DOM'dan kaldır
                    const postElement = document.getElementById(`post-${postId}`);
                    if (postElement) {
                        postElement.style.opacity = '0';
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

/**
 * Post düzenlemeyi aktif et
 */
function enablePostEditing(postId, contentElement) {
    const originalContent = contentElement.textContent.trim();
    
    // Textarea oluştur
    const textarea = document.createElement('textarea');
    textarea.className = 'reply-textarea';
    textarea.value = originalContent;
    textarea.style.minHeight = '150px';
    
    // Butonlar
    const buttonContainer = document.createElement('div');
    buttonContainer.style.marginTop = '10px';
    buttonContainer.style.display = 'flex';
    buttonContainer.style.gap = '10px';
    
    const saveButton = document.createElement('button');
    saveButton.className = 'btn btn-primary btn-small';
    saveButton.textContent = 'Kaydet';
    
    const cancelButton = document.createElement('button');
    cancelButton.className = 'btn btn-outline btn-small';
    cancelButton.textContent = 'İptal';
    
    buttonContainer.appendChild(saveButton);
    buttonContainer.appendChild(cancelButton);
    
    // Orijinal içeriği gizle
    contentElement.style.display = 'none';
    contentElement.parentNode.insertBefore(textarea, contentElement.nextSibling);
    contentElement.parentNode.insertBefore(buttonContainer, textarea.nextSibling);
    
    // İptal butonu
    cancelButton.addEventListener('click', function() {
        contentElement.style.display = '';
        textarea.remove();
        buttonContainer.remove();
    });
    
    // Kaydet butonu
    saveButton.addEventListener('click', async function() {
        const newContent = textarea.value.trim();
        
        if (!newContent || newContent.length < 10) {
            Utils.showToast('İçerik en az 10 karakter olmalı', 'error');
            return;
        }
        
        Utils.showLoading(this);
        
        try {
            const result = await PostAPI.update(postId, newContent);
            
            if (result.success) {
                contentElement.textContent = newContent;
                contentElement.style.display = '';
                textarea.remove();
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
}

/**
 * Çözüm işaretleme
 */
function initSolutionMarking() {
    document.querySelectorAll('[data-mark-solution]').forEach(button => {
        button.addEventListener('click', async function() {
            if (!Utils.confirm('Bu gönderiyi çözüm olarak işaretlemek istiyor musunuz?')) {
                return;
            }
            
            const postId = this.dataset.markSolution;
            const topicId = this.dataset.topicId || 
                          document.querySelector('input[name="topic_id"]')?.value;
            
            Utils.showLoading(this);
            
            try {
                const result = await PostAPI.markSolution(postId, topicId);
                
                if (result.success) {
                    Utils.showToast('Çözüm olarak işaretlendi!', 'success');
                    
                    // Sayfayı yenile
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
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

/**
 * Bildirim sistemi (polling)
 */
function initNotifications() {
    const notificationBell = document.getElementById('notification-bell');
    
    if (!notificationBell) return;
    
    // İlk yükleme
    updateNotificationCount();
    
    // 30 saniyede bir güncelle
    setInterval(updateNotificationCount, 30000);
    
    // Bildirim dropdown'ı aç/kapat
    notificationBell.addEventListener('click', function() {
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown) {
            dropdown.classList.toggle('show');
            
            // Açıldığında bildirimleri yükle
            if (dropdown.classList.contains('show')) {
                loadNotifications();
            }
        }
    });
}

/**
 * Bildirim sayısını güncelle
 */
async function updateNotificationCount() {
    try {
        const result = await NotificationAPI.getUnreadCount();
        
        if (result.success && result.data.count > 0) {
            const badge = document.getElementById('notification-badge');
            if (badge) {
                badge.textContent = result.data.count;
                badge.style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Notification count error:', error);
    }
}

/**
 * Bildirimleri yükle
 */
async function loadNotifications() {
    const container = document.getElementById('notification-list');
    if (!container) return;
    
    container.innerHTML = '<div style="padding:20px;text-align:center;">Yükleniyor...</div>';
    
    try {
        const result = await NotificationAPI.get(true);
        
        if (result.success && result.data.notifications.length > 0) {
            container.innerHTML = result.data.notifications.map(notif => `
                <div class="notification-item ${notif.is_read ? 'read' : 'unread'}" 
                     data-id="${notif.id}">
                    <div class="notification-content">
                        ${notif.message}
                    </div>
                    <div class="notification-time">
                        ${notif.created_at}
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div style="padding:20px;text-align:center;color:#999;">Bildirim yok</div>';
        }
    } catch (error) {
        container.innerHTML = '<div style="padding:20px;text-align:center;color:#dc3545;">Hata oluştu</div>';
    }
}

/**
 * Arama önerileri (debounced)
 */
function initSearchSuggestions() {
    const searchInput = document.querySelector('input[name="search"]');
    
    if (!searchInput) return;
    
    const debouncedSearch = Utils.debounce(async function(query) {
        if (query.length < 3) return;
        
        // Burada arama API'si çağrılabilir
        console.log('Searching for:', query);
    }, 300);
    
    searchInput.addEventListener('input', function() {
        debouncedSearch(this.value);
    });
}

/**
 * Keyboard shortcuts
 */
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K - Arama focus
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) searchInput.focus();
    }
    
    // Escape - Modal/dropdown kapat
    if (e.key === 'Escape') {
        document.querySelectorAll('.dropdown.show').forEach(el => {
            el.classList.remove('show');
        });
    }
});

/**
 * Scroll to top button
 */
window.addEventListener('scroll', function() {
    const scrollBtn = document.getElementById('scroll-to-top');
    
    if (scrollBtn) {
        if (window.pageYOffset > 300) {
            scrollBtn.style.display = 'block';
        } else {
            scrollBtn.style.display = 'none';
        }
    }
});

// Scroll to top butonu varsa
const scrollTopBtn = document.getElementById('scroll-to-top');
if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Image lazy loading
 */
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

// Console'da logo göster
console.log('%c🎉 Zunvo Forum', 'color: #667eea; font-size: 24px; font-weight: bold;');
console.log('%cModern Forum Sistemi', 'color: #999; font-size: 14px;');