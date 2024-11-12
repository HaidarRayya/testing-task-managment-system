<?php

namespace App\Http\Controllers;

use App\Enums\UserPermission;
use App\Http\Requests\Attachment\StoreAttachmentRequest;
use App\Http\Requests\Attachment\UpdateAttachmentRequest;
use App\Models\Attachment;
use App\Models\Task;
use App\Services\AttachmentService;
use App\Services\AuthService;
use App\Jobs\SendErrorMessage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class AttachmentController extends Controller
{
    protected $attachmentService;
    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }
    /**
     * Display a listing of the resource.
     */

    /**
     * get all  comments
     * 
     * @param Task $task 
     *
     * @return response  of the status of operation : permissions 
     */
    public function index(int $task_id)
    {
        AuthService::canDo(UserPermission::GET_ATTACHMENT->value);
        $attachments = $this->attachmentService->allAttachments($task_id);
        return response()->json([
            'status' => 'success',
            'data' => [
                'attachments' => $attachments
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttachmentRequest $request, int $task_id)
    {
        AuthService::canDo(UserPermission::CREATE_ATTACHMENT->value);
        $attachmentData = $request->file('file');
        $attachment = $this->attachmentService->createAttachment($attachmentData, $task_id);
        return response()->json([
            'status' => 'success',
            'data' => [
                'attachment' => $attachment
            ]
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $task_id, $attachment_id)
    {
        AuthService::canDo(UserPermission::GET_ATTACHMENT->value);

        $attachment = $this->attachmentService->oneAttachment($attachment_id);
        return response()->json([
            'status' => 'success',
            'data' => [
                'attachment' => $attachment
            ]
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttachmentRequest $request, int  $task_id,  int $attachment_id)
    {
        AuthService::canDo(UserPermission::UPDATE_ATTACHMENT->value);
        try {
            $attachment = Attachment::findOrFail($attachment_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in found a  attachment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  download a  attachment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        if (Auth::user()->id != $attachment->user_id) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية'",
                ],
                403
            ));
        };
        $attachmentData = $request->file('file');
        $attachment = $this->attachmentService->updateAttachment($attachmentData, $attachment);
        return response()->json([
            'status' => 'success',
            'data' => [
                'attachment' => $attachment
            ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $task_id, int  $attachment_id)
    {
        AuthService::canDo(UserPermission::DELETE_ATTACHMENT->value);
        try {
            $attachment = Attachment::findOrFail($attachment_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in found a  attachment"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  download a  attachment"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        if (Auth::user()->id != $attachment->user_id) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية'",
                ],
                403
            ));
        };
        $this->attachmentService->deleteAttachment($attachment);
        return response()->json(status: 204);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function download(int $task_id, int  $attachment_id)
    {
        AuthService::canDo(UserPermission::DOWNLOAD_ATTACHMENT->value);
        $file = $this->attachmentService->downloadAttachment($attachment_id);
        return $file;
    }
}
