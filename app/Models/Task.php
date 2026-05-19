<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Task extends Model
{
    use Searchable;

    protected $fillable = ['board_list_id', 'title', 'description', 'position'];

    public function list(): BelongsTo
    {
        return $this->belongsTo(BoardList::class, 'board_list_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->orderByDesc('created_at');
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing('list');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => (string) $this->description,
            'board_list_id' => $this->board_list_id,
            'list_name' => $this->list?->name,
        ];
    }
}
