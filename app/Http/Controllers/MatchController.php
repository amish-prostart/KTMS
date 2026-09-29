<?php

namespace App\Http\Controllers;

use App\Http\Requests\MatchRequest;
use App\Models\GameMatch;
use App\Models\Tournament;
use App\Services\MatchService;
use App\Services\StatisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function __construct(
        protected MatchService $matches,
        protected StatisticsService $statistics,
    ) {}

    public function index(Request $request): View
    {
        $matches = GameMatch::query()
            ->with(['tournament', 'homeTeam', 'awayTeam'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('tournament'), fn ($query) => $query->where('tournament_id', $request->integer('tournament')))
            ->orderByRaw("FIELD(status, 'live', 'half_time', 'scheduled', 'completed')")
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $tournaments = Tournament::orderBy('name')->get();

        return view('matches.index', compact('matches', 'tournaments'));
    }

    public function create(Tournament $tournament): View
    {
        $teams = $this->matches->selectableTeams($tournament);

        $match = new GameMatch([
            'tournament_id' => $tournament->id,
            'half_duration_seconds' => $tournament->half_duration_seconds,
            'raid_duration_seconds' => $tournament->raid_duration_seconds,
            'players_per_side' => $tournament->players_per_side,
            'venue' => $tournament->venue,
            'match_number' => (int) $tournament->matches()->max('match_number') + 1,
        ]);

        return view('matches.create', compact('tournament', 'teams', 'match'));
    }

    public function store(MatchRequest $request, Tournament $tournament): RedirectResponse
    {
        if ($tournament->teams()->count() < 2) {
            return back()->with('error', 'Add at least two teams before creating a match.');
        }

        $match = $this->matches->createForTournament($tournament, $request->validated());

        return redirect()
            ->route('matches.show', $match)
            ->with('status', "Match created: {$match->title}.");
    }

    public function show(GameMatch $match): View
    {
        $match->load([
            'tournament',
            'homeTeam.players',
            'awayTeam.players',
            'winnerTeam',
        ]);

        $raids = $match->raids()
            ->with(['raider', 'raidingTeam', 'defensiveActions.defender'])
            ->orderByDesc('raid_number')
            ->get();

        $timeline = $match->events()
            ->with(['team', 'player'])
            ->orderByDesc('id')
            ->limit(60)
            ->get();

        $topPerformers = $match->tournament
            ->playerStatistics()
            ->with('player.team')
            ->whereIn('player_id', $match->raids()->select('raider_id'))
            ->orderByDesc('total_points')
            ->limit(5)
            ->get();

        return view('matches.show', compact('match', 'raids', 'timeline', 'topPerformers'));
    }

    public function edit(GameMatch $match): View
    {
        $tournament = $match->tournament;
        $teams = $this->matches->selectableTeams($tournament);

        return view('matches.edit', compact('match', 'tournament', 'teams'));
    }

    public function update(MatchRequest $request, GameMatch $match): RedirectResponse
    {
        $data = $request->validated();
        $lineupAffected = (int) $data['players_per_side'] !== $match->players_per_side
            || (int) $data['home_team_id'] !== $match->home_team_id
            || (int) $data['away_team_id'] !== $match->away_team_id;

        $match->update($data);

        // If the fixture or squad size changed, the stored lineup no longer applies.
        if ($lineupAffected) {
            $match->refresh();
            $this->matches->initialiseLineup($match);
        }

        return redirect()
            ->route('matches.show', $match)
            ->with('status', 'Match updated.');
    }

    public function destroy(GameMatch $match): RedirectResponse
    {
        $tournament = $match->tournament;
        $title = $match->title;

        // Collect the squads before deleting: the lineup rows cascade away with
        // the match, and their totals still need rebuilding afterwards.
        $playerIds = DB::table('match_player')->where('match_id', $match->id)->pluck('player_id');

        $match->delete();

        $this->statistics->recalculatePlayers($playerIds, $tournament->id);

        return redirect()
            ->route('tournaments.show', $tournament)
            ->with('status', "Match \"{$title}\" deleted.");
    }

    /**
     * Clear all scoring for a match and return it to its pre-match state.
     */
    public function reset(GameMatch $match): RedirectResponse
    {
        $this->matches->resetLiveState($match);

        return redirect()
            ->route('matches.show', $match)
            ->with('status', 'Match reset. Scores, raids and timers are back to their starting values.');
    }
}
