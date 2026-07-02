<?php

function parse_markdown($text) {
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $blocks = preg_split('/\n```/', $text);
    $html = '';
    foreach ($blocks as $i => $block) {
        if ($i === 0) {
            $html .= parse_markdown_inline_block($block);
            continue;
        }
        $end = strpos($block, "\n```");
        if ($end === false) {
            $html .= '<pre><code>' . escape($block) . '</code></pre>';
            continue;
        }
        $code_part = substr($block, 0, $end);
        $rest = substr($block, $end + 4);
        $lang = '';
        $code = $code_part;
        if (preg_match('/^(\w+)\n/', $code_part, $m)) {
            $lang = $m[1];
            $code = substr($code_part, strlen($m[0]));
        }
        $lang_class = $lang ? ' class="language-' . escape($lang) . '"' : '';
        $html .= '<pre><code' . $lang_class . '>' . escape(trim($code)) . '</code></pre>';
        $html .= parse_markdown_inline_block($rest);
    }
    return $html;
}

function parse_markdown_inline_block($text) {
    $lines = explode("\n", $text);
    $out = '';
    $in_ul = false;
    $in_ol = false;
    $para = [];
    $flush_para = function() use (&$para, &$out) {
        if (empty($para)) return;
        $p = escape(implode("\n", $para));
        $p = format_inline_markdown($p);
        $out .= '<p>' . $p . '</p>';
        $para = [];
    };
    $close_lists = function() use (&$in_ul, &$in_ol, &$out) {
        if ($in_ul) { $out .= '</ul>'; $in_ul = false; }
        if ($in_ol) { $out .= '</ol>'; $in_ol = false; }
    };
    foreach ($lines as $line) {
        $trim = trim($line);
        if ($trim === '') {
            $flush_para();
            $close_lists();
            continue;
        }
        if (preg_match('/^### (.+)$/', $trim, $m)) {
            $flush_para(); $close_lists();
            $out .= '<h3>' . format_inline_markdown(escape($m[1])) . '</h3>';
            continue;
        }
        if (preg_match('/^## (.+)$/', $trim, $m)) {
            $flush_para(); $close_lists();
            $out .= '<h2>' . format_inline_markdown(escape($m[1])) . '</h2>';
            continue;
        }
        if (preg_match('/^# (.+)$/', $trim, $m)) {
            $flush_para(); $close_lists();
            $out .= '<h1>' . format_inline_markdown(escape($m[1])) . '</h1>';
            continue;
        }
        if (preg_match('/^> (.+)$/', $trim, $m)) {
            $flush_para(); $close_lists();
            $out .= '<blockquote>' . format_inline_markdown(escape($m[1])) . '</blockquote>';
            continue;
        }
        if (preg_match('/^\|\|(.+)\|\|$/', $trim, $m)) {
            $flush_para(); $close_lists();
            $out .= '<span class="spoiler" onclick="this.classList.toggle(\'revealed\')">' . format_inline_markdown(escape($m[1])) . '</span>';
            continue;
        }
        if (preg_match('/^- (.+)$/', $trim, $m)) {
            $flush_para();
            if ($in_ol) { $out .= '</ol>'; $in_ol = false; }
            if (!$in_ul) { $out .= '<ul>'; $in_ul = true; }
            $out .= '<li>' . format_inline_markdown(escape($m[1])) . '</li>';
            continue;
        }
        if (preg_match('/^\d+\. (.+)$/', $trim, $m)) {
            $flush_para();
            if ($in_ul) { $out .= '</ul>'; $in_ul = false; }
            if (!$in_ol) { $out .= '<ol>'; $in_ol = true; }
            $out .= '<li>' . format_inline_markdown(escape($m[1])) . '</li>';
            continue;
        }
        $close_lists();
        $para[] = $line;
    }
    $flush_para();
    $close_lists();
    return $out;
}

function format_inline_markdown($text) {
    $text = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" class="md-image">', $text);
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $text);
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" rel="nofollow noopener">$1</a>', $text);
    $text = nl2br($text);
    return $text;
}

