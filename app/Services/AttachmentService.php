<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Traits\HandleServiceErrors;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    use HandleServiceErrors;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get all attachments from database
     *
     * @return array $arraydata
     */
    public function getAllAttachments()
    {
        try{
            $attachments = Cache::remember('all_attachments', 3600, function(){
                    return  Attachment::VisibleTo(Auth::user())->get();
                });
            return $attachments;

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Add new attachment to the database.
     *
     * @param array $arraydata
     *
     * @return Attachment $attachment
     */
    public function createAttachment(array $data , Model $model , string $type)
    {
        try{
            $file = $data['attachment'];

            //Extract fields from the file, upload the file, and create it in the database
            return DB::transaction(function () use ($file, $model, $type) {
                $file_name = Str::random(5).$file->getClientOriginalName();
                $file_size = $file->getSize();
                $mime_type = $file->getMimeType();
                $path = $file->storeAs('files_'.$type ,$file_name, 'public');

                $attachment = $model->attachments()->create([
                        'path'      => $path,
                        'disk'      => 'public',
                        'file_name' => $file_name,
                        'file_size' => $file_size,
                        'mime_type' => $mime_type
                        ]);
                Cache::forget("all_attachments");
                return $attachment;
            });

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Update the specified attachment in the database.
     *
     * @param array $arraydata
     * @param Attachment $attachment
     *
     * @return Attachment $attachment
     */

    public function updateAttachment(array $data, Attachment $attachment){
        try{
            $file = $data['attachment'];

            //Verify that the file exists
            if(!$file){
                return $this->error("no data to update",500);
            }

            //get the type to which the attachment belongs in order to store the files in the appropriate folder.
            $type = match($attachment->attachable_type){
            'App\Models\Project' => 'projects',
            'App\Models\Task'    => 'tasks'  ,
            'App\Models\Comment' => 'comments'  ,
            };

            /* Extract the fields from the file,
            then delete the old file from storage,
            upload the new file and update it in the database
            */
            return DB::transaction(function () use ($file,$attachment, $type) {
                $file_name = Str::random(5).$file->getClientOriginalName();
                $file_size = $file->getSize();
                $mime_type = $file->getMimeType();
                $path = $file->storeAs('files_'.$type ,$file_name, 'public');

                if ($attachment->path && Storage::disk($attachment->disk)->exists($attachment->path)){
                Storage::disk($attachment->disk)->delete($attachment->path);
                }

                $attachment->update([
                        'path'      => $path,
                        'disk'      => 'public',
                        'file_name' => $file_name,
                        'file_size' => $file_size,
                        'mime_type' => $mime_type]);
                Cache::forget("all_attachments");
            return $attachment;
            });

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Delete the specified attachment from the database.
     *
     * @param Attachment $attachment
     *
     * @return Attachment $attachment
     */

    public function deleteAttachment(Attachment $attachment){
        try{
            // Delete the file from the storage and then delete attachment from the database
            return DB::transaction(function () use ($attachment) {
                Cache::forget("all_attachments");
                return $attachment->delete();
            });

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

}
