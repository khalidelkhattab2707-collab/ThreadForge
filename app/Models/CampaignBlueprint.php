<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class CampaignBlueprint extends Model
{
  protected $fillable=[
    'user_id','name','target_audience','tone',
    'max_length','forbidden_words','max_hashtags',
  ];
  protected $casts=[
    'forbidden_words'=>'array',
  ];

  public function user():BlongsTo
   {
    return $this->BelongsTo(User::class);
   }
   public function rawContents():HasMany
   {
    return $this->HasMany(RawContents::class);
   }
}
