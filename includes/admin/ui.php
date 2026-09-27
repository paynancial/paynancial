<?php
/**
 * Enterprise admin UI components (server-rendered, progressively enhanced
 * by public/assets/admin/admin.js). Every component escapes its input.
 */

declare(strict_types=1);

/** Inline SVG icon (24px grid, 1.8px stroke). Decorative: aria-hidden. */
function adm_icon(string $name, int $size = 18, string $class = ''): string
{
    static $paths = [
        'home' => 'M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z',
        'check' => 'M4 12.5l5 5L20 6.5',
        'pulse' => 'M3 12h4l3-8 4 16 3-8h4',
        'box' => 'M3 7.5 12 3l9 4.5v9L12 21l-9-4.5z M3 7.5l9 4.5 9-4.5 M12 12v9',
        'card' => 'M3 6h18v12H3z M3 10h18 M7 15h4',
        'building' => 'M4 21V5l8-2v18 M12 8h8v13 M3 21h18 M7.5 9h1 M7.5 13h1 M7.5 17h1 M15.5 12h1 M15.5 16h1',
        'inbox' => 'M3 13l3-8h12l3 8v6H3z M3 13h5l1 3h6l1-3h5',
        'file' => 'M6 3h8l5 5v13H6z M14 3v5h5 M9 13h6 M9 17h6',
        'handshake' => 'M2 11l4-4 4 2 3-2 3 2 4-2 2 4-9 8z M9 12l3 3 M11 10l3 3',
        'clipboard' => 'M8 3h8v3H8z M6 4.5H5V21h14V4.5h-1 M9 11h6 M9 15h4',
        'shield' => 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z',
        'percent' => 'M5 19L19 5 M7 5a2 2 0 1 1 0 4 2 2 0 0 1 0-4z M17 15a2 2 0 1 1 0 4 2 2 0 0 1 0-4z',
        'layout' => 'M3 4h18v16H3z M3 9h18 M9 9v11',
        'pen' => 'M4 20l4-1 11-11-3-3L5 16z M14 6l3 3',
        'image' => 'M3 5h18v14H3z M3 16l5-5 4 4 3-3 6 6 M15.5 8a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z',
        'eye' => 'M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z M12 9a3 3 0 1 1 0 6 3 3 0 0 1 0-6z',
        'search' => 'M11 4a7 7 0 1 1 0 14 7 7 0 0 1 0-14z M20 20l-4-4',
        'users' => 'M9 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8z M2 21c0-4 3-6 7-6s7 2 7 6 M16 3.5a4 4 0 0 1 0 7.5 M18 15c2.5.6 4 2.6 4 6',
        'key' => 'M8 11a4 4 0 1 1 0 8 4 4 0 0 1 0-8z M11 12l9-9 M17 6l3 3 M14.5 8.5l2 2',
        'list' => 'M8 6h13 M8 12h13 M8 18h13 M3.5 6h.01 M3.5 12h.01 M3.5 18h.01',
        'lock' => 'M5 11h14v10H5z M8 11V7a4 4 0 0 1 8 0v4',
        'heart' => 'M12 20s-8-4.5-8-10a4.5 4.5 0 0 1 8-3 4.5 4.5 0 0 1 8 3c0 5.5-8 10-8 10z',
        'scale' => 'M12 3v18 M6 21h12 M4 7h16 M7 7l-3 7a3 3 0 0 0 6 0z M17 7l-3 7a3 3 0 0 0 6 0z',
        'briefcase' => 'M3 7h18v13H3z M8 7V4h8v3 M3 12h18',
        'upload' => 'M12 16V4 M7 9l5-5 5 5 M4 20h16',
        'download' => 'M12 4v12 M7 11l5 5 5-5 M4 20h16',
        'receipt' => 'M6 3h12v18l-3-2-3 2-3-2-3 2z M9 8h6 M9 12h6',
        'bell' => 'M6 17v-6a6 6 0 0 1 12 0v6l2 2H4z M10 21h4',
        'help' => 'M12 3a9 9 0 1 1 0 18 9 9 0 0 1 0-18z M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.7 M12 17h.01',
        'menu' => 'M4 6h16 M4 12h16 M4 18h16',
        'chevron' => 'M9 6l6 6-6 6',
        'chevron-down' => 'M6 9l6 6 6-6',
        'chevron-up' => 'M6 15l6-6 6 6',
        'sidebar' => 'M3 4h18v16H3z M9 4v16 M15.5 10l-2 2 2 2',
        'x' => 'M6 6l12 12 M18 6L6 18',
        'dots' => 'M5 12h.01 M12 12h.01 M19 12h.01',
        'plus' => 'M12 5v14 M5 12h14',
        'refresh' => 'M20 11a8 8 0 1 0-2.3 5.7 M20 4v7h-7',
        'alert' => 'M12 3l10 18H2z M12 10v4 M12 17h.01',
        'info' => 'M12 3a9 9 0 1 1 0 18 9 9 0 0 1 0-18z M12 11v5 M12 8h.01',
        'logout' => 'M15 4h4v16h-4 M10 16l4-4-4-4 M14 12H3',
        'server' => 'M4 4h16v6H4z M4 14h16v6H4z M8 7h.01 M8 17h.01',
        'database' => 'M12 3c4.4 0 8 1.3 8 3s-3.6 3-8 3-8-1.3-8-3 3.6-3 8-3z M4 6v12c0 1.7 3.6 3 8 3s8-1.3 8-3V6 M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3',
        'globe' => 'M12 3a9 9 0 1 1 0 18 9 9 0 0 1 0-18z M3 12h18 M12 3a14 14 0 0 1 0 18 M12 3a14 14 0 0 0 0 18',
        'mail' => 'M3 5h18v14H3z M3 6l9 7 9-7',
        'chart' => 'M4 20V10 M10 20V4 M16 20v-7 M22 20H2',
        'columns' => 'M3 4h18v16H3z M9 4v16 M15 4v16',
        'filter' => 'M3 5h18l-7 8v6l-4 2v-8z',
        'sliders' => 'M4 6h10 M18 6h2 M16 4v4 M4 12h4 M12 12h8 M10 10v4 M4 18h12 M20 18h0 M18 16v4',
        'plug' => 'M9 3v5 M15 3v5 M6 8h12v3a6 6 0 0 1-12 0z M12 17v4',
        'clock' => 'M12 3a9 9 0 1 1 0 18 9 9 0 0 1 0-18z M12 7v5l3 2',
    ];
    $d = $paths[$name] ?? $paths['info'];
    return '<svg class="adm-ico ' . e($class) . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
        . ' stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="' . $d . '"/></svg>';
}

/**
 * Status pill: colour AND text (never colour alone). $tone: green, blue,
 * yellow, purple, coral, grey.
 */
function adm_pill(string $text, string $tone = 'grey', bool $dot = true): string
{
    return '<span class="adm-pill adm-pill--' . e($tone) . '">' . ($dot ? '<span class="adm-pill-dot" aria-hidden="true"></span>' : '') . e($text) . '</span>';
}

/** Health / availability state → [label, tone, icon]. */
function adm_state_meta(string $state): array
{
    return match ($state) {
        'operational'   => ['Operational', 'green', 'check'],
        'warning'       => ['Warning', 'yellow', 'alert'],
        'critical'      => ['Critical', 'coral', 'alert'],
        'not_connected' => ['Not connected', 'grey', 'plug'],
        default         => ['Status unavailable', 'grey', 'info'],
    };
}

function adm_state_pill(string $state): string
{
    [$label, $tone] = adm_state_meta($state);
    return adm_pill($label, $tone);
}

/**
 * Honest non-data states for a widget body.
 *   not_connected — no data source exists yet
 *   error         — the source exists but failed (details only in the server log)
 *   empty         — the source works and has no records
 *   restricted    — the viewer lacks permission
 */
function adm_widget_state(array $w, string $emptyText = 'Nothing here yet.', ?string $action = null, ?string $actionUrl = null): string
{
    $state = $w['state'] ?? 'unavailable';
    $key = e($w['key'] ?? '');
    return match ($state) {
        'not_connected' => '<div class="adm-state adm-state--nc">' . adm_icon('plug', 20) . '<div><strong>' . e($w['nc_title'] ?? 'Not connected')
            . '</strong><p>' . e($w['note'] ?? 'Connect a supported data source to populate this panel.') . '</p></div></div>',
        'error' => '<div class="adm-state adm-state--error" role="alert">' . adm_icon('alert', 20) . '<div><strong>Unable to load this metric.</strong>'
            . '<p>The error was logged. No value is shown rather than a wrong one.</p><button type="button" class="adm-btn adm-btn--sm" data-widget-retry="' . $key . '">'
            . adm_icon('refresh', 14) . 'Retry</button></div></div>',
        'restricted' => '<div class="adm-state">' . adm_icon('lock', 20) . '<div><strong>Not available for your role</strong><p>Ask a super admin if you need access.</p></div></div>',
        'empty' => '<div class="adm-state">' . adm_icon('inbox', 20) . '<div><strong>' . e($emptyText) . '</strong>'
            . ($action && $actionUrl ? '<p><a class="adm-btn adm-btn--sm adm-btn--primary" href="' . e($actionUrl) . '">' . adm_icon('plus', 14) . e($action) . '</a></p>' : '')
            . '</div></div>',
        default => '<div class="adm-state">' . adm_icon('info', 20) . '<div><strong>Status unavailable</strong></div></div>',
    };
}

/** Loading skeleton used while an async widget fetches (and during Retry). */
function adm_skeleton(int $lines = 3): string
{
    $out = '<div class="adm-skel" aria-hidden="true">';
    for ($i = 0; $i < $lines; $i++) {
        $out .= '<span style="width:' . [92, 78, 64, 85, 70][$i % 5] . '%"></span>';
    }
    return $out . '</div><span class="sr-only">Loading…</span>';
}

/** "Source: …" provenance line shown in widget footers. */
function adm_source(array $w): string
{
    return '<p class="adm-source">' . adm_icon('database', 12) . 'Source: ' . e($w['source'] ?? 'unknown')
        . (!empty($w['updated_at']) ? ' · Updated ' . e(date('H:i', strtotime((string) $w['updated_at']))) : '') . '</p>';
}

/** Relative time ("5 min ago") with the exact time as a title. */
function adm_ago(?string $ts): string
{
    if (!$ts) {
        return '—';
    }
    $t = strtotime($ts);
    $d = time() - $t;
    $txt = match (true) {
        $d < 60 => 'just now',
        $d < 3600 => floor($d / 60) . ' min ago',
        $d < 86400 => floor($d / 3600) . ' h ago',
        $d < 86400 * 7 => floor($d / 86400) . ' d ago',
        default => date('j M Y', $t),
    };
    return '<time datetime="' . e(date('c', $t)) . '" title="' . e(date('j M Y, H:i', $t)) . '">' . e($txt) . '</time>';
}

// ---------------------------------------------------------------- tables

/** Normalised table query state from $_GET: search, sort, dir, page, per. */
function adm_table_state(array $sortable, string $defaultSort, string $defaultDir = 'desc', int $per = 25): array
{
    $sort = (string) ($_GET['sort'] ?? $defaultSort);
    $sort = isset($sortable[$sort]) ? $sort : $defaultSort;
    $dir = strtolower((string) ($_GET['dir'] ?? $defaultDir)) === 'asc' ? 'asc' : 'desc';
    $perAllowed = [10, 25, 50, 100];
    $req = (int) ($_GET['per'] ?? 0);
    $per = in_array($req, $perAllowed, true) ? $req : $per;
    return [
        'q' => mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100),
        'sort' => $sort, 'dir' => $dir, 'order_sql' => $sortable[$sort] . ' ' . strtoupper($dir),
        'page' => max(1, (int) ($_GET['page'] ?? 1)), 'per' => $per,
    ];
}

/** URL for the current page with some query parameters replaced. */
function adm_url(array $replace): string
{
    $q = array_merge($_GET, $replace);
    $q = array_filter($q, fn ($v) => $v !== null && $v !== '');
    $path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    return $path . ($q ? '?' . http_build_query($q) : '');
}

/** Sortable column header (a real link — works without JavaScript). */
function adm_th(string $label, string $key, array $st, string $col = ''): string
{
    $active = $st['sort'] === $key;
    $next = $active && $st['dir'] === 'asc' ? 'desc' : 'asc';
    $aria = $active ? ($st['dir'] === 'asc' ? 'ascending' : 'descending') : 'none';
    return '<th scope="col" aria-sort="' . $aria . '"' . ($col ? ' data-col="' . e($col) . '"' : '') . '><a class="adm-sort' . ($active ? ' is-active' : '')
        . '" href="' . e(adm_url(['sort' => $key, 'dir' => $next, 'page' => null])) . '">' . e($label)
        . '<span class="adm-sort-ind" aria-hidden="true">' . ($active ? ($st['dir'] === 'asc' ? '↑' : '↓') : '↕') . '</span></a></th>';
}

function adm_pagination(int $total, array $st): string
{
    $pages = max(1, (int) ceil($total / $st['per']));
    $page = min($st['page'], $pages);
    $from = $total ? ($page - 1) * $st['per'] + 1 : 0;
    $to = min($total, $page * $st['per']);
    $out = '<nav class="adm-pager" aria-label="Pagination"><span>' . $from . '–' . $to . ' of ' . number_format($total) . '</span><div>';
    $out .= $page > 1 ? '<a class="adm-btn adm-btn--sm" href="' . e(adm_url(['page' => $page - 1])) . '" rel="prev">Previous</a>' : '<span class="adm-btn adm-btn--sm is-disabled" aria-disabled="true">Previous</span>';
    $out .= '<span class="adm-pager-n">Page ' . $page . ' of ' . $pages . '</span>';
    $out .= $page < $pages ? '<a class="adm-btn adm-btn--sm" href="' . e(adm_url(['page' => $page + 1])) . '" rel="next">Next</a>' : '<span class="adm-btn adm-btn--sm is-disabled" aria-disabled="true">Next</span>';
    $out .= '<label class="adm-per">Rows <select data-nav-select aria-label="Rows per page">';
    foreach ([10, 25, 50, 100] as $n) {
        $out .= '<option value="' . e(adm_url(['per' => $n, 'page' => null])) . '"' . ($n === $st['per'] ? ' selected' : '') . '>' . $n . '</option>';
    }
    return $out . '</select></label></div></nav>';
}

/** Column-visibility menu for a table (JS toggles data-col cells; remembered per table). */
function adm_columns_menu(string $tableId, array $cols): string
{
    $out = '<div class="adm-pop-wrap"><button type="button" class="adm-btn adm-btn--sm" data-pop="' . e($tableId) . '-cols" aria-expanded="false" aria-controls="'
        . e($tableId) . '-cols">' . adm_icon('columns', 15) . 'Columns</button><div class="adm-pop adm-pop--menu" id="' . e($tableId) . '-cols" hidden>'
        . '<p class="adm-pop-title">Show columns</p>';
    foreach ($cols as $key => $label) {
        $out .= '<label class="adm-check"><input type="checkbox" data-col-toggle="' . e($tableId) . '" value="' . e($key) . '" checked> ' . e($label) . '</label>';
    }
    return $out . '</div></div>';
}

/** Page header: breadcrumbs are in the top bar; this renders title, subtitle and actions. */
function adm_page_head(string $title, string $sub = '', string $actions = ''): string
{
    return '<div class="adm-head"><div><h1 class="adm-h1">' . e($title) . '</h1>' . ($sub ? '<p class="adm-sub">' . e($sub) . '</p>' : '')
        . '</div>' . ($actions ? '<div class="adm-head-actions">' . $actions . '</div>' : '') . '</div>';
}
