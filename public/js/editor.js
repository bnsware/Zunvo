(function() {
    function updateCharHint(hintEl, length, minChars) {
        if (!hintEl) return;
        var defaultText = hintEl.dataset.defaultText || '';
        var prevState = hintEl.dataset.charState || '';
        var state;
        if (minChars > 0 && length < minChars) {
            hintEl.textContent = (minChars - length) + ' karakter daha (en az ' + minChars + ' karakter)';
            hintEl.className = 'form-hint form-hint-error';
            state = 'error';
        } else {
            hintEl.textContent = defaultText;
            hintEl.className = minChars > 0 ? 'form-hint form-hint-success' : 'form-hint';
            state = 'ok';
        }
        if (prevState !== state) {
            hintEl.dataset.charState = state;
            fetch('http://127.0.0.1:7413/ingest/8d0e9d2c-a7a0-4d13-bd59-4bc13327397f', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': '8d0e9d' }, body: JSON.stringify({ sessionId: '8d0e9d', location: 'editor.js:updateCharHint', message: 'char hint state', data: { hintId: hintEl.id, length: length, minChars: minChars, state: state }, timestamp: Date.now(), hypothesisId: 'char-hint' }) }).catch(function() {});
        }
    }

    function bindCharHintInput(input, minChars, hintId) {
        var hint = typeof hintId === 'string' ? document.getElementById(hintId) : hintId;
        if (!input || !hint) return;
        var run = function() {
            updateCharHint(hint, input.value.length, minChars);
        };
        input.addEventListener('input', run);
        run();
    }

    function syncEditorWrapCharHint(wrap, length) {
        if (!wrap || !wrap.dataset.hintId) return;
        var hint = document.getElementById(wrap.dataset.hintId);
        var min = parseInt(wrap.dataset.minlength, 10) || 0;
        updateCharHint(hint, length, min);
    }

    function serializeChildren(parent) {
        return Array.from(parent.childNodes).map(serializeNode).join('');
    }

    function serializeNode(node) {
        if (node.nodeType === Node.TEXT_NODE) {
            return node.textContent;
        }
        if (node.nodeType !== Node.ELEMENT_NODE) {
            return '';
        }
        var el = node;
        var tag = el.tagName.toLowerCase();
        var kids = function() { return serializeChildren(el); };

        if (tag === 'br') return '\n';
        if (tag === 'b' || tag === 'strong') return '[b]' + kids() + '[/b]';
        if (tag === 'i' || tag === 'em') return '[i]' + kids() + '[/i]';
        if (tag === 'u') return '[u]' + kids() + '[/u]';
        if (tag === 's' || tag === 'strike' || tag === 'del') return '[s]' + kids() + '[/s]';
        if (tag === 'a') {
            if (el.classList.contains('mention-link')) {
                var uname = el.dataset.username || el.textContent.replace(/^@/, '').trim();
                return '@' + uname;
            }
            var href = el.getAttribute('href') || '';
            var text = kids() || href;
            return '[url=' + href + ']' + text + '[/url]';
        }
        if (tag === 'img') {
            return '[img]' + (el.getAttribute('src') || '') + '[/img]';
        }
        if (tag === 'hr') return '[hr]\n';
        if (tag === 'blockquote' && el.classList.contains('bbcode-quote')) {
            var authorEl = el.querySelector('.bbcode-quote-author');
            var bodyEl = el.querySelector('.bbcode-quote-body');
            var author = '';
            if (authorEl) {
                author = authorEl.textContent.replace(/\s*yazdı:\s*$/i, '').trim();
            }
            var body = bodyEl ? serializeChildren(bodyEl) : kids();
            body = body.replace(/^\n+|\n+$/g, '');
            if (author && author !== 'Alıntı') {
                return '[quote=' + author + ']' + body + '[/quote]';
            }
            return '[quote]' + body + '[/quote]';
        }
        if (tag === 'pre' && el.classList.contains('bbcode-code')) {
            var codeEl = el.querySelector('code');
            return '[code]' + (codeEl ? codeEl.textContent : el.textContent) + '[/code]';
        }
        if (tag === 'span' && el.classList.contains('bbcode-spoiler')) {
            return '[spoiler]' + kids() + '[/spoiler]';
        }
        if (tag === 'div' && el.classList.contains('bbcode-center')) {
            return '[center]' + kids().trim() + '[/center]';
        }
        if (tag === 'div' && el.classList.contains('bbcode-youtube')) {
            var iframe = el.querySelector('iframe');
            var src = iframe ? (iframe.getAttribute('src') || '') : '';
            var match = src.match(/embed\/([^?&]+)/);
            return '[youtube]' + (match ? match[1] : '') + '[/youtube]';
        }
        if (tag === 'ul' && el.classList.contains('bbcode-list')) {
            var items = Array.from(el.querySelectorAll(':scope > li')).map(function(li) {
                return '[*]' + serializeChildren(li).trim();
            }).join('');
            return '[list]' + items + '[/list]';
        }
        if (tag === 'ol' && el.classList.contains('bbcode-list')) {
            var oitems = Array.from(el.querySelectorAll(':scope > li')).map(function(li) {
                return '[*]' + serializeChildren(li).trim();
            }).join('');
            return '[list=1]' + oitems + '[/list]';
        }
        if (tag === 'span') {
            var color = el.style.color;
            var size = el.style.fontSize;
            if (color) {
                return '[color=' + colorToHex(color) + ']' + kids() + '[/color]';
            }
            if (size) {
                return '[size=' + parseInt(size, 10) + ']' + kids() + '[/size]';
            }
        }
        if (tag === 'p' || tag === 'div') {
            var inner = kids();
            if (!inner.trim()) return '\n';
            return inner + '\n';
        }
        if (tag === 'li') return kids();
        return kids();
    }

    function colorToHex(color) {
        if (!color) return '#000000';
        if (color.charAt(0) === '#') return color;
        var m = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
        if (!m) return color;
        var hex = function(n) {
            var h = parseInt(n, 10).toString(16);
            return h.length === 1 ? '0' + h : h;
        };
        return '#' + hex(m[1]) + hex(m[2]) + hex(m[3]);
    }

    function htmlToBbcode(html) {
        var div = document.createElement('div');
        div.innerHTML = html;
        var bb = serializeChildren(div);
        bb = bb.replace(/\u00a0/g, ' ');
        bb = bb.replace(/\n{3,}/g, '\n\n');
        return bb.trim();
    }

    function youtubeId(input) {
        input = (input || '').trim();
        if (/^[a-zA-Z0-9_-]{11}$/.test(input)) return input;
        var m = input.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i);
        return m ? m[1] : null;
    }

    function initEditor(wrap) {
        var visual = wrap.querySelector('.editor-visual');
        var store = wrap.querySelector('.editor-bbcode-store');
        var preview = wrap.querySelector('.editor-preview');
        var sourceInput = wrap.querySelector('.editor-source-input');
        var popover = wrap.querySelector('.editor-popover');
        var mentionDropdown = wrap.querySelector('.editor-mention-dropdown');
        if (!visual || !store) return null;

        var currentTab = 'write';
        var syncTimer = null;
        var mentionTimer = null;
        var mentionActiveIndex = 0;
        var mentionUsers = [];
        var activePopoverBtn = null;
        var savedRange = null;

        function focusVisual() {
            visual.focus();
        }

        function selectionInsideVisual(range) {
            if (!range) return false;
            var node = range.commonAncestorContainer;
            return visual.contains(node.nodeType === Node.TEXT_NODE ? node.parentNode : node);
        }

        function saveSelection() {
            var sel = window.getSelection();
            if (!sel.rangeCount) return;
            var range = sel.getRangeAt(0);
            if (!selectionInsideVisual(range)) return;
            savedRange = range.cloneRange();
        }

        function restoreSelection() {
            focusVisual();
            if (!savedRange) return false;
            var sel = window.getSelection();
            sel.removeAllRanges();
            try {
                sel.addRange(savedRange);
                return !sel.isCollapsed || savedRange.toString().length === 0;
            } catch (err) {
                savedRange = null;
                return false;
            }
        }

        function getSavedText() {
            return savedRange ? savedRange.toString() : '';
        }

        function syncToStore(bbcode) {
            var value = bbcode !== undefined ? bbcode : htmlToBbcode(visual.innerHTML);
            store.value = value;
            store.dispatchEvent(new Event('input', { bubbles: true }));
            syncEditorWrapCharHint(wrap, value.length);
            return value;
        }

        function debouncedSync() {
            clearTimeout(syncTimer);
            syncTimer = setTimeout(function() {
                syncToStore();
            }, 120);
        }

        function fetchBbcodeHtml(bbcode) {
            var base = window.ZUNVO_CONFIG ? window.ZUNVO_CONFIG.baseUrl : '';
            return fetch(base + '/api/v1/preview-bbcode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': window.ZUNVO_CONFIG ? window.ZUNVO_CONFIG.csrfToken : ''
                },
                body: JSON.stringify({ content: bbcode })
            }).then(function(r) { return r.json(); });
        }

        function loadVisualFromBbcode(bbcode) {
            if (!bbcode || !bbcode.trim()) {
                visual.innerHTML = '';
                return Promise.resolve();
            }
            return fetchBbcodeHtml(bbcode).then(function(data) {
                if (data.success) {
                    visual.innerHTML = data.html;
                    visual.querySelectorAll('.bbcode-spoiler').forEach(function(el) {
                        el.classList.add('revealed');
                    });
                }
            });
        }

        function hideMentionDropdown() {
            if (!mentionDropdown) return;
            mentionDropdown.hidden = true;
            mentionDropdown.innerHTML = '';
            mentionUsers = [];
            mentionActiveIndex = 0;
        }

        function getTextBeforeCursor() {
            var sel = window.getSelection();
            if (!sel.rangeCount) return { text: '', range: null };
            var range = sel.getRangeAt(0);
            var pre = range.cloneRange();
            pre.selectNodeContents(visual);
            pre.setEnd(range.endContainer, range.endOffset);
            return { text: pre.toString(), range: range };
        }

        function renderMentionDropdown(users) {
            if (!mentionDropdown) return;
            mentionUsers = users;
            mentionActiveIndex = 0;
            if (!users.length) {
                hideMentionDropdown();
                return;
            }
            mentionDropdown.innerHTML = users.map(function(user, index) {
                return '<button type="button" class="editor-mention-item' + (index === 0 ? ' is-active' : '') + '" data-username="' + user.username + '">' +
                    '<img src="' + user.avatar_url + '" alt="" onerror="this.style.display=\'none\'">' +
                    '<span>@' + user.username + '</span></button>';
            }).join('');
            mentionDropdown.hidden = false;
            mentionDropdown.querySelectorAll('.editor-mention-item').forEach(function(btn) {
                btn.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    insertMention(btn.dataset.username);
                });
            });
        }

        function insertMention(username) {
            var info = getTextBeforeCursor();
            var match = info.text.match(/@([a-zA-Z0-9_-]*)$/);
            if (!match || !info.range) {
                document.execCommand('insertText', false, '@' + username + ' ');
            } else {
                var range = info.range;
                var node = range.endContainer;
                if (node.nodeType === Node.TEXT_NODE) {
                    var startOffset = range.endOffset - match[0].length;
                    if (startOffset >= 0) {
                        var text = node.textContent;
                        node.textContent = text.substring(0, startOffset) + '@' + username + ' ' + text.substring(range.endOffset);
                        var nr = document.createRange();
                        var pos = startOffset + username.length + 2;
                        nr.setStart(node, Math.min(pos, node.textContent.length));
                        nr.collapse(true);
                        var sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(nr);
                    } else {
                        document.execCommand('insertText', false, '@' + username + ' ');
                    }
                } else {
                    document.execCommand('insertText', false, '@' + username + ' ');
                }
            }
            hideMentionDropdown();
            debouncedSync();
            focusVisual();
        }

        function checkMention() {
            if (!mentionDropdown || typeof UserAPI === 'undefined') return;
            var info = getTextBeforeCursor();
            var match = info.text.match(/@([a-zA-Z0-9_-]*)$/);
            if (!match) {
                hideMentionDropdown();
                return;
            }
            clearTimeout(mentionTimer);
            mentionTimer = setTimeout(function() {
                UserAPI.search(match[1]).then(function(res) {
                    if (res.success) {
                        renderMentionDropdown(res.data.users || []);
                    }
                }).catch(function() {
                    hideMentionDropdown();
                });
            }, 180);
        }

        function setTab(tab) {
            if (tab === currentTab) return;

            if (currentTab === 'source' && tab === 'write') {
                loadVisualFromBbcode(sourceInput.value).then(function() {
                    syncToStore(sourceInput.value);
                });
            } else if (currentTab === 'write' && tab !== 'write') {
                syncToStore();
            } else if (currentTab === 'source' && tab === 'preview') {
                syncToStore(sourceInput.value);
            }

            currentTab = tab;
            wrap.querySelectorAll('button[data-tab]').forEach(function(b) {
                b.classList.toggle('active', b.dataset.tab === tab);
            });

            visual.hidden = tab !== 'write';
            preview.hidden = tab !== 'preview';
            sourceInput.hidden = tab !== 'source';

            if (tab === 'preview') {
                var bb = store.value;
                preview.innerHTML = '<div class="editor-preview-loading">Yükleniyor...</div>';
                fetchBbcodeHtml(bb).then(function(data) {
                    preview.innerHTML = data.success ? data.html : '<p>Önizleme yüklenemedi</p>';
                });
            }
            if (tab === 'source') {
                sourceInput.value = store.value;
            }
            if (tab === 'write') {
                focusVisual();
            }
            closePopover();
            hideMentionDropdown();
        }

        function closePopover() {
            if (!popover) return;
            popover.hidden = true;
            popover.querySelectorAll('.editor-popover-panel').forEach(function(p) {
                p.hidden = true;
            });
            if (activePopoverBtn) {
                activePopoverBtn.classList.remove('is-active');
                activePopoverBtn = null;
            }
        }

        function openPopover(type, btn) {
            if (!popover) return;
            closePopover();
            var panel = popover.querySelector('.editor-popover-panel[data-popover="' + type + '"]');
            if (!panel) return;
            activePopoverBtn = btn;
            btn.classList.add('is-active');
            panel.hidden = false;
            popover.hidden = false;
            panel.querySelectorAll('.editor-popover-input').forEach(function(inp) {
                inp.value = '';
            });
            var rect = btn.getBoundingClientRect();
            var wrapRect = wrap.getBoundingClientRect();
            popover.style.top = (rect.bottom - wrapRect.top + 6) + 'px';
            popover.style.left = Math.max(0, rect.left - wrapRect.left) + 'px';
            var firstInput = panel.querySelector('.editor-popover-input');
            if (type === 'url') {
                var textField = panel.querySelector('[data-field="text"]');
                if (textField) {
                    textField.value = getSavedText();
                }
            }
            if (firstInput) {
                setTimeout(function() { firstInput.focus(); }, 50);
            }
        }

        function exec(cmd, val) {
            restoreSelection();
            document.execCommand(cmd, false, val || null);
            saveSelection();
            debouncedSync();
        }

        function insertHtml(html) {
            restoreSelection();
            document.execCommand('insertHTML', false, html);
            saveSelection();
            debouncedSync();
        }

        function wrapLink(href, label) {
            if (!href) return;
            restoreSelection();
            var sel = window.getSelection();
            if (!sel.rangeCount) return;
            var range = sel.getRangeAt(0);
            var a = document.createElement('a');
            a.href = href;
            a.rel = 'nofollow noopener';
            a.target = '_blank';
            if (!range.collapsed) {
                try {
                    range.surroundContents(a);
                } catch (e) {
                    var frag = range.extractContents();
                    a.appendChild(frag);
                    range.insertNode(a);
                }
            } else {
                a.textContent = label || href;
                range.insertNode(a);
                range.setStartAfter(a);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
            }
            saveSelection();
            debouncedSync();
        }

        function wrapRange(tag, className, style) {
            var hadSaved = !!savedRange;
            var savedLen = savedRange ? savedRange.toString().length : 0;
            if (!restoreSelection()) {
                // #region agent log
                fetch('http://127.0.0.1:7413/ingest/3ab16eef-dff9-4a32-bb37-cf4b77ade7a2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'8d0e9d'},body:JSON.stringify({sessionId:'8d0e9d',location:'editor.js:wrapRange',message:'wrapRange aborted',data:{hadSaved:hadSaved,savedLen:savedLen,tag:tag},timestamp:Date.now(),hypothesisId:'A'})}).catch(function(){});
                // #endregion
                return;
            }
            var sel = window.getSelection();
            if (!sel.rangeCount) return;
            var range = sel.getRangeAt(0);
            if (range.collapsed) {
                // #region agent log
                fetch('http://127.0.0.1:7413/ingest/3ab16eef-dff9-4a32-bb37-cf4b77ade7a2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'8d0e9d'},body:JSON.stringify({sessionId:'8d0e9d',location:'editor.js:wrapRange',message:'collapsed range',data:{hadSaved:hadSaved,savedLen:savedLen,tag:tag},timestamp:Date.now(),hypothesisId:'A'})}).catch(function(){});
                // #endregion
                return;
            }
            var el = document.createElement(tag);
            if (className) el.className = className;
            if (style) {
                Object.keys(style).forEach(function(k) { el.style[k] = style[k]; });
            }
            try {
                range.surroundContents(el);
            } catch (e) {
                var frag = range.extractContents();
                el.appendChild(frag);
                range.insertNode(el);
            }
            sel.removeAllRanges();
            var nr = document.createRange();
            nr.selectNodeContents(el);
            nr.collapse(false);
            sel.addRange(nr);
            savedRange = nr.cloneRange();
            debouncedSync();
        }

        function toggleInlineFormat(cmd) {
            restoreSelection();
            document.execCommand(cmd, false, null);
            saveSelection();
            debouncedSync();
        }

        wrap.querySelectorAll('button[data-bb]').forEach(function(btn) {
            btn.addEventListener('mousedown', function(e) {
                e.preventDefault();
                saveSelection();
            });
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var action = btn.dataset.bb;
                var popType = btn.dataset.popover;

                if (popType) {
                    openPopover(popType, btn);
                    return;
                }

                closePopover();

                if (action === 'bold') toggleInlineFormat('bold');
                else if (action === 'italic') toggleInlineFormat('italic');
                else if (action === 'underline') toggleInlineFormat('underline');
                else if (action === 'strike') toggleInlineFormat('strikeThrough');
                else if (action === 'code') {
                    insertHtml('<pre class="bbcode-code"><code>kod</code></pre><p><br></p>');
                } else if (action === 'spoiler') {
                    wrapRange('span', 'bbcode-spoiler revealed', null);
                } else if (action === 'list') {
                    exec('insertUnorderedList');
                    var lists = visual.querySelectorAll('ul:not(.bbcode-list)');
                    lists.forEach(function(ul) { ul.classList.add('bbcode-list'); });
                    debouncedSync();
                } else if (action === 'center') {
                    var sel = window.getSelection();
                    if (sel.rangeCount && !sel.isCollapsed) {
                        wrapRange('div', 'bbcode-center', null);
                    } else {
                        insertHtml('<div class="bbcode-center"><br></div>');
                    }
                } else if (action === 'hr') {
                    insertHtml('<hr class="bbcode-hr"><p><br></p>');
                }
            });
        });

        if (popover) {
            popover.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            popover.querySelectorAll('.editor-popover-cancel').forEach(function(btn) {
                btn.addEventListener('click', closePopover);
            });

            popover.querySelector('.editor-popover-apply[data-apply="url"]')?.addEventListener('click', function() {
                var panel = popover.querySelector('[data-popover="url"]');
                var url = panel.querySelector('[data-field="url"]').value.trim();
                var text = panel.querySelector('[data-field="text"]').value.trim();
                if (!url) return;
                if (!/^https?:\/\//i.test(url) && !/^www\./i.test(url)) {
                    url = 'https://' + url;
                }
                restoreSelection();
                var sel = window.getSelection();
                if (sel.rangeCount && !sel.isCollapsed) {
                    wrapLink(url);
                } else {
                    wrapLink(url, text || url);
                }
                closePopover();
            });

            popover.querySelector('.editor-popover-apply[data-apply="img"]')?.addEventListener('click', function() {
                var panel = popover.querySelector('[data-popover="img"]');
                var url = panel.querySelector('[data-field="url"]').value.trim();
                if (!url) return;
                insertHtml('<img src="' + url.replace(/"/g, '&quot;') + '" class="bbcode-image" alt=""><p><br></p>');
                closePopover();
            });

            popover.querySelector('.editor-popover-apply[data-apply="quote"]')?.addEventListener('click', function() {
                var panel = popover.querySelector('[data-popover="quote"]');
                var user = panel.querySelector('[data-field="user"]').value.trim();
                var author = user ? user + ' yazdı:' : 'Alıntı';
                var html = '<blockquote class="bbcode-quote"><div class="bbcode-quote-author">' +
                    author.replace(/</g, '&lt;') +
                    '</div><div class="bbcode-quote-body"><br></div></blockquote><p><br></p>';
                insertHtml(html);
                closePopover();
            });

            popover.querySelector('.editor-popover-apply[data-apply="color"]')?.addEventListener('click', function() {
                var panel = popover.querySelector('[data-popover="color"]');
                var hex = panel.querySelector('[data-field="hex"]').value.trim();
                if (!hex) return;
                if (hex.charAt(0) !== '#') hex = '#' + hex;
                wrapRange('span', '', { color: hex });
                closePopover();
            });

            popover.querySelectorAll('.editor-color-swatch').forEach(function(sw) {
                sw.addEventListener('mousedown', function(e) { e.preventDefault(); });
                sw.addEventListener('click', function() {
                    wrapRange('span', '', { color: sw.dataset.color });
                    closePopover();
                });
            });

            popover.querySelectorAll('.editor-size-btn').forEach(function(sw) {
                sw.addEventListener('mousedown', function(e) { e.preventDefault(); });
                sw.addEventListener('click', function() {
                    wrapRange('span', '', { fontSize: sw.dataset.size + 'px' });
                    closePopover();
                });
            });

            popover.querySelector('.editor-popover-apply[data-apply="youtube"]')?.addEventListener('click', function() {
                var panel = popover.querySelector('[data-popover="youtube"]');
                var url = panel.querySelector('[data-field="url"]').value.trim();
                var id = youtubeId(url);
                if (!id) return;
                insertHtml('<div class="bbcode-youtube"><iframe src="https://www.youtube.com/embed/' + id + '" allowfullscreen loading="lazy" title="YouTube"></iframe></div><p><br></p>');
                closePopover();
            });
        }

        document.addEventListener('click', function(e) {
            if (!popover || popover.hidden) return;
            if (popover.contains(e.target) || e.target.closest('[data-popover]')) return;
            closePopover();
        });

        wrap.querySelectorAll('button[data-tab]').forEach(function(tabBtn) {
            tabBtn.addEventListener('click', function() {
                setTab(tabBtn.dataset.tab);
            });
        });

        visual.addEventListener('input', function() {
            debouncedSync();
            checkMention();
        });
        visual.addEventListener('mouseup', saveSelection);
        visual.addEventListener('keyup', saveSelection);
        visual.addEventListener('blur', function() {
            setTimeout(function() { hideMentionDropdown(); }, 150);
            syncToStore();
        });
        visual.addEventListener('keydown', function(e) {
            if (!mentionDropdown || mentionDropdown.hidden) return;
            if (e.key === 'Escape') {
                hideMentionDropdown();
                e.preventDefault();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                mentionActiveIndex = Math.min(mentionActiveIndex + 1, mentionUsers.length - 1);
                mentionDropdown.querySelectorAll('.editor-mention-item').forEach(function(el, i) {
                    el.classList.toggle('is-active', i === mentionActiveIndex);
                });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                mentionActiveIndex = Math.max(mentionActiveIndex - 1, 0);
                mentionDropdown.querySelectorAll('.editor-mention-item').forEach(function(el, i) {
                    el.classList.toggle('is-active', i === mentionActiveIndex);
                });
            } else if (e.key === 'Enter' && mentionUsers.length) {
                e.preventDefault();
                insertMention(mentionUsers[mentionActiveIndex].username);
            }
        });

        visual.addEventListener('paste', function(e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
            debouncedSync();
        });

        sourceInput.addEventListener('input', function() {
            store.value = sourceInput.value;
            store.dispatchEvent(new Event('input', { bubbles: true }));
            syncEditorWrapCharHint(wrap, store.value.length);
        });

        var form = wrap.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                if (currentTab === 'source') {
                    syncToStore(sourceInput.value);
                } else {
                    syncToStore();
                }
            }, true);
        }

        if (store.value.trim()) {
            loadVisualFromBbcode(store.value).then(function() {
                syncToStore(store.value);
            });
        } else {
            syncEditorWrapCharHint(wrap, store.value.length);
        }

        return {
            sync: function() { return syncToStore(); },
            getValue: function() { return syncToStore(); },
            focus: focusVisual
        };
    }

    function mountEditorFromTemplate(templateId, targetId, content) {
        var tpl = document.getElementById(templateId);
        if (!tpl) return null;
        var wrap = tpl.content.firstElementChild.cloneNode(true);
        wrap.dataset.target = targetId;
        var store = wrap.querySelector('.editor-bbcode-store');
        if (store) {
            store.id = targetId;
            store.removeAttribute('name');
            store.value = content || '';
        }
        return wrap;
    }

    window.ZunvoEditor = {
        init: initEditor,
        mountFromTemplate: function(templateId, targetId, content, container) {
            var wrap = mountEditorFromTemplate(templateId, targetId, content);
            if (!wrap || !container) return null;
            container.appendChild(wrap);
            return initEditor(wrap);
        }
    };

    window.ZunvoCharHint = {
        update: updateCharHint,
        bindInput: bindCharHintInput
    };

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.editor-wrap').forEach(function(wrap) {
            initEditor(wrap);
        });
        document.querySelectorAll('[data-char-hint]').forEach(function(el) {
            var hintId = el.dataset.charHint;
            var min = parseInt(el.dataset.charMin, 10) || 0;
            if (hintId && min > 0) {
                bindCharHintInput(el, min, hintId);
            }
        });
    });
})();
