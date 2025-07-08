<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Services\CommentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class CommentContoller extends Controller
{
    use AuthorizesRequests;
    /**
     * This property is used to handle various operations related to comments,
     * such as creating, updating, ...
     *
     * @var CommentService
     */
    protected $commentService;

    /**
     * Summary of middleware
     * @return array<Middleware|string>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin|team_owner|project_manager|member', only:['index','storeTaskComment','storeProjectComment','show', 'update', 'destroy']),
        ];
    }

    /**
     * Constructor for the CommentController class.
     *
     * Initializes the $commentService property via dependency injection.
     *
     * @param CommentService $commentService
     */
    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * This method return all comments from database.
     */
    public function index()
    {
        return $this->success($this->commentService->getAllComments());
    }

    /**
     * Add a new comment on task in the database using the commentService via the createComment method
     * passes the validated request data to createComment.
     *
     * @param StoreCommentRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function storeTaskComment(StoreCommentRequest $request ,Task $task)
    {
        return $this->success(
            $this->commentService->createComment($request->validated() , $task)
                    , 'Comment has been created successfully'
                    , 201);
    }

    /**
     * Add a new comment on task in the database using the commentService via the createComment method
     * passes the validated request data to createComment.
     *
     * @param StoreCommentRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function storeProjectComment(StoreCommentRequest $request ,Project $project)
    {
        return $this->success(
            $this->commentService->createComment($request->validated() , $project)
                    , 'Comment has been created successfully'
                    , 201);
    }

    /**
     * Get comment from database.
     * using the commentService via the showComment method
     *
     * @param Comment $comment
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        $this->authorize('view',$comment);
        return $this->success($this->commentService->showComment($comment));
    }

    /**
     * Update a comment in the database using the commentService via the updateComment method.
     * passes the validated request data to updateComment.
     *
     * @param UpdateCommentRequest $request
     *
     * @param Comment $comment
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $this->authorize('update',$comment);
            return $this->success($this->commentService->updateComment($request->validated(), $comment)
                                ,'updated successfuly');
    }

    /**
     * Remove the specified comment from database.
     *
     * @param Comment $comment
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete',$comment);
        $this->commentService->deleteComment($comment);
        return $this->success(null ,'Deleted successfuly');
    }
}
