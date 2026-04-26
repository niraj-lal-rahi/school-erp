<?php

namespace App\Models\Communication;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationConversation extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'communication_conversations';

    protected $fillable = [
        'school_id',
        'conversation_type',
        'title',
        'created_by',
        'status',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CommunicationMessage::class, 'conversation_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class, 'conversation_id');
    }
}
