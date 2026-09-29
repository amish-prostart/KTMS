<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlayerRequest;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function create(Team $team): View
    {
        $player = new Player([
            'team_id' => $team->id,
            'role' => Player::ROLE_ALL_ROUNDER,
            'is_active' => true,
            // Suggest the next free shirt number.
            'jersey_number' => (int) $team->players()->max('jersey_number') + 1,
        ]);

        return view('players.create', compact('team', 'player'));
    }

    public function store(PlayerRequest $request, Team $team): RedirectResponse
    {
        $player = DB::transaction(function () use ($request, $team) {
            $player = $team->players()->create($request->validated());

            $this->enforceSingleCaptain($player);

            // Open the player's aggregate row immediately so reports list every
            // squad member, even before their first match.
            $player->statisticFor($team->tournament_id);

            return $player;
        });

        return redirect()
            ->route('teams.show', $team)
            ->with('status', "{$player->name} added to {$team->name}.");
    }

    public function show(Player $player): View
    {
        $player->load(['team.tournament']);

        $statistic = $player->statisticFor($player->team->tournament_id);

        $raids = $player->raids()
            ->with(['match.homeTeam', 'match.awayTeam', 'defendingTeam'])
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        $defenses = $player->defensiveActions()
            ->with(['match.homeTeam', 'match.awayTeam', 'raid.raider'])
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        return view('players.show', compact('player', 'statistic', 'raids', 'defenses'));
    }

    public function edit(Player $player): View
    {
        $team = $player->team;

        return view('players.edit', compact('player', 'team'));
    }

    public function update(PlayerRequest $request, Player $player): RedirectResponse
    {
        DB::transaction(function () use ($request, $player) {
            $player->update($request->validated());
            $this->enforceSingleCaptain($player);
        });

        return redirect()
            ->route('players.show', $player)
            ->with('status', 'Player updated.');
    }

    public function destroy(Player $player): RedirectResponse
    {
        $team = $player->team;
        $name = $player->name;

        $player->delete();

        return redirect()
            ->route('teams.show', $team)
            ->with('status', "{$name} removed from the squad.");
    }

    /**
     * A squad can only have one captain at a time.
     */
    protected function enforceSingleCaptain(Player $player): void
    {
        if (! $player->is_captain) {
            return;
        }

        Player::where('team_id', $player->team_id)
            ->whereKeyNot($player->id)
            ->where('is_captain', true)
            ->update(['is_captain' => false]);
    }
}
