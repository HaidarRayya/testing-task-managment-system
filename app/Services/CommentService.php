<?php

namespace App\Services;

use App\Http\Resources\CommentResource;
use App\Jobs\SendErrorMessage;
use App\Models\Comment;
use App\Models\Task;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class CommentService
{
    /**
     * show all comments
     * @param int $task_id
     * @return CommentResource $comments 
     */
    public function allComments($task_id)
    {
        try {
            $task = Task::findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in get all comments"  . $e->getMessage());
            SendErrorMessage::dispatch("error in get all commentst"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $comments =  $task->comments()->with('user.role')->get();
            $comments = CommentResource::collection($comments);
            return  $comments;
        } catch (Exception $e) {
            Log::error("error in get all comments"  . $e->getMessage());
            SendErrorMessage::dispatch("error in get all commentst"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in get all comments"  . $e->getMessage());
            SendErrorMessage::dispatch("error in get all commentst"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
    }
    /**
     * show a comment 
     * @param int $comment_id  
     * @return CommentResource $comment 
     */
    public function oneComment($comment_id)
    {
        try {
            $comment = Comment::findOrFail($comment_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in  show a  comment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  show a  comment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $comment = $comment->load('user.role');
            $comment = CommentResource::make($comment);
            return $comment;
        } catch (Exception $e) {
            Log::error("error in  show a  comment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  show a  comment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in  show a  comment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  show a  comment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
    }
    /**
     * create a  new comment
     * @param  array $commentData  
     * @return CommentResource comment  
     */
    public function createComment($commentData, $task_id)
    {
        try {
            $task = Task::findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error(message: "error in create a  comment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  create a  comment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {

            $comment = $task->comments()->create([
                'user_id' => Auth::user()->id,
                'description' => $commentData['description']
            ]);
            $comment  = $comment->load('user.role');
            $comment = CommentResource::make($comment);
            return  $comment;
        } catch (Exception $e) {
            Log::error("error in create a  comment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  create a  comment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error(message: "error in create a  comment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  create a  comment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
    }
    /**
     * update a comment
     * @param Comment $comment_id  
     * @param  array $commentData  
     * @return CommentResource comment  
     */
    public function updateComment($commentData, $comment)
    {
        try {
            $comment->update([
                'description' => $commentData['description']
            ]);
            $comment = Comment::where('id', '=', $comment->id)->select('id', 'description', 'user_id')->first();
            $comment = $comment->load('user.role');
            $comment  = CommentResource::make($comment);
            return  $comment;
        } catch (Exception $e) {
            Log::error("error in   update a  comment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  update a  comment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (ModelNotFoundException $e) {
            Log::error("error in   update a  comment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  update a  comment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in   update a  comment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  update a  comment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
    }

    /**
     *  delete a  comment
     * @param Comment $comment
     */
    public function deleteComment($comment)
    {
        try {
            $comment->delete();
        } catch (Exception $e) {
            Log::error("error in delete a  comment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  delete a  comment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (ModelNotFoundException $e) {
            Log::error("error in delete a  comment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  delete a  comment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
    }
}
