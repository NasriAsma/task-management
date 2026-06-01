<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Traits\StandardizedResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use StandardizedResponse;

    /**
     * Dashboard complet avec toutes les statistiques
     */
    public function index(Request $request)
    {
        $this->logRequest($request, __FUNCTION__);
        $this->authorize('viewGlobalStats', Task::class);

        $stats = [
            'tasks' => $this->getTaskStats(),
            'users' => $this->getUserStats(),
            'timeline' => $this->getTaskTimeline($request->query('period', 'monthly')),
            'summary' => $this->getSummary(),
        ];

        return $this->statsResponse($stats, 'dashboard');
    }

    /**
     * Statistiques complètes des tâches
     */
    public function tasks(Request $request)
    {
        $this->logRequest($request, __FUNCTION__);
        $this->authorize('viewGlobalStats', Task::class);

        $stats = $this->getTaskStats();

        return $this->statsResponse($stats, 'tasks');
    }

    /**
     * Statistiques complètes des utilisateurs
     */
    public function users(Request $request)
    {
        $this->logRequest($request, __FUNCTION__);
        $this->authorize('viewAny', User::class);

        $stats = $this->getUserStats();

        return $this->statsResponse($stats, 'users');
    }

    /**
     * Timeline des tâches par période
     */
    public function timeline(Request $request)
    {
        $this->logRequest($request, __FUNCTION__);
        $this->authorize('viewGlobalStats', Task::class);

        $period = $request->query('period', 'monthly'); // daily, weekly, monthly
        $timeline = $this->getTaskTimeline($period);

        return $this->statsResponse($timeline, "timeline_$period");
    }

    /**
     * Statistiques par utilisateur
     */
    public function userStatistics(Request $request)
    {
        $this->logRequest($request, __FUNCTION__);
        $this->authorize('viewGlobalStats', Task::class);

        $users = User::all();
        $userStats = [];

        foreach ($users as $user) {
            $tasks = Task::where('assigned_to', $user->id);
            $userStats[] = [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'total_tasks' => $tasks->count(),
                'completed' => $tasks->where('status', 'completed')->count(),
                'pending' => $tasks->where('status', 'pending')->count(),
                'in_progress' => $tasks->where('status', 'in_progress')->count(),
                'completion_rate' => $tasks->count() > 0 
                    ? round(($tasks->where('status', 'completed')->count() / $tasks->count()) * 100, 2)
                    : 0,
            ];
        }

        return $this->statsResponse(['users' => $userStats], 'user_statistics');
    }

    /**
     * Statistiques par priorité
     */
    public function priorityStatistics(Request $request)
    {
        $this->logRequest($request, __FUNCTION__);
        $this->authorize('viewGlobalStats', Task::class);

        $stats = [
            'high' => Task::where('priority', 'high')->count(),
            'medium' => Task::where('priority', 'medium')->count(),
            'low' => Task::where('priority', 'low')->count(),
        ];

        $percentages = $this->statsWithPercentages($stats);

        return $this->statsResponse([
            'counts' => $stats,
            'percentages' => $percentages,
        ], 'priorities');
    }

    /**
     * Obtenir les stats complètes des tâches
     */
    private function getTaskStats(): array
    {
        $total = Task::count();
        $completed = Task::where('status', 'completed')->count();
        $pending = Task::where('status', 'pending')->count();
        $inProgress = Task::where('status', 'in_progress')->count();
        $cancelled = Task::where('status', 'cancelled')->count();

        $counts = [
            'completed' => $completed,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'cancelled' => $cancelled,
        ];

        return [
            'total' => $total,
            'by_status' => $counts,
            'percentages' => $this->statsWithPercentages($counts),
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'overdue' => Task::where('deadline', '<', Carbon::now())->where('status', '!=', 'completed')->count(),
        ];
    }

    /**
     * Obtenir les stats complètes des utilisateurs
     */
    private function getUserStats(): array
    {
        $total = User::count();
        $active = User::where('is_active', true)->count();
        $inactive = User::where('is_active', false)->count();
        $twoFaEnabled = User::where('is_2fa_enabled', true)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'two_fa_enabled' => $twoFaEnabled,
            'two_fa_percentage' => $total > 0 ? round(($twoFaEnabled / $total) * 100, 2) : 0,
            'security_score' => $total > 0 ? round(($twoFaEnabled / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Obtenir la timeline des tâches
     */
    private function getTaskTimeline(string $period = 'monthly'): array
    {
        $dateFormat = match ($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-W%w',
            'monthly' => '%Y-%m',
            default => '%Y-%m',
        };

        $query = Task::query()
            ->selectRaw("DATE_FORMAT(created_at, '$dateFormat') as date, status, COUNT(*) as count")
            ->groupByRaw("DATE_FORMAT(created_at, '$dateFormat'), status")
            ->orderBy('date', 'asc')
            ->get();

        $timeline = [];
        foreach ($query as $item) {
            if (!isset($timeline[$item->date])) {
                $timeline[$item->date] = [
                    'date' => $item->date,
                    'completed' => 0,
                    'pending' => 0,
                    'in_progress' => 0,
                    'cancelled' => 0,
                ];
            }
            $timeline[$item->date][$item->status] = $item->count;
        }

        return [
            'period' => $period,
            'data' => array_values($timeline),
        ];
    }

    /**
     * Résumé général du dashboard
     */
    private function getSummary(): array
    {
        $taskStats = $this->getTaskStats();
        $userStats = $this->getUserStats();

        return [
            'total_tasks' => $taskStats['total'],
            'tasks_completed_today' => Task::whereDate('updated_at', Carbon::today())
                ->where('status', 'completed')
                ->count(),
            'total_users' => $userStats['total'],
            'active_users' => $userStats['active'],
            'health_score' => round(
                ($taskStats['completion_rate'] + $userStats['two_fa_percentage']) / 2,
                2
            ),
        ];
    }
}
