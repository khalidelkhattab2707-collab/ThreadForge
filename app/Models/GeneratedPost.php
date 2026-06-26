<?php

namespace App\Models;

use App\Enums\PostStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedPost extends Model
{
    protected $fillable = [
        'raw_content_id',
        'hook_propose',
        'body_points',
        'technical_readability_score',
        'suggested_hashtags',
        'tone_compliance_justification',
        'status',
        'ai_conversation_id',
    ];

    protected $casts = [
        'body_points'        => 'array',
        'suggested_hashtags' => 'array',
        'status'             => PostStatusEnum::class,
    ];

    public function rawContent(): BelongsTo
    {
        return $this->belongsTo(RawContent::class);
    }
}