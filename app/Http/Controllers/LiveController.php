<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Services\MatchStateService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Public-facing broadcast scoreboard plus the shared state endpoint that every
 * live screen polls.
 */
class LiveController extends Controller
{
    public function __construct(protected MatchStateService $state) {}

    /**
     * Pro Kabaddi style scoreboard, intended for a projector or second screen.
     */
    public function show(GameMatch $match): View
    {
        $match->load(['tournament', 'homeTeam', 'awayTeam']);

        $snapshot = $this->state->snapshot($match);

        return view('live.display', compact('match', 'snapshot'));
    }

    /**
     * Single source of truth for the scoring console, timer console and display.
     */
    public function state(GameMatch $match): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'state' => $this->state->snapshot($match),
        ]);
    }
}
