<?php

if (!function_exists('get_user_by_username')) {
    require_once APP_PATH . '/models/user.php';
}

function parse_post_content($text) {
    if ($text === null || $text === '') {
        return '';
    }
    if (preg_match('/\[(?:\/)?(?:b|i|u|s|url|img|quote|code|spoiler|hide|list|color|size|center|youtube|hr)\b/i', $text)) {
        return parse_bbcode($text);
    }
    if (function_exists('parse_markdown')) {
        return bbcode_format_mentions(parse_markdown($text));
    }
    return bbcode_format_mentions(nl2br(escape($text)));
}

function parse_bbcode($text, $already_escaped = false) {
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    if (!$already_escaped) {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    $vault = [];
    $vault_index = 0;

    $stash = function ($html) use (&$vault, &$vault_index) {
        $key = "\x00BBC" . $vault_index++ . "\x00";
        $vault[$key] = $html;
        return $key;
    };

    $text = preg_replace_callback('/\[code\](.*?)\[\/code\]/si', function ($m) use ($stash) {
        return $stash('<pre class="bbcode-code"><code>' . $m[1] . '</code></pre>');
    }, $text);

    $text = preg_replace_callback('/\[youtube\](.*?)\[\/youtube\]/si', function ($m) use ($stash) {
        $id = bbcode_youtube_id($m[1]);
        if (!$id) {
            return $m[0];
        }
        return $stash(
            '<div class="bbcode-youtube"><iframe src="https://www.youtube.com/embed/' . $id . '" allowfullscreen loading="lazy" title="YouTube"></iframe></div>'
        );
    }, $text);

    $text = preg_replace('/\[hr\]/i', '<hr class="bbcode-hr">', $text);

    $text = preg_replace_callback('/\[list=1\](.*?)\[\/list\]/si', function ($m) {
        return bbcode_parse_list($m[1], true);
    }, $text);

    $text = preg_replace_callback('/\[list\](.*?)\[\/list\]/si', function ($m) {
        return bbcode_parse_list($m[1], false);
    }, $text);

    $text = preg_replace_callback('/\[quote(?:=([^\]]*))?\](.*?)\[\/quote\]/si', function ($m) {
        $author = trim($m[1]);
        $body = parse_bbcode($m[2], true);
        $head = $author !== ''
            ? '<div class="bbcode-quote-author">' . $author . ' yazdı:</div>'
            : '<div class="bbcode-quote-author">Alıntı</div>';
        return '<blockquote class="bbcode-quote">' . $head . '<div class="bbcode-quote-body">' . $body . '</div></blockquote>';
    }, $text);

    $text = preg_replace_callback('/\[(?:spoiler|hide)\](.*?)\[\/(?:spoiler|hide)\]/si', function ($m) {
        $inner = parse_bbcode($m[1], true);
        return '<span class="bbcode-spoiler" onclick="this.classList.toggle(\'revealed\')">' . $inner . '</span>';
    }, $text);

    $text = preg_replace_callback('/\[center\](.*?)\[\/center\]/si', function ($m) {
        return '<div class="bbcode-center">' . parse_bbcode($m[1], true) . '</div>';
    }, $text);

    $text = parse_bbcode_inline($text);
    $text = nl2br($text, false);

    foreach ($vault as $key => $html) {
        $text = str_replace($key, $html, $text);
    }

    $text = bbcode_format_mentions($text);

    return $text;
}

function bbcode_parse_list($content, $ordered) {
    $items = preg_split('/\[\*\]/i', $content);
    $tag = $ordered ? 'ol' : 'ul';
    $html = '<' . $tag . ' class="bbcode-list">';
    $has_item = false;
    foreach ($items as $item) {
        $item = trim($item);
        if ($item === '') {
            continue;
        }
        $has_item = true;
        $html .= '<li>' . parse_bbcode($item, true) . '</li>';
    }
    $html .= '</' . $tag . '>';
    return $has_item ? $html : escape($content);
}

function parse_bbcode_inline($text) {
    $text = preg_replace_callback('/\[url=([^\]]+)\](.*?)\[\/url\]/si', function ($m) {
        $href = bbcode_sanitize_url(trim($m[1]));
        if (!$href) {
            return $m[0];
        }
        return '<a href="' . $href . '" rel="nofollow noopener" target="_blank">' . $m[2] . '</a>';
    }, $text);

    $text = preg_replace_callback('/\[url\](.*?)\[\/url\]/si', function ($m) {
        $href = bbcode_sanitize_url(trim($m[1]));
        if (!$href) {
            return $m[0];
        }
        $label = $m[1];
        return '<a href="' . $href . '" rel="nofollow noopener" target="_blank">' . $label . '</a>';
    }, $text);

    $text = preg_replace_callback('/\[img\](.*?)\[\/img\]/si', function ($m) {
        $src = bbcode_sanitize_url(trim($m[1]));
        if (!$src) {
            return $m[0];
        }
        return '<img src="' . $src . '" alt="" class="bbcode-image" loading="lazy">';
    }, $text);

    $text = preg_replace_callback('/\[color=([^\]]+)\](.*?)\[\/color\]/si', function ($m) {
        $color = bbcode_sanitize_color($m[1]);
        if (!$color) {
            return $m[0];
        }
        return '<span style="color:' . $color . '">' . $m[2] . '</span>';
    }, $text);

    $text = preg_replace_callback('/\[size=([^\]]+)\](.*?)\[\/size\]/si', function ($m) {
        $size = bbcode_sanitize_size($m[1]);
        if (!$size) {
            return $m[0];
        }
        return '<span style="font-size:' . $size . 'px">' . $m[2] . '</span>';
    }, $text);

    $text = preg_replace('/\[b\](.*?)\[\/b\]/si', '<strong>$1</strong>', $text);
    $text = preg_replace('/\[i\](.*?)\[\/i\]/si', '<em>$1</em>', $text);
    $text = preg_replace('/\[u\](.*?)\[\/u\]/si', '<u>$1</u>', $text);
    $text = preg_replace('/\[s\](.*?)\[\/s\]/si', '<s>$1</s>', $text);

    return $text;
}

function bbcode_format_mentions($text) {
    return preg_replace_callback('/(?<![\w\]])@([a-zA-Z0-9_-]{3,20})\b/', function ($m) {
        $user = get_user_by_username($m[1]);
        if ($user) {
            return '<a href="' . url('/profil/' . $user['username']) . '" class="mention-link">@' . escape($user['username']) . '</a>';
        }
        return '@' . escape($m[1]);
    }, $text);
}

function bbcode_sanitize_url($url) {
    $url = trim(html_entity_decode($url, ENT_QUOTES, 'UTF-8'));
    if ($url === '') {
        return null;
    }
    if (preg_match('#^https?://#i', $url)) {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
    if (preg_match('#^www\.#i', $url)) {
        return htmlspecialchars('https://' . $url, ENT_QUOTES, 'UTF-8');
    }
    return null;
}

function bbcode_sanitize_color($color) {
    $color = trim(strtolower($color));
    $named = [
        'black', 'white', 'red', 'green', 'blue', 'yellow', 'orange', 'purple',
        'pink', 'gray', 'grey', 'brown', 'navy', 'teal', 'aqua', 'lime', 'maroon',
        'olive', 'silver', 'fuchsia', 'cyan'
    ];
    if (in_array($color, $named, true)) {
        return $color;
    }
    if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $color)) {
        return htmlspecialchars($color, ENT_QUOTES, 'UTF-8');
    }
    return null;
}

function bbcode_sanitize_size($size) {
    $size = (int)trim($size);
    if ($size < 8 || $size > 36) {
        return null;
    }
    return $size;
}

function bbcode_youtube_id($input) {
    $input = trim(html_entity_decode($input, ENT_QUOTES, 'UTF-8'));
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i', $input, $m)) {
        return htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
    }
    return null;
}

function bbcode_toolbar_html($textarea_id = 'content', $content = '', $options = []) {
    $compact = !empty($options['compact']);
    $required = array_key_exists('required', $options) ? $options['required'] : true;
    $minlength = isset($options['minlength']) ? (int)$options['minlength'] : 20;
    $placeholder = $options['placeholder'] ?? 'İçeriğinizi yazın...';
    $req_attr = $required ? ' required' : '';
    $min_attr = $minlength > 0 ? ' minlength="' . $minlength . '"' : '';
    $toolbar_class = $compact ? 'bbcode-toolbar bbcode-toolbar-compact' : 'bbcode-toolbar';

  $colors = ['#000000', '#e60000', '#ff6600', '#ffcc00', '#009900', '#0066cc', '#9933ff', '#666666'];
    $sizes = [10, 12, 14, 16, 18, 20, 24];

    $color_swatches = '';
    foreach ($colors as $c) {
        $color_swatches .= '<button type="button" class="editor-color-swatch" data-color="' . escape($c) . '" style="background:' . escape($c) . '" title="' . escape($c) . '"></button>';
    }
    $size_buttons = '';
    foreach ($sizes as $s) {
        $size_buttons .= '<button type="button" class="editor-size-btn" data-size="' . $s . '">' . $s . '</button>';
    }

    $hint_attr = !empty($options['hint_id']) ? ' data-hint-id="' . escape($options['hint_id']) . '"' : '';

    return '<div class="editor-wrap" data-target="' . escape($textarea_id) . '" data-minlength="' . $minlength . '"' . $hint_attr . '>
        <div class="' . $toolbar_class . '">
            <div class="editor-toolbar-main">
                <button type="button" data-bb="bold" title="Kalın"><strong>B</strong></button>
                <button type="button" data-bb="italic" title="İtalik"><em>I</em></button>
                <button type="button" data-bb="underline" title="Altı çizili"><u>U</u></button>
                <button type="button" data-bb="strike" title="Üstü çizili"><s>S</s></button>
                <span class="editor-toolbar-sep"></span>
                <button type="button" data-bb="url" data-popover="url" title="Link">URL</button>
                <button type="button" data-bb="img" data-popover="img" title="Resim">IMG</button>
                <button type="button" data-bb="quote" data-popover="quote" title="Alıntı">Alıntı</button>
                <button type="button" data-bb="code" title="Kod">Kod</button>
                <button type="button" data-bb="spoiler" title="Gizli">Gizli</button>
                <button type="button" data-bb="color" data-popover="color" title="Renk">Renk</button>
                <button type="button" data-bb="size" data-popover="size" title="Boyut">Boyut</button>
                <button type="button" data-bb="list" title="Liste">Liste</button>
                <button type="button" data-bb="center" title="Ortala">Orta</button>
                <button type="button" data-bb="youtube" data-popover="youtube" title="YouTube">YT</button>
                <button type="button" data-bb="hr" title="Çizgi">—</button>
            </div>
            <span class="editor-tabs">
                <button type="button" data-tab="write" class="active">Yaz</button>
                <button type="button" data-tab="preview">Önizle</button>
                <button type="button" data-tab="source">Kod</button>
            </span>
        </div>
        <div class="editor-popover" hidden>
            <div class="editor-popover-panel" data-popover="url">
                <label>Adres</label>
                <input type="url" class="editor-popover-input" data-field="url" placeholder="https://">
                <label>Görünen metin</label>
                <input type="text" class="editor-popover-input" data-field="text" placeholder="Link metni (opsiyonel)">
                <div class="editor-popover-actions">
                    <button type="button" class="editor-popover-cancel">İptal</button>
                    <button type="button" class="editor-popover-apply" data-apply="url">Ekle</button>
                </div>
            </div>
            <div class="editor-popover-panel" data-popover="img">
                <label>Resim adresi</label>
                <input type="url" class="editor-popover-input" data-field="url" placeholder="https://">
                <div class="editor-popover-actions">
                    <button type="button" class="editor-popover-cancel">İptal</button>
                    <button type="button" class="editor-popover-apply" data-apply="img">Ekle</button>
                </div>
            </div>
            <div class="editor-popover-panel" data-popover="quote">
                <label>Kullanıcı adı (opsiyonel)</label>
                <input type="text" class="editor-popover-input" data-field="user" placeholder="Kullanıcı adı">
                <div class="editor-popover-actions">
                    <button type="button" class="editor-popover-cancel">İptal</button>
                    <button type="button" class="editor-popover-apply" data-apply="quote">Ekle</button>
                </div>
            </div>
            <div class="editor-popover-panel" data-popover="color">
                <div class="editor-color-grid">' . $color_swatches . '</div>
                <input type="text" class="editor-popover-input" data-field="hex" placeholder="#ff0000">
                <div class="editor-popover-actions">
                    <button type="button" class="editor-popover-cancel">İptal</button>
                    <button type="button" class="editor-popover-apply" data-apply="color">Uygula</button>
                </div>
            </div>
            <div class="editor-popover-panel" data-popover="size">
                <div class="editor-size-grid">' . $size_buttons . '</div>
                <div class="editor-popover-actions">
                    <button type="button" class="editor-popover-cancel">Kapat</button>
                </div>
            </div>
            <div class="editor-popover-panel" data-popover="youtube">
                <label>YouTube link veya video ID</label>
                <input type="text" class="editor-popover-input" data-field="url" placeholder="https://youtube.com/watch?v=...">
                <div class="editor-popover-actions">
                    <button type="button" class="editor-popover-cancel">İptal</button>
                    <button type="button" class="editor-popover-apply" data-apply="youtube">Ekle</button>
                </div>
            </div>
        </div>
        <div class="editor-panels">
            <div class="editor-visual bbcode-body" contenteditable="true" data-placeholder="' . escape($placeholder) . '"></div>
            <textarea id="' . escape($textarea_id) . '" name="' . escape($textarea_id) . '" class="editor-bbcode-store"' . $req_attr . $min_attr . '>' . escape($content) . '</textarea>
            <div class="editor-preview bbcode-body" hidden></div>
            <textarea class="editor-source-input" hidden spellcheck="false"></textarea>
            <div class="editor-mention-dropdown" hidden></div>
        </div>
    </div>';
}
