<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TrainingService
{
    public function __construct(private InternalApiClient $client) {}

    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->client->paginate('biz/training/paginate', [
            'filters' => $filters, 'perPage' => $perPage, 'page' => request()->input('page', 1),
        ]);
    }

    public function find(int $courseId): ?array
    {
        try { return $this->client->get("biz/training/{$courseId}")['data'] ?? null; }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException) { return null; }
    }

    public function create(array $payload): int
    {
        return (int) ($this->client->post('biz/training', $payload)['id'] ?? 0);
    }

    public function update(int $courseId, array $payload): void
    {
        $this->client->put("biz/training/{$courseId}", $payload);
    }

    public function delete(int $courseId): void
    {
        $this->client->delete("biz/training/{$courseId}");
    }

    public function participantsPageData(int $courseId): array
    {
        return $this->client->get("biz/training/{$courseId}/participants-page")['data'] ?? [];
    }

    public function addParticipant(int $courseId, int $maNV): int
    {
        return (int) ($this->client->post("biz/training/{$courseId}/participants", ['MaNV' => $maNV])['id'] ?? 0);
    }

    public function updateParticipantResult(int $participantId, array $payload): void
    {
        $this->client->put("biz/training/participants/{$participantId}", $payload);
    }
}
