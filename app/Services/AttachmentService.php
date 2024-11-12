<?php

namespace App\Services;

use App\Http\Resources\AttachmentResource;
use App\Jobs\SendErrorMessage;
use App\Models\Attachment;
use App\Services\AssetsService;
use App\Models\Task;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    /**
     * show all attachments
     * @param int $task_id 
     * @return AttachmentResource $attachments 
     */
    public function allAttachments($task_id)
    {
        try {
            $task = Task::findOrFail($task_id);
            $attachments =  $task->attachments()->with('user.role')->get();
            $attachments = AttachmentResource::collection($attachments);
            return  $attachments;
        } catch (Exception $e) {
            Log::error("error in get all attachments"  . $e->getMessage());

            SendErrorMessage::dispatch("error in get all attachments"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (ModelNotFoundException $e) {
            Log::error("error in get all attachments"  . $e->getMessage());
            SendErrorMessage::dispatch("error in get all attachments"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in  get all attachments"  . $e->getMessage());
            SendErrorMessage::dispatch("error in get all attachments"  . $e->getMessage());

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
     * show a attachment 
     * @param  int $attachment_id  
     * @return AttachmentResource $attachment 
     */
    public function oneAttachment($attachment_id)
    {
        try {
            $attachment = Attachment::findOrFail($attachment_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in  show a  attachment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  show a  attachment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $attachment = $attachment->load('user.role');

            $attachment = AttachmentResource::make($attachment);
            return $attachment;
        } catch (Exception $e) {
            Log::error("error in  show a  attachment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  show a  attachment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in  show a attachment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  show a  attachment"  . $e->getMessage());

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
     * create a  new attachment
     * @param  array $commentData  
     * @return AttachmentResource attachment  
     */
    public function createAttachment($attachmentData, $task_id)
    {
        try {
            $task = Task::findOrFail($task_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in found task"  . $e->getMessage());
            SendErrorMessage::dispatch("error in assign employee" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        $attachmentInfo = AssetsService::storeFile($attachmentData);
        try {
            $attachment =  $task->attachments()->create([
                'name' => $attachmentInfo['name'],
                'path' => $attachmentInfo['path'],
                'mime_type' => $attachmentInfo['mime_type'],
                'user_id' => Auth::user()->id
            ]);
            $attachment  = $attachment->load('user.role');
            $attachment = AttachmentResource::make($attachment);
            return  $attachment;
        } catch (Exception $e) {
            Log::error("error in create a  attachment"  . $e->getMessage());

            SendErrorMessage::dispatch("error in  create a  attachment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in  create a  attachment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  create a  attachment"  . $e->getMessage());

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
     * @param int $attachment_id 
     * @param  array $attachmentData  
     * @return AttachmentResource attachment  
     */
    public function updateAttachment($attachmentData, $attachment)
    {
        $attachmentInfo = AssetsService::storeFile($attachmentData);
        try {
            Storage::delete($attachment->path);
            $attachment->update([
                'name' => $attachmentInfo['name'],
                'path' => $attachmentInfo['path'],
                'mime_type' => $attachmentInfo['mime_type'],
            ]);
            $attachment = Attachment::find($attachment->id);
            $attachment  = $attachment->load('user.role');
            $attachment = AttachmentResource::make($attachment);
            return  $attachment;
        } catch (Exception $e) {
            Log::error("error in   update a  attachment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  update a  attachment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (ModelNotFoundException $e) {
            Log::error("error in update a  attachment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  update a  attachment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in  update a  attachment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  update a  attachment"  . $e->getMessage());

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
     *  delete a  attachment
     * @param Attachment $attachment_id 
     */
    public function deleteAttachment($attachment)
    {
        try {
            Storage::delete($attachment->path);
            $attachment->delete();
        } catch (Exception $e) {
            Log::error("error in delete a  attachment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  delete a  attachment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (ModelNotFoundException $e) {
            Log::error("error in download a  attachment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  delete a  attachment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
    }
    /**
     *  download a  attachment
     * @param int $attachment_id 
     */
    public function downloadAttachment($attachment_id)
    {

        try {
            $attachment = Attachment::findOrFail($attachment_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in download a  attachment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  download a  attachment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $headers = array(
                "Content-Type: $attachment->mime_type",
            );
            return  Storage::download($attachment->path, $attachment->name, $headers);
        } catch (Exception $e) {
            Log::error("error in download a  attachment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  download a  attachment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
}
