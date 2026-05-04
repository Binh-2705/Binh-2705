<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RecruitmentService
{
    public function __construct(private InternalApiClient $client) {}

    // ─── Campaigns ─────────────────────────────────────────────────────────────

    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->client->paginate('biz/recruitment/paginate', [
            'filters' => $filters, 'perPage' => $perPage, 'page' => request()->input('page', 1),
        ]);
    }

    public function find(int $id): ?array
    {
        try { return $this->client->get("biz/recruitment/{$id}")['data'] ?? null; }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException) { return null; }
    }

    public function create(array $payload): int
    {
        return (int) ($this->client->post('biz/recruitment', $payload)['id'] ?? 0);
    }

    public function update(int $id, array $payload): void
    {
        $this->client->put("biz/recruitment/{$id}", $payload);
    }

    public function delete(int $id): void
    {
        $this->client->delete("biz/recruitment/{$id}");
    }

    public function campaignOptions(): array
    {
        $data = $this->client->get('biz/recruitment/campaign-options')['data'] ?? [];
        return array_map(fn($c) => (object) $c, $data);
    }

    // ─── Candidates ────────────────────────────────────────────────────────────

    public function paginateCandidates(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->client->paginate('biz/recruitment/candidates/paginate', [
            'filters' => $filters, 'perPage' => $perPage, 'page' => request()->input('page', 1),
        ]);
    }

    public function findCandidate(int $id): ?array
    {
        try { return $this->client->get("biz/recruitment/candidates/{$id}")['data'] ?? null; }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException) { return null; }
    }

    public function createCandidate(array $payload): int
    {
        return (int) ($this->client->post('biz/recruitment/candidates', $payload)['id'] ?? 0);
    }

    // ─── Applications ──────────────────────────────────────────────────────────

    public function paginateApplications(int $campaignId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->client->paginate("biz/recruitment/{$campaignId}/applications/paginate", [
            'filters' => $filters, 'perPage' => $perPage, 'page' => request()->input('page', 1),
        ]);
    }

    public function attachCandidate(int $campaignId, array $payload): int
    {
        return (int) ($this->client->post("biz/recruitment/{$campaignId}/applications", $payload)['id'] ?? 0);
    }

    public function findApplication(int $id): ?array
    {
        try { return $this->client->get("biz/recruitment/applications/{$id}")['data'] ?? null; }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException) { return null; }
    }

    public function updateApplicationStatus(int $id, string $status): void
    {
        $this->client->put("biz/recruitment/applications/{$id}/status", ['TrangThai' => $status]);
    }

    public function updateKanban(int $id, array $payload): void
    {
        $this->client->put("biz/recruitment/applications/{$id}/kanban", $payload);
    }

    // ─── Interviews ────────────────────────────────────────────────────────────

    public function listInterviews(int $applicationId): array
    {
        return $this->client->get("biz/recruitment/applications/{$applicationId}/interviews")['data'] ?? [];
    }

    public function listReviews(int $applicationId): array
    {
        try {
            return $this->client->get("biz/recruitment/applications/{$applicationId}/reviews")['data'] ?? [];
        } catch (\RuntimeException) {
            return [];
        }
    }

    public function storeInterview(int $applicationId, array $payload): int
    {
        return (int) ($this->client->post("biz/recruitment/applications/{$applicationId}/interviews", $payload)['id'] ?? 0);
    }

    public function storeReview(int $interviewId, array $payload): int
    {
        return (int) ($this->client->post("biz/recruitment/interviews/{$interviewId}/reviews", $payload)['id'] ?? 0);
    }
}
