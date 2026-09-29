<?php

namespace App\Http\Controllers;

use App\Models\DefensiveAction;
use App\Models\GameMatch;
use App\Models\Raid;
use App\Services\MatchService;
use App\Services\MatchStateService;
use App\Services\ScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The scoring operator's half of the dual-operator setup: points, raids,
 * defensive actions and court corrections.
 */
class ScoringController extends Controller
{
    public function __construct(
        protected ScoringService $scoring,
        protected MatchStateService $state,
        protected MatchService $matches,
    ) {}

    public function console(GameMatch $match): View
    {
        $match->load(['tournament', 'homeTeam', 'awayTeam']);

        $lineups = [
            'home' => $this->state->selectablePlayers($match, $match->home_team_id),
            'away' => $this->state->selectablePlayers($match, $match->away_team_id),
        ];

        $snapshot = $this->state->snapshot($match);

        return view('live.scoring', compact('match', 'lineups', 'snapshot'));
    }

    public function adjustScore(Request $request, GameMatch $match): JsonResponse
    {
        $data = $request->validate([
            'team_id' => ['required', 'integer'],
            'delta' => ['required', 'integer', 'between:-50,50'],
        ]);

        $this->scoring->adjustScore($match, $data['team_id'], $data['delta']);

        return $this->stateResponse($match);
    }

    public function toggleRaid(Request $request, GameMatch $match): JsonResponse
    {
        $data = $request->validate([
            'active' => ['nullable', 'boolean'],
            'raiding_team_id' => ['nullable', 'integer'],
        ]);

        $this->scoring->toggleRaid(
            $match,
            array_key_exists('active', $data) ? (bool) $data['active'] : null,
            $data['raiding_team_id'] ?? null,
        );

        return $this->stateResponse($match);
    }

    public function recordRaid(Request $request, GameMatch $match): JsonResponse
    {
        $data = $request->validate([
            'raider_id' => ['required', 'integer', 'exists:players,id'],
            'result' => ['required', Rule::in(array_keys(Raid::RESULTS))],
            'touch_points' => ['nullable', 'integer', 'min:0', 'max:7'],
            'is_bonus' => ['nullable', 'boolean'],
            'is_do_or_die' => ['nullable', 'boolean'],
            'defender_out_ids' => ['nullable', 'array'],
            'defender_out_ids.*' => ['integer', 'exists:players,id'],
            'tackler_ids' => ['nullable', 'array'],
            'tackler_ids.*' => ['integer', 'exists:players,id'],
            'beaten_defender_ids' => ['nullable', 'array'],
            'beaten_defender_ids.*' => ['integer', 'exists:players,id'],
            'action_type' => ['nullable', Rule::in(array_keys(DefensiveAction::ACTION_TYPES))],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->scoring->recordRaid($match, $data);

        return $this->stateResponse($match, $result['messages']);
    }

    public function recordDefense(Request $request, GameMatch $match): JsonResponse
    {
        $data = $request->validate([
            'defender_id' => ['required', 'integer', 'exists:players,id'],
            'action_type' => ['required', Rule::in(array_keys(DefensiveAction::ACTION_TYPES))],
            'is_successful' => ['required', 'boolean'],
            'points_awarded' => ['nullable', 'integer', 'min:0', 'max:5'],
            'raid_id' => ['nullable', 'integer', 'exists:raids,id'],
            'raider_id' => ['nullable', 'integer', 'exists:players,id'],
            'send_raider_out' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->scoring->recordDefensiveAction($match, $data);

        return $this->stateResponse($match, $result['messages']);
    }

    public function adjustCourt(Request $request, GameMatch $match): JsonResponse
    {
        $data = $request->validate([
            'team_id' => ['required', 'integer'],
            'delta' => ['required', 'integer', 'between:-12,12'],
        ]);

        $this->scoring->adjustPlayersOnCourt($match, $data['team_id'], $data['delta']);

        return $this->stateResponse($match);
    }

    public function revive(Request $request, GameMatch $match): JsonResponse
    {
        $data = $request->validate([
            'team_id' => ['required', 'integer'],
        ]);

        $this->scoring->reviveTeam($match, $data['team_id']);

        return $this->stateResponse($match);
    }

    public function undo(GameMatch $match): JsonResponse
    {
        $result = $this->scoring->undoLastAction($match);

        return $this->stateResponse($match, [$result['message']]);
    }

    public function setStatus(Request $request, GameMatch $match): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(GameMatch::STATUSES))],
        ]);

        $this->matches->setStatus($match, $data['status']);

        return $this->stateResponse($match);
    }

    /**
     * Every scoring endpoint answers with the full snapshot, so the operator's
     * screen redraws from a single source of truth.
     *
     * @param  array<int, string>  $messages
     */
    protected function stateResponse(GameMatch $match, array $messages = []): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'messages' => array_values(array_filter($messages)),
            'state' => $this->state->snapshot($match->refresh()),
        ]);
    }
}
