<?php

namespace Modules\AppFiles\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\AppFiles\Support\PublishedPostMediaDriveArchiver;
use Modules\AppPublishing\Models\PublishingPost;

class ArchivePublishedPostMediaToGoogleDriveJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public int $postId)
    {
        $this->onQueue('drive-archive');
    }

    public function handle(PublishedPostMediaDriveArchiver $archiver): void
    {
        $post = PublishingPost::query()
            ->with('account')
            ->find($this->postId);

        if (! $post) {
            return;
        }

        $archiver->archive($post);
    }
}
