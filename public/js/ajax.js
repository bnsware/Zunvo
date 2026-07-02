/**
 * Zunvo Forum Sistemi
 * AJAX Fonksiyonları
 * 
 * Tüm AJAX istekleri bu dosyadan yapılır
 */

// Base URL (config'den geliyor)
const BASE_URL = (() => {
    const cfg = window.ZUNVO_CONFIG || {};
    if (cfg.baseUrl) {
        return cfg.baseUrl.replace(/\/$/, '');
    }
    const basePath = cfg.basePath || '';
    return window.location.origin + basePath;
})();

// CSRF token'ı al
function getCsrfToken() {
    if (window.ZUNVO_CONFIG && window.ZUNVO_CONFIG.csrfToken) {
        return window.ZUNVO_CONFIG.csrfToken;
    }
    const tokenInput = document.querySelector('input[name="csrf_token"]');
    return tokenInput ? tokenInput.value : null;
}

// Generic AJAX request fonksiyonu
async function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    // POST/PUT/DELETE için data ekle
    if (data && method !== 'GET') {
        // CSRF token'ı ekle
        data.csrf_token = getCsrfToken();
        options.body = JSON.stringify(data);
    }
    
    // #region agent log
    fetch('http://127.0.0.1:7413/ingest/3ab16eef-dff9-4a32-bb37-cf4b77ade7a2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'8d0e9d'},body:JSON.stringify({sessionId:'8d0e9d',location:'ajax.js:ajaxRequest:start',message:'AJAX request',data:{url:url,method:method,baseUrl:(window.ZUNVO_CONFIG||{}).baseUrl,basePath:(window.ZUNVO_CONFIG||{}).basePath},timestamp:Date.now(),hypothesisId:'A'})}).catch(()=>{});
    // #endregion
    try {
        const response = await fetch(url, options);
        const contentType = response.headers.get('content-type') || '';
        const isJson = contentType.includes('application/json');
        const result = isJson ? await response.json() : null;
        
        if (!response.ok) {
            const message = (result && result.error) ? result.error : 'İstek başarısız oldu (' + response.status + ')';
            // #region agent log
            fetch('http://127.0.0.1:7413/ingest/3ab16eef-dff9-4a32-bb37-cf4b77ade7a2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'8d0e9d'},body:JSON.stringify({sessionId:'8d0e9d',location:'ajax.js:ajaxRequest:httpError',message:'AJAX HTTP error',data:{url:url,status:response.status,contentType:contentType,error:message},timestamp:Date.now(),hypothesisId:'B'})}).catch(()=>{});
            // #endregion
            throw new Error(message);
        }
        
        if (!isJson) {
            // #region agent log
            fetch('http://127.0.0.1:7413/ingest/3ab16eef-dff9-4a32-bb37-cf4b77ade7a2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'8d0e9d'},body:JSON.stringify({sessionId:'8d0e9d',location:'ajax.js:ajaxRequest:notJson',message:'Non-JSON response',data:{url:url,status:response.status,contentType:contentType},timestamp:Date.now(),hypothesisId:'C'})}).catch(()=>{});
            // #endregion
            throw new Error('Sunucu geçersiz yanıt döndürdü');
        }
        
        return result;
    } catch (error) {
        // #region agent log
        fetch('http://127.0.0.1:7413/ingest/3ab16eef-dff9-4a32-bb37-cf4b77ade7a2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'8d0e9d'},body:JSON.stringify({sessionId:'8d0e9d',location:'ajax.js:ajaxRequest:catch',message:'AJAX exception',data:{url:url,error:error.message},timestamp:Date.now(),hypothesisId:'D'})}).catch(()=>{});
        // #endregion
        console.error('AJAX Error:', error);
        throw error;
    }
}

// Vote sistemi için AJAX fonksiyonları
const VoteAPI = {
    /**
     * Post'a oy ver
     */
    vote: async function(postId, voteType) {
        return ajaxRequest(`${BASE_URL}/vote/submit`, 'POST', {
            post_id: postId,
            vote_type: voteType
        });
    },
    
    /**
     * Kullanıcının oyunu al
     */
    getUserVote: async function(postId) {
        return ajaxRequest(`${BASE_URL}/vote/get?post_id=${postId}`);
    },
    
    /**
     * Post istatistiklerini al
     */
    getStats: async function(postId) {
        return ajaxRequest(`${BASE_URL}/vote/stats?post_id=${postId}`);
    },
    
    /**
     * Toplu oyları al (sayfa yüklenirken)
     */
    getBatchVotes: async function(postIds) {
        return ajaxRequest(`${BASE_URL}/vote/batch`, 'POST', {
            post_ids: postIds
        });
    }
};

// Post işlemleri için AJAX fonksiyonları
const PostAPI = {
    /**
     * Yeni post ekle
     */
    create: async function(topicId, content) {
        return ajaxRequest(`${BASE_URL}/topic/add-post`, 'POST', {
            topic_id: topicId,
            content: content
        });
    },
    
    /**
     * Post düzenle
     */
    update: async function(postId, content) {
        return ajaxRequest(`${BASE_URL}/topic/edit-post`, 'POST', {
            post_id: postId,
            content: content
        });
    },
    
    /**
     * Post sil
     */
    delete: async function(postId) {
        return ajaxRequest(`${BASE_URL}/topic/delete-post`, 'POST', {
            post_id: postId
        });
    },
    
    /**
     * Çözüm olarak işaretle
     */
    markSolution: async function(postId, topicId) {
        return ajaxRequest(`${BASE_URL}/topic/mark-solution`, 'POST', {
            post_id: postId,
            topic_id: topicId
        });
    }
};

// Bildirim sistemi için AJAX fonksiyonları
const NotificationAPI = {
    /**
     * Bildirimleri al
     */
    get: async function(unreadOnly = false) {
        const url = `${BASE_URL}/notification/get${unreadOnly ? '?unread=1' : ''}`;
        return ajaxRequest(url);
    },
    
    /**
     * Bildirimi okundu olarak işaretle
     */
    markRead: async function(notificationId) {
        return ajaxRequest(`${BASE_URL}/notification/mark-read`, 'POST', {
            notification_id: notificationId
        });
    },
    
    /**
     * Tüm bildirimleri okundu işaretle
     */
    markAllRead: async function() {
        return ajaxRequest(`${BASE_URL}/notification/mark-all-read`, 'POST');
    },
    
    /**
     * Bildirimi sil
     */
    delete: async function(notificationId) {
        return ajaxRequest(`${BASE_URL}/notification/delete`, 'POST', {
            notification_id: notificationId
        });
    },
    
    /**
     * Tüm bildirimleri sil
     */
    deleteAll: async function() {
        return ajaxRequest(`${BASE_URL}/notification/delete-all`, 'POST');
    },
    
    /**
     * Okunmamış bildirim sayısını al
     */
    getUnreadCount: async function() {
        return ajaxRequest(`${BASE_URL}/notification/unread-count`);
    },
    
    /**
     * Bildirim polling (gerçek zamanlı güncelleme)
     */
    poll: async function(lastCheck = null) {
        const url = `${BASE_URL}/notification/poll${lastCheck ? '?last_check=' + lastCheck : ''}`;
        return ajaxRequest(url);
    }
};

// Kullanıcı işlemleri için AJAX fonksiyonları
const UserAPI = {
    /**
     * Kullanıcı takip et/takibi bırak
     */
    follow: async function(userId) {
        return ajaxRequest(`${BASE_URL}/user/follow`, 'POST', {
            user_id: userId
        });
    },
    
    /**
     * Kullanıcı engelle
     */
    block: async function(userId) {
        return ajaxRequest(`${BASE_URL}/user/block`, 'POST', {
            user_id: userId
        });
    },
    
    /**
     * Kullanıcı ara
     */
    search: async function(query) {
        return ajaxRequest(`${BASE_URL}/user/search?q=${encodeURIComponent(query)}`);
    }
};

// Yardımcı fonksiyonlar
const Utils = {
    /**
     * Toast bildirimi göster
     */
    showToast: function(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : '#dc3545'};
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },
    
    /**
     * Confirm dialog göster
     */
    confirm: function(message) {
        return confirm(message);
    },
    
    /**
     * Loading state göster
     */
    showLoading: function(element) {
        element.disabled = true;
        element.dataset.originalText = element.textContent;
        element.textContent = 'Yükleniyor...';
    },
    
    /**
     * Loading state'i kaldır
     */
    hideLoading: function(element) {
        element.disabled = false;
        element.textContent = element.dataset.originalText || element.textContent;
    },
    
    /**
     * Sayıyı formatla (1000 -> 1K)
     */
    formatNumber: function(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    },
    
    /**
     * Debounce fonksiyonu
     */
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// CSS animasyonlarını ekle
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Export (modül sistemi kullanılıyorsa)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        VoteAPI,
        PostAPI,
        NotificationAPI,
        UserAPI,
        Utils
    };
}