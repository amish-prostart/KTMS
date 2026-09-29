<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamRequest;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function create(Tournament $tournament): View
    {
        $team = new Team([
            'tournament_id' => $tournament->id,
            'primary_color' => '#1b4dff',
            'secondary_color' => '#ffffff',
        ]);

        return view('teams.create', compact('tournament', 'team'));
    }

    public function store(TeamRequest $request, Tournament $tournament): RedirectResponse
    {
        $data = $request->validated();
        $data['logo_path'] = $this->storeLogo($request);

        unset($data['logo'], $data['remove_logo']);

        $team = $tournament->teams()->create($data);

        return redirect()
            ->route('teams.show', $team)
            ->with('status', "Team \"{$team->name}\" added.");
    }

    public function show(Team $team): View
    {
        $team->load(['tournament', 'players']);

        // Season aggregates for the squad, keyed by player for easy lookup in the view.
        $statistics = $team->tournament
            ->playerStatistics()
            ->whereIn('player_id', $team->players->pluck('id'))
            ->get()
            ->keyBy('player_id');

        $matches = $team->matches()
            ->with(['homeTeam', 'awayTeam'])
            ->orderByDesc('id')
            ->get();

        return view('teams.show', compact('team', 'statistics', 'matches'));
    }

    public function edit(Team $team): View
    {
        $tournament = $team->tournament;

        return view('teams.edit', compact('team', 'tournament'));
    }

    public function update(TeamRequest $request, Team $team): RedirectResponse
    {
        $data = $request->validated();
        $removeLogo = (bool) ($data['remove_logo'] ?? false);
        unset($data['logo'], $data['remove_logo']);

        if ($newLogo = $this->storeLogo($request)) {
            $this->deleteLogo($team->logo_path);
            $data['logo_path'] = $newLogo;
        } elseif ($removeLogo) {
            $this->deleteLogo($team->logo_path);
            $data['logo_path'] = null;
        }

        $team->update($data);

        return redirect()
            ->route('teams.show', $team)
            ->with('status', 'Team updated.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $tournament = $team->tournament;
        $name = $team->name;

        $this->deleteLogo($team->logo_path);
        $team->delete();

        return redirect()
            ->route('tournaments.show', $tournament)
            ->with('status', "Team \"{$name}\" removed.");
    }

    /**
     * Persist an uploaded logo on the public disk and return its relative path.
     */
    protected function storeLogo(TeamRequest $request): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        return $request->file('logo')->store('team-logos', 'public');
    }

    protected function deleteLogo(?string $path): void
    {
        if (filled($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
