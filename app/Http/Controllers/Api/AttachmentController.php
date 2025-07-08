<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attachment\StoreAttachmentRequest;
use App\Http\Requests\Attachment\UpdateAttachmentRequest;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Services\AttachmentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    use AuthorizesRequests;
    /**
     * This property is used to handle various operations related to attachments,
     * such as creating, updating, ...
     *
     * @var AttachmentService
     */
    protected $attachmentService;

    /**
     * Summary of middleware
     * @return array<Middleware|string>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin|team_owner|project_manager|member', only:['index','store','show' ,'update', 'destroy']),
        ];
    }

    /**
     * Constructor for the AttachmentController class.
     *
     * Initializes the $attachmentService property via dependency injection.
     *
     * @param AttachmentService $attachmentService
     */
    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    /**
     * This method return all attachments from database.
     */
    public function index()
    {
        return $this->success($this->attachmentService->getAllAttachments());
    }

    /**
     * Add a new attachments in the database using the attachmentService via the createAttachment method
     * passes the validated request data to createAttachment.
     *
     * @param StoreAttachmentRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAttachmentRequest $request )
    {
        $id = request()->route('attachable');
        $type = request()->segment(2);
        $model = match($type){
            'projects'  => Project::findOrfail($id),
            'tasks'     => Task::findOrfail($id),
            'comments'  => Comment::findOrfail($id),

        };

        $this->authorize('attachable',$model);
        return $this->success(
            $this->attachmentService->createAttachment($request->validated() , $model, $type)
                    , 'Attachment has been created successfully'
                    , 201);
    }

    /**
     * Get attachment from database.
     * using the attachmentService via the showAttachment method
     *
     * @param Attachment $attachment
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Attachment $attachment)
    {
        $this->authorize('view',$attachment);
        return $this->success($attachment);
    }

    /**
     * Update a attachment in the database using the attachmentService via the updateAttachment method.
     * passes the validated request data to updateAttachment.
     *
     * @param UpdateAttachmentRequest $request
     *
     * @param Attachment $attachment
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAttachmentRequest $request, Attachment $attachment)
    {
        $this->authorize('update',$attachment);
        return $this->success($this->attachmentService->updateAttachment($request->validated(), $attachment)
                                ,'updated successfuly');
    }

    /**
     * Remove the specified attachment from database.
     *
     * @param Attachment $attachment
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attachment $attachment)
    {
        $this->authorize('delete',$attachment);
        $this->attachmentService->deleteAttachment($attachment);
        return $this->success(null ,'Deleted successfuly');

    }
}
