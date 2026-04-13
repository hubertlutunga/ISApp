<?php

final class EventThumbnailService
{
    public static function photoThumbPath(string $srcRel, int $w = 100, int $h = 100): ?string
    {
        $baseOriginals = __DIR__ . '/../../event/users/photosevent/';
        $baseCache = __DIR__ . '/../../event/users/photosevent_cache/';

        $srcAbs = $baseOriginals . $srcRel;
        if (!file_exists($srcAbs)) {
            return null;
        }

        if (!is_dir($baseCache)) {
            @mkdir($baseCache, 0775, true);
        }

        $cacheName = $w . 'x' . $h . '_' . $srcRel;
        $dstAbs = $baseCache . $cacheName;
        $dstRel = '../photosevent_cache/' . $cacheName;

        if (file_exists($dstAbs)) {
            return $dstRel;
        }

        $imageInfo = @getimagesize($srcAbs);
        if ($imageInfo === false) {
            return '../photosevent/' . $srcRel;
        }

        [$ow, $oh, $type] = $imageInfo;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $im = imagecreatefromjpeg($srcAbs);
                break;
            case IMAGETYPE_PNG:
                $im = imagecreatefrompng($srcAbs);
                break;
            case IMAGETYPE_WEBP:
                $im = imagecreatefromwebp($srcAbs);
                break;
            default:
                return '../photosevent/' . $srcRel;
        }

        $ratio = max($w / $ow, $h / $oh);
        $nw = (int) ceil($ow * $ratio);
        $nh = (int) ceil($oh * $ratio);
        $tmp = imagecreatetruecolor($w, $h);

        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
            $trans = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
            imagefilledrectangle($tmp, 0, 0, $w, $h, $trans);
        }

        imagecopyresampled(
            $tmp,
            $im,
            (int) (($w - $nw) / 2),
            (int) (($h - $nh) / 2),
            0,
            0,
            $nw,
            $nh,
            $ow,
            $oh
        );

        imagejpeg($tmp, $dstAbs, 85);
        imagedestroy($tmp);
        imagedestroy($im);

        return $dstRel;
    }
}