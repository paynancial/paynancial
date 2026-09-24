<?php
/**
 * CMS image uploads (social / Open Graph images). Validated and re-encoded:
 *   - upload error, size (max 2 MB) and real MIME type (finfo) checked;
 *   - JPEG, PNG or WebP only; dimensions 200–4000 px;
 *   - the image is decoded and re-encoded with GD, which drops metadata and
 *     anything appended to the file, and stored under a random name in
 *     public/uploads/cms/YYYY/ (no script execution — see its .htaccess).
 */

declare(strict_types=1);

const CMS_IMAGE_MAX_BYTES = 2 * 1024 * 1024;
const CMS_IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

/** Validation error for an uploaded image, or null when acceptable. */
function cms_image_error(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return 'The upload failed. Please choose the image again.';
    }
    if (($file['size'] ?? 0) <= 0 || $file['size'] > CMS_IMAGE_MAX_BYTES) {
        return 'Images must be 2 MB or smaller.';
    }
    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_file($tmp) || (PHP_SAPI !== 'cli' && !is_uploaded_file($tmp))) {
        return 'The upload failed. Please choose the image again.';
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if (!isset(CMS_IMAGE_TYPES[$mime])) {
        return 'Only JPEG, PNG or WebP images are accepted.';
    }
    $info = @getimagesize($tmp);
    if ($info === false || ($info['mime'] ?? '') !== $mime) {
        return 'This file is not a valid image.';
    }
    [$w, $h] = $info;
    if ($w < 200 || $h < 200 || $w > 4000 || $h > 4000) {
        return 'Images must be between 200 and 4000 pixels on each side.';
    }
    return null;
}

/** Store a validated image; returns its public path (/uploads/cms/…). */
function cms_store_image(array $file, ?string $publicDir = null): string
{
    $error = cms_image_error($file);
    if ($error !== null) {
        throw new RuntimeException($error);
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $img = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
        'image/png'  => @imagecreatefrompng($file['tmp_name']),
        'image/webp' => @imagecreatefromwebp($file['tmp_name']),
    };
    if (!$img) {
        throw new RuntimeException('This file is not a valid image.');
    }
    $publicDir ??= dirname(__DIR__, 2) . '/public';
    $rel = '/uploads/cms/' . date('Y');
    if (!is_dir($publicDir . $rel) && !mkdir($publicDir . $rel, 0755, true) && !is_dir($publicDir . $rel)) {
        throw new RuntimeException('The image could not be saved.');
    }
    $ext = CMS_IMAGE_TYPES[$mime];
    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $publicDir . $rel . '/' . $name;
    if ($ext === 'png') {
        imagesavealpha($img, true);
    }
    $ok = match ($ext) {
        'jpg'  => imagejpeg($img, $dest, 86),
        'png'  => imagepng($img, $dest, 6),
        'webp' => imagewebp($img, $dest, 86),
    };
    imagedestroy($img);
    if (!$ok) {
        throw new RuntimeException('The image could not be saved.');
    }
    @chmod($dest, 0644);
    return $rel . '/' . $name;
}
