<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\PlayerStatistic;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $counts = [
            'tournaments' => Tournament::count(),
            'teams' => Team::count(),
            'players' => Player::count(),
            'matches' => GameMatch::count(),
        ];

        $liveMatches = GameMatch::query()
            ->with(['tournament', 'homeTeam', 'awayTeam'])
            ->whereIn('status', [GameMatch::STATUS_LIVE, GameMatch::STATUS_HALF_TIME])
            ->orderByDesc('id')
            ->get();

        $upcomingMatches = GameMatch::query()
            ->with(['tournament', 'homeTeam', 'awayTeam'])
            ->where('status', GameMatch::STATUS_SCHEDULED)
            ->orderBy('scheduled_at')
            ->orderBy('match_number')
            ->limit(5)
            ->get();

        $recentResults = GameMatch::query()
            ->with(['tournament', 'homeTeam', 'awayTeam', 'winnerTeam'])
            ->where('status', GameMatch::STATUS_COMPLETED)
            ->orderByDesc('completed_at')
            ->limit(5)
            ->get();

        $topRaiders = PlayerStatistic::query()
            ->with(['player.team', 'tournament'])
            ->where('total_raids', '>', 0)
            ->orderByDesc('raid_points')
            ->limit(5)
            ->get();

        $topDefenders = PlayerStatistic::query()
            ->with(['player.team', 'tournament'])
            ->where('total_defenses', '>', 0)
            ->orderByDesc('defense_points')
            ->limit(5)
            ->get();

        $tournaments = Tournament::query()
            ->withCount(['teams', 'matches'])
            ->latest('id')
            ->limit(4)
            ->get();

        return view('dashboard', compact(
            'counts',
            'liveMatches',
            'upcomingMatches',
            'recentResults',
            'topRaiders',
            'topDefenders',
            'tournaments',
        ));
    }
}
