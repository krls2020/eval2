<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function index(Task $task): JsonResponse
    {
        return response()->json(
            $task->attachments->map(fn ($a) => $this->serialize($a))
        );
    }

    public function store(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $diskPath = 'tasks/' . $task->id . '/' . Str::ulid() . '_' . $file->getClientOriginalName();

        Storage::disk('s3')->put($diskPath, file_get_contents($file->getRealPath()), [
            'ContentType' => $file->getMimeType(),
        ]);

        $attachment = $task->attachments()->create([
            'disk_path' => $diskPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json($this->serialize($attachment), 201);
    }

    public function download(TaskAttachment $attachment): StreamedResponse
    {
        abort_unless(Storage::disk('s3')->exists($attachment->disk_path), 404);

        return Storage::disk('s3')->download($attachment->disk_path, $attachment->original_name);
    }

    public function destroy(TaskAttachment $attachment): JsonResponse
    {
        $attachment->delete();
        return response()->json(['ok' => true]);
    }

    private function serialize(TaskAttachment $attachment): array
    {
        return [
            'id' => $attachment->id,
            'task_id' => $attachment->task_id,
            'original_name' => $attachment->original_name,
            'mime_type' => $attachment->mime_type,
            'size' => $attachment->size,
            'download_url' => route('attachments.download', $attachment),
            'created_at' => $attachment->created_at?->toIso8601String(),
        ];
    }
}
