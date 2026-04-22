<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ChatbotMonitorService
{
    private function connection(): string
    {
        return (string) config('service_registry.services.chatbot.connection', config('database.default'));
    }

    public function paginateSessions(int $perPage = 15): LengthAwarePaginator
    {
        return DB::connection($this->connection())
            ->table('chatbot_sessions as cs')
            ->leftJoin('chatbot_messages as cm', 'cm.session_id', '=', 'cs.id')
            ->leftJoin('chatbot_action_drafts as cad', 'cad.session_id', '=', 'cs.id')
            ->select([
                'cs.id',
                'cs.session_key',
                'cs.ma_tk',
                'cs.username',
                'cs.role_name',
                'cs.created_at',
                'cs.last_interaction_at',
                DB::raw('COUNT(DISTINCT cm.id) as MessageCount'),
                DB::raw('COUNT(DISTINCT cad.id) as DraftCount'),
            ])
            ->groupBy('cs.id', 'cs.session_key', 'cs.ma_tk', 'cs.username', 'cs.role_name', 'cs.created_at', 'cs.last_interaction_at')
            ->orderByDesc('cs.last_interaction_at')
            ->paginate($perPage);
    }

    public function findSession(int $sessionId): array
    {
        $connection = DB::connection($this->connection());
        $session = $connection->table('chatbot_sessions')->where('id', $sessionId)->first();

        abort_if($session === null, 404);

        return [
            'session' => (array) $session,
            'messages' => $connection->table('chatbot_messages')->where('session_id', $sessionId)->orderBy('created_at')->get(),
            'drafts' => $connection->table('chatbot_action_drafts')->where('session_id', $sessionId)->orderByDesc('created_at')->get(),
        ];
    }
}