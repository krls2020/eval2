<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TaskAttachment extends Model
{
    protected $fillable = ['task_id', 'disk_path', 'original_name', 'mime_type', 'size'];

    protected static function booted(): void
    {
        static::deleting(function (TaskAttachment $attachment) {
            Storage::disk('s3')->delete($attachment->disk_path);
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
