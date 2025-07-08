<?php

namespace App\Services;

use App\Events\CommentCreated;
use App\Models\Comment;
use App\Traits\HandleServiceErrors;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CommentService
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
     * Get all comments from database
     *
     * @return array $arraydata
     */
    public function getAllComments()
    {
        try{
            $user = Auth::user();
            $comments = Cache::remember('all_comments', 3600, function() use($user){
                    return  Comment::VisibleTo($user)->get();
                });
            return $comments;

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Get a single comment with its relationships.
     *
     * @param  Comment $comment
     *
     * @return Comment $comment
     */
    public function showComment(Comment $comment)
    {
        try{
            return $comment->load([
                    'user',
                    'attachments',
                ])->loadCount('attachments');

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Add new comment to the database.
     *
     * @param array $arraydata
     * @param Model $model
     *
     * @return Comment $comment
     */
    public function createComment(array $data , Model $model)
    {
        try{
            $user = Auth::user();
            return DB::transaction(function () use ($data, $user, $model) {
                $data['user_id'] = $user->id;
                $comment = $model->comments()->create($data);
                event(new CommentCreated($comment));
                Cache::forget("all_comments");
                return $comment->load('user')
                                ->loadCount('attachments');
            });

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Update the specified comment in the database.
     *
     * @param array $arraydata
     * @param Comment $comment
     *
     * @return Comment $comment
     */

    public function updateComment(array $data, Comment $comment){
        try{
            $comment->update(array_filter($data));
            Cache::forget("all_comments");
            return $comment;

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Delete the specified comment from the database.
     *
     * @param Comment $comment
     *
     * @return Comment $comment
     */

    public function deleteComment(Comment $comment){
        try{

            return DB::transaction(function () use ($comment) {
                $comment->user()->delete();
                $comment->attachments()->delete();
                Cache::forget("all_comments");
                return $comment->delete();
            });

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

}
