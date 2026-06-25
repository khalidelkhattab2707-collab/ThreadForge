<?php


namespace App\Models;

use App\Enums\RawContentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RawContent extends Model
{
     protected $fillable = [
        'user_id',
        'campaign_blueprint_id',
        'content',
        'status',
    ];
     protected $casts = [
        'status' => RawContentStatusEnum::class,
    ];
        public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaignBlueprint(): BelongsTo
    {
        return $this->belongsTo(CampaignBlueprint::class);
    }

    public function generatedPost(): HasOne
    {
        return $this->hasOne(GeneratedPost::class);
    }
}
