<?php

namespace App\Http\Controllers;

use App\Http\Requests\TournamentRequest;
use App\Models\GameMatch;
use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(Request $request): View
    {
        $tournaments = Tournament::query()
            ->withCount(['teams', 'matches'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('city', 'like', $term));
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('tournaments.index', compact('tournaments'));
    }

    public function create(): View
    {
        $tournament = new Tournament([
            'status' => Tournament::STATUS_UPCOMING,
            'half_duration_seconds' => 1200,
            'raid_duration_seconds' => 30,
            'players_per_side' => 7,
        ]);

        return view('tournaments.create', compact('tournament'));
    }

    public function store(TournamentRequest $request): RedirectResponse
    {
        $tournament = Tournament::create($request->validated());

        return redirect()
            ->route('tournaments.show', $tournament)
            ->with('status', "Tournament \"{$tournament->name}\" created.");
    }

    public function show(Tournament $tournament): View
    {
        $tournament->load(['teams' => fn ($query) => $query->withCount('players')->orderBy('name')]);

        $matches = $tournament->matches()
            ->with(['homeTeam', 'awayTeam'])
            ->orderByRaw("FIELD(status, 'live', 'half_time', 'scheduled', 'completed')")
            ->orderBy('match_number')
            ->get();

        $leaders = $tournament->playerStatistics()
            ->with('player.team')
            ->orderByDesc('total_points')
            ->limit(5)
            ->get();

        return view('tournaments.show', compact('tournament', 'matches', 'leaders'));
    }

    public function edit(Tournament $tournament): View
    {
        return view('tournaments.edit', compact('tournament'));
    }

    public function update(TournamentRequest $request, Tournament $tournament): RedirectResponse
    {
        $tournament->update($request->validated());

        return redirect()
            ->route('tournaments.show', $tournament)
            ->with('status', 'Tournament updated.');
    }

    public function destroy(Tournament $tournament): RedirectResponse
    {
        $name = $tournament->name;

        // Guard against wiping a tournament that still has a match in progress.
        $liveMatches = $tournament->matches()
            ->whereIn('status', [GameMatch::STATUS_LIVE, GameMatch::STATUS_HALF_TIME])
            ->count();

        if ($liveMatches > 0) {
            return redirect()
                ->route('tournaments.show', $tournament)
                ->with('error', 'Finish or reset the live matches before deleting this tournament.');
        }

        $tournament->delete();

        return redirect()
            ->route('tournaments.index')
            ->with('status', "Tournament \"{$name}\" deleted.");
    }
}
