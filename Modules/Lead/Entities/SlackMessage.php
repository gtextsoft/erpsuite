<?php

namespace Modules\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SlackMessage  extends Model
{
   use HasFactory;

    protected $table = 'slack_messages';

    protected $fillable = [
        'user_id',
        'channel',
        'message',
        'from_slack',
    ];

    protected $casts = [
        'from_slack' => 'boolean',
    ];

    /**
     * Relationship: Message belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
