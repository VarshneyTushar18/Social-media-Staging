<?php

namespace Modules\AppAIStudio\Support;

class AiImageExportNormalizer
{
    public function normalize(string $contents, string $ratio): string
    {
        $target = $this->targetExportSize($ratio);

        if ($target === null || ! function_exists('imagecreatefromstring')) {
            return $contents;
        }

        [$targetW, $targetH] = $target;

        $src = @imagecreatefromstring($contents);

        if ($src === false) {
            return $contents;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);

        if ($srcW <= 0 || $srcH <= 0) {
            imagedestroy($src);

            return $contents;
        }

        $targetAspect = $targetW / $targetH;
        $srcAspect = $srcW / $srcH;

        if ($srcAspect > $targetAspect) {
            $cropW = (int) round($srcH * $targetAspect);
            $cropH = $srcH;
            $cropX = (int) (($srcW - $cropW) / 2);
            $cropY = 0;
        } else {
            $cropW = $srcW;
            $cropH = (int) round($srcW / $targetAspect);
            $cropX = 0;
            $cropY = (int) (($srcH - $cropH) / 2);
        }

        $cropped = imagecreatetruecolor($cropW, $cropH);

        if ($cropped === false) {
            imagedestroy($src);

            return $contents;
        }

        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);
        imagecopy($cropped, $src, 0, 0, $cropX, $cropY, $cropW, $cropH);
        imagedestroy($src);

        $out = imagecreatetruecolor($targetW, $targetH);

        if ($out === false) {
            imagedestroy($cropped);

            return $contents;
        }

        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagecopyresampled($out, $cropped, 0, 0, 0, 0, $targetW, $targetH, $cropW, $cropH);
        imagedestroy($cropped);

        ob_start();
        imagepng($out);
        $png = (string) ob_get_clean();
        imagedestroy($out);

        return $png !== '' ? $png : $contents;
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    public function targetExportSize(string $ratio): ?array
    {
        return match (trim($ratio)) {
            '1:1' => [1080, 1080],
            '4:5' => [1080, 1350],
            '9:16' => [1080, 1920],
            '16:9' => [1920, 1080],
            default => null,
        };
    }
}
