<?php

namespace Modules\AppWebsiteBuilder\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class WebsiteAuditPdfExporter
{
    /**
     * @param  array<string, mixed>  $result
     */
    public static function downloadFilename(array $result, ?\DateTimeInterface $generatedAt = null): string
    {
        $label = Str::slug((string) ($result['target_label'] ?? 'website'), '-');

        if ($label === '') {
            $label = 'website';
        }

        $date = ($generatedAt ?? now())->format('Y-m-d');

        return 'website-audit-'.Str::limit($label, 48, '').'-'.$date.'.pdf';
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public static function pdfContent(array $result, ?\DateTimeInterface $generatedAt = null): string
    {
        $at = $generatedAt ?? now();

        return Pdf::loadView('appwebsitebuilder::exports.audit-report-pdf', [
            'result' => $result,
            'appName' => (string) config('app.name', 'Stackposts'),
            'generatedAt' => $at->format('Y-m-d H:i'),
        ])
            ->setPaper('a4', 'portrait')
            ->output();
    }
}