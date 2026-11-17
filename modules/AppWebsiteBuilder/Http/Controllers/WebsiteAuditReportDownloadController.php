<?php

namespace Modules\AppWebsiteBuilder\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Models\WebsiteAuditRun;
use Modules\AppWebsiteBuilder\Support\WebsiteAuditPdfExporter;

class WebsiteAuditReportDownloadController extends Controller
{
    public function __invoke(Request $request, WebsiteAuditRun $websiteAuditRun): Response
    {
        $user = $request->user();
        abort_unless($user, 403);

        $onboarding = app(AgencyOnboarding::class);
        abort_unless($onboarding->isComplete($user), 403);
        abort_unless(in_array('website', $onboarding->enabledAgentsForProject($user), true), 403);
        abort_unless((int) $websiteAuditRun->owner_user_id === (int) $user->id, 404);

        $result = $websiteAuditRun->result;
        abort_unless(is_array($result), 404);

        $pdf = WebsiteAuditPdfExporter::pdfContent($result, $websiteAuditRun->created_at);
        $filename = WebsiteAuditPdfExporter::downloadFilename($result, $websiteAuditRun->created_at);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}