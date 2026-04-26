<?php

namespace App\Repositories\Contracts\Communication;

use App\Models\Communication\CommunicationConversation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ConversationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): CommunicationConversation;

    public function create(array $attributes): CommunicationConversation;

    public function update(CommunicationConversation $conversation, array $attributes): CommunicationConversation;

    public function delete(CommunicationConversation $conversation): void;
}
