@extends('layouts.console')

@section('title', 'Timer console')
@section('console-name', 'Timer operator')

@section('console-switch')
    <a href="{{ route('scoring.console', $match) }}" class="btn btn-sm btn-outline-warning">
        <i class="bi bi-joystick me-1"></i><span class="d-none d-md-inline">Scoring</span>
    </a>
@endsection

@section('content')
    {{-- Read-only scoreline so the timer operator has context --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="row align-items-center text-center g-2">
                <div class="col-5 d-flex align-items-center justify-content-center gap-2">
                    <x-team-logo :team="$match->homeTeam" :size="34" />
                    <span class="fw-bold text-truncate">{{ $match->homeTeam->name }}</span>
                </div>
                <div class="col-2">
                    <span class="fs-3 fw-bold">
                        <span data-score="home">{{ $match->home_score }}</span>
                        <span class="text-muted">–</span>
                        <span data-score="away">{{ $match->away_score }}</span>
                    </span>
                </div>
                <div class="col-5 d-flex align-items-center justify-content-center gap-2">
                    <span class="fw-bold text-truncate">{{ $match->awayTeam->name }}</span>
                    <x-team-logo :team="$match->awayTeam" :size="34" />
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 console-actions">
        {{-- ------------------------------------------------ game clock --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-hourglass-split me-1"></i>Game clock</span>
                    <span class="small text-muted">
                        Half <span data-half>{{ $match->current_half }}</span>
                    </span>
                </div>
                <div class="card-body text-center">
                    <div class="clock-face clock-game mb-2" data-game-clock>
                        {{ \App\Models\GameMatch::formatClock($match->effectiveGameClockRemaining()) }}
                    </div>
                    <p class="text-muted mb-4">
                        <span class="clock-running-dot" data-game-dot></span>
                        <span data-game-status>Paused</span>
                        · half is {{ \App\Models\GameMatch::formatClock($match->half_duration_seconds) }} long
                    </p>

                    <div class="d-grid gap-2">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-lg" data-action="game-start">
                                <i class="bi bi-play-fill"></i> Start
                            </button>
                            <button type="button" class="btn btn-warning btn-lg" data-action="game-pause">
                                <i class="bi bi-pause-fill"></i> Pause
                            </button>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-light" data-action="game-adjust" data-seconds="-60">−60s</button>
                            <button type="button" class="btn btn-outline-light" data-action="game-adjust" data-seconds="-10">−10s</button>
                            <button type="button" class="btn btn-outline-light" data-action="game-adjust" data-seconds="10">+10s</button>
                            <button type="button" class="btn btn-outline-light" data-action="game-adjust" data-seconds="60">+60s</button>
                        </div>

                        <button type="button" class="btn btn-outline-danger" data-action="game-reset">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset to full half
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ------------------------------------------------ raid clock --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-stopwatch me-1"></i>Raid clock</span>
                    <span class="raid-indicator off" data-raid-indicator>
                        <i class="bi bi-circle-fill"></i> <span data-raid-indicator-label>no raid</span>
                    </span>
                </div>
                <div class="card-body text-center">
                    <div class="clock-face clock-raid mb-2" data-raid-clock>
                        {{ \App\Models\GameMatch::formatClock($match->effectiveRaidClockRemaining()) }}
                    </div>
                    <p class="text-muted mb-4">
                        <span class="clock-running-dot" data-raid-dot></span>
                        <span data-raid-status>Paused</span>
                        · resets to {{ $match->raid_duration_seconds }}s for every raid
                    </p>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-warning btn-lg" data-action="raid-restart">
                            <i class="bi bi-arrow-repeat me-1"></i>New raid ({{ $match->raid_duration_seconds }}s)
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-success" data-action="raid-start">
                                <i class="bi bi-play-fill"></i> Start
                            </button>
                            <button type="button" class="btn btn-outline-warning" data-action="raid-pause">
                                <i class="bi bi-pause-fill"></i> Pause
                            </button>
                            <button type="button" class="btn btn-outline-light" data-action="raid-reset">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-danger mt-3 mb-0 d-none" data-raid-expired>
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Raid clock expired — the raider is out. Tell the scoring operator to record it.
                    </div>
                </div>
            </div>
        </div>

        {{-- ------------------------------------------------ half control --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-arrow-left-right me-1"></i>Half</div>
                <div class="card-body">
                    <p class="text-muted small">
                        Switching half refills the game clock, clears the raid and puts both sides
                        back to full strength.
                    </p>
                    <div class="btn-group w-100 mb-3" role="group">
                        <button type="button" class="btn btn-outline-light btn-lg" data-action="half" data-half="1">
                            First half
                        </button>
                        <button type="button" class="btn btn-outline-light btn-lg" data-action="half" data-half="2">
                            Second half
                        </button>
                    </div>
                    <p class="mb-0 small text-muted">
                        When the game clock reaches zero the match moves to half time on its own,
                        and to full time at the end of the second half.
                    </p>
                </div>
            </div>
        </div>

        {{-- ------------------------------------------------ players on court --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-people me-1"></i>Players on the mat</div>
                <div class="card-body">
                    @foreach (['home' => $match->homeTeam, 'away' => $match->awayTeam] as $side => $team)
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold">{{ $team->name }}</span>
                                <span class="fs-4 fw-bold">
                                    <span data-court="{{ $side }}">{{ $match->playersOnCourtFor($team->id) }}</span>
                                    <span class="text-muted fs-6">/ {{ $match->playersPerSide() }}</span>
                                </span>
                            </div>
                            <div class="court-dots mb-2" data-court-dots="{{ $side }}"></div>
                            <div class="btn-group btn-group-sm w-100" role="group"
                                 aria-label="Set players on court for {{ $team->name }}">
                                @for ($count = 0; $count <= $match->playersPerSide(); $count++)
                                    <button type="button" class="btn btn-outline-light"
                                            data-action="set-court" data-team="{{ $team->id }}"
                                            data-side="{{ $side }}" data-count="{{ $count }}">
                                        {{ $count }}
                                    </button>
                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const routes = {
        state: @json(route('matches.state', $match)),
        gameStart: @json(route('timer.game.start', $match)),
        gamePause: @json(route('timer.game.pause', $match)),
        gameReset: @json(route('timer.game.reset', $match)),
        gameAdjust: @json(route('timer.game.adjust', $match)),
        raidStart: @json(route('timer.raid.start', $match)),
        raidPause: @json(route('timer.raid.pause', $match)),
        raidReset: @json(route('timer.raid.reset', $match)),
        half: @json(route('timer.half', $match)),
        court: @json(route('timer.court', $match)),
    };

    const initialState = @json($snapshot);
    const playersPerSide = initialState.match.players_per_side;

    const session = KtmsLive.session({
        stateUrl: routes.state,
        initialState: initialState,
        pollInterval: 3000,
    });

    const el = (selector) => document.querySelector(selector);
    const els = (selector) => Array.from(document.querySelectorAll(selector));

    function renderCourtDots(side, count) {
        const container = el('[data-court-dots="' + side + '"]');

        if (!container) {
            return;
        }

        // Rebuild only when the number of dots would change.
        if (container.children.length !== playersPerSide) {
            container.innerHTML = '';
            for (let i = 0; i < playersPerSide; i++) {
                const dot = document.createElement('span');
                dot.className = 'court-dot';
                container.appendChild(dot);
            }
        }

        Array.from(container.children).forEach(function (dot, index) {
            dot.classList.toggle('filled', index < count);
        });
    }

    session.onState(function (state) {
        ['home', 'away'].forEach(function (side) {
            const team = state.teams[side];
            KtmsLive.setText('[data-score="' + side + '"]', team.score);
            KtmsLive.setText('[data-court="' + side + '"]', team.players_on_court);
            renderCourtDots(side, team.players_on_court);

            // Highlight the button matching the current count.
            els('[data-action="set-court"][data-side="' + side + '"]').forEach(function (button) {
                const active = Number(button.dataset.count) === team.players_on_court;
                button.classList.toggle('btn-warning', active);
                button.classList.toggle('btn-outline-light', !active);
            });
        });

        KtmsLive.setText('[data-half]', state.match.current_half);

        els('[data-action="half"]').forEach(function (button) {
            const active = Number(button.dataset.half) === state.match.current_half;
            button.classList.toggle('btn-warning', active);
            button.classList.toggle('btn-outline-light', !active);
        });

        const status = el('[data-match-status]');
        if (status) {
            status.textContent = state.match.status_label;
            status.className = 'badge bg-' + state.match.status_colour;
        }

        // Clock running indicators
        KtmsLive.setText('[data-game-status]', state.clocks.game.running ? 'Running' : 'Paused');
        KtmsLive.setText('[data-raid-status]', state.clocks.raid.running ? 'Running' : 'Paused');
        el('[data-game-dot]').classList.toggle('paused', !state.clocks.game.running);
        el('[data-raid-dot]').classList.toggle('paused', !state.clocks.raid.running);

        const indicator = el('[data-raid-indicator]');
        indicator.classList.toggle('on', state.match.is_raid_active);
        indicator.classList.toggle('off', !state.match.is_raid_active);
        KtmsLive.setText('[data-raid-indicator-label]', state.match.is_raid_active ? 'raid live' : 'no raid');

        el('[data-raid-expired]').classList.toggle(
            'd-none',
            !(state.clocks.raid.expired && state.match.is_raid_active)
        );
    });

    session.onTick(function (live) {
        KtmsLive.setText('[data-game-clock]', live.gameClock.formatted());
        KtmsLive.setText('[data-raid-clock]', live.raidClock.formatted());

        // Flash the raid clock over the closing seconds.
        const raid = el('[data-raid-clock]');
        const remaining = live.raidClock.value();
        raid.classList.toggle('danger', live.raidClock.running && remaining <= 5);
    });

    function bind(selector, handler) {
        els(selector).forEach(function (button) {
            button.addEventListener('click', function () {
                handler(button);
            });
        });
    }

    bind('[data-action="game-start"]', () => session.act(routes.gameStart, {}));
    bind('[data-action="game-pause"]', () => session.act(routes.gamePause, {}));
    bind('[data-action="game-adjust"]', (b) => session.act(routes.gameAdjust, { seconds: Number(b.dataset.seconds) }));

    bind('[data-action="game-reset"]', function () {
        if (window.confirm('Reset the game clock to a full half?')) {
            session.act(routes.gameReset, {});
        }
    });

    bind('[data-action="raid-start"]', () => session.act(routes.raidStart, {}));
    bind('[data-action="raid-pause"]', () => session.act(routes.raidPause, {}));
    bind('[data-action="raid-reset"]', () => session.act(routes.raidReset, { auto_start: false }));
    bind('[data-action="raid-restart"]', () => session.act(routes.raidReset, { auto_start: true }));

    bind('[data-action="half"]', function (button) {
        const half = Number(button.dataset.half);

        if (half === session.state.match.current_half) {
            session.toast('Already in half ' + half + '.', 'info');

            return;
        }

        if (window.confirm('Switch to half ' + half + '? The game clock refills and both sides return to full strength.')) {
            session.act(routes.half, { half: half });
        }
    });

    bind('[data-action="set-court"]', function (button) {
        session.act(routes.court, {
            team_id: Number(button.dataset.team),
            count: Number(button.dataset.count),
        });
    });

    session.apply(initialState);
    session.start();
})();
</script>
@endpush
