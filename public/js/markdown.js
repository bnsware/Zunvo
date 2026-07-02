document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.markdown-toolbar').forEach(function(toolbar) {
        var targetId = toolbar.dataset.target;
        var textarea = document.getElementById(targetId) || document.querySelector('[name="' + targetId + '"]');
        if (!textarea) return;
        var preview = document.createElement('div');
        preview.className = 'markdown-preview';
        preview.style.display = 'none';
        textarea.parentNode.insertBefore(preview, textarea.nextSibling);
        toolbar.querySelectorAll('button[data-md]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var action = btn.dataset.md;
                var start = textarea.selectionStart;
                var end = textarea.selectionEnd;
                var val = textarea.value;
                if (action === 'bold') {
                    textarea.value = val.substring(0, start) + '**' + val.substring(start, end) + '**' + val.substring(end);
                } else if (action === 'italic') {
                    textarea.value = val.substring(0, start) + '*' + val.substring(start, end) + '*' + val.substring(end);
                } else if (action === 'link') {
                    var url = prompt('URL:');
                    if (url) textarea.value = val.substring(0, start) + '[' + val.substring(start, end) + '](' + url + ')' + val.substring(end);
                } else if (action === 'code') {
                    textarea.value = val.substring(0, start) + '`' + val.substring(start, end) + '`' + val.substring(end);
                } else if (action === 'preview') {
                    if (preview.style.display === 'none') {
                        preview.innerHTML = simpleMarkdown(textarea.value);
                        preview.style.display = 'block';
                        textarea.style.display = 'none';
                        btn.textContent = 'Düzenle';
                    } else {
                        preview.style.display = 'none';
                        textarea.style.display = 'block';
                        btn.textContent = 'Önizleme';
                    }
                }
                textarea.focus();
            });
        });
    });
    var themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        var saved = localStorage.getItem('zunvo-theme');
        if (saved) document.documentElement.setAttribute('data-theme', saved);
        themeToggle.addEventListener('click', function() {
            var current = document.documentElement.getAttribute('data-theme');
            var next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('zunvo-theme', next);
        });
    }
});

function simpleMarkdown(text) {
    var html = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
    html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
    html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>');
    html = html.replace(/\n/g, '<br>');
    return html;
}
