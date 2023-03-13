<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Task extends Model implements HasMedia
{
    use HasFactory, HasTags, InteractsWithMedia;

    protected $guarded = ['id'];

    protected $casts = [
        'due_date' => 'datetime'
    ];

    public function assignee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
//        return $this->belongsTo(User::class)->withDefault([
//            'id' => null,
//            'name' => 'User',
//            'email' => 'user@example.com',
//        ]);

        return $this->belongsTo(User::class);
    }

    public function status(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
}
