<?php

namespace App\Services;

use App\Jobs\SendErrorMessage;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetsService
{
    /**
     * store a file
     * @param  $file 
     * 
     */

    public static function storeFile($file)
    {
        $originalName = $file->getClientOriginalName();

        // Ensure the file extension is valid and there is no path traversal in the file name
        if (preg_match('/\.[^.]+\./', $originalName)) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "الملف الذي قمت بادخاله غير مقبول",
                ],
                422
            ));
        }

        // Check for path traversal attack (e.g., using ../ or ..\ or / to go up directories)
        if (strpos($originalName, '..') !== false || strpos($originalName, '/') !== false || strpos($originalName, '\\') !== false) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "الملف الذي قمت بادخاله غير مقبول",
                ],
                422
            ));
        }

        // Validate the MIME type to ensure it's an file
        $allowedMimeTypes = ['application/pdf', 'application/docx', 'application/doc'];
        $mime_type = $file->getClientMimeType();

        if (!in_array($mime_type, $allowedMimeTypes)) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => 'pdf,docx,doc' . "لاحقة الملف الذي ادخلتها خاطئة. يجب ادخال ",
                ],
                422
            ));
        }
        try {
            // Generate a safe, random file name
            $fileName = Str::random(32);

            $extension = $file->getClientOriginalExtension(); // Safe way to get file extension
            // Store the file securely
            $path =  Storage::putFileAs('Attachments', $file, $fileName . '.' . $extension);

            // Get the full URL path of the stored file
            $url = asset($path);
        } catch (Exception $e) {
            Log::error("error in store file"  . $e->getMessage());
            SendErrorMessage::dispatch("error in store file"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
        return [
            'name' => $originalName ?? 'New File',
            'path' => $url,
            'mime_type' => $mime_type
        ];
    }
}