<?php

namespace App\Observers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttachmentObserver
{
    /**
     * Handle the Attachment "created" event.
     */
    public function created(Attachment $attachment): void
    {
        if($attachment->wasRecentlyCreated){
            Log::info("New Attachment created:{$attachment->id}");
        }
    }

    /**
     * Handle the Attachment "updated" event.
     */
    public function updating(Attachment $attachment): void
    {
        if ($attachment->isDirty()){
            Log::info("Attachment updated");
        }
    }

    /**
     * Handle the Attachment "deleted" event.
     */
    public function deleting(Attachment $attachment): void
    {
        // When you delete an attachment, it makes sure that the actual file is deleted from storage.
        if ($attachment->path && Storage::disk($attachment->disk)->exists($attachment->path)){
            Storage::disk($attachment->disk)->delete($attachment->path);
        }
    }

    /**
     * Handle the Attachment "restored" event.
     */
    public function restored(Attachment $attachment): void
    {
        //
    }

    /**
     * Handle the Attachment "force deleted" event.
     */
    public function forceDeleted(Attachment $attachment): void
    {
        //
    }

}
