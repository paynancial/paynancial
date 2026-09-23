<?php
/**
 * Marketing site <head>. Expects optional $page_meta (title, description,
 * canonical, schema) set by the page template before this is included.
 */
$page_meta = $page_meta ?? [];
?><!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php seo_meta($page_meta); ?>
<link rel="icon" type="image/png" href="<?= asset('images/paynancial-icon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,340;9..144,460;9..144,560&family=Inter:wght@400;500;600;650;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/main.css') ?>">
<?php if (!empty($page_meta['extra_css'])): ?>
<link rel="stylesheet" href="<?= asset($page_meta['extra_css']) ?>">
<?php endif; ?>
</head>
<body>
