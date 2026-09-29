<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }} · Live</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/ktms.css') }}" rel="stylesheet">
</head>
<body class="broadcast">
<div class="container-fluid py-4 px-4 px-xl-5">

    {{-- ------------------------------------------------ header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="broadcast-tag">{{ $match->tournament->name }}</div>
            <div class="broadcast-tag opacity-75">
                @if ($match->round){{ $match->round }} · @endif
                @if ($match->match_number)Match {{ $match->match_number }}@endif
                @if ($match->venue) · {{ $match->venue }}@endif
            </div>
        </div>

        <div class="text-center">
            <span class="badge fs-6 bg-danger px-3 py-2" data-status-badge>
                <span class="live-dot" data-live-dot></span>
                <span data-status-label>{{ $match->status_label }}</span>
            </span>
        </div>

        <div class="d-flex align-items-center gap-3">
            <span class="broadcast-tag">
                <i class="bi bi-circle-fill" data-connection></i> sync
            </span>
            <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleFullscreen()">
                <i class="bi bi-arrows-fullscreen"></i>
            </button>
        </div>
    </div>

    {{-- ------------------------------------------------ scoreboard --}}
    <div class="scoreboard mb-4">
        {{-- Home --}}
        <div class="scoreboard-team home">
            <x-team-logo :team="$match->homeTeam" :size="96" />
            <div class="min-w-0">
                <p class="team-name">{{ $match->homeTeam->name }}</p>
                <div class="team-meta mb-2">{{ $match->homeTeam->city ?? 'Home' }}</div>
                <div class="d-flex align-items-center gap-2">
                    <span class="court-dots" data-court-dots="home"></span>
                    <span class="broadcast-tag">
                        <span data-court="home">{{ $match->home_players_on_court }}</span>
                        on mat
                    </span>
                </div>
                <span class="raiding-flag mt-2 d-none" data-raiding="home">
                    <i class="bi bi-arrow-right-circle-fill"></i> raiding
                </span>
            </div>
            <div class="scoreboard-score ms-auto" data-score="home">{{ $match->home_score }}</div>
        </div>

        {{-- Centre: clocks --}}
        <div class="scoreboard-centre">
            <div class="broadcast-tag mb-1">Half <span data-half>{{ $match->current_half }}</span></div>
            <div class="game-clock mb-1" data-game-clock>
                {{ \App\Models\GameMatch::formatClock($match->effectiveGameClockRemaining()) }}
            </div>
            <div class="broadcast-tag mb-3" data-game-status>
                {{ $match->game_timer_running ? 'running' : 'paused' }}
            </div>

            <div class="raid-clock-ring mx-auto" data-raid-ring style="--pct: 100">
                <span data-raid-clock>{{ $match->effectiveRaidClockRemaining() }}</span>
            </div>
            <div class="broadcast-tag mt-2">raid clock</div>
        </div>

        {{-- Away --}}
        <div class="scoreboard-team away">
            <x-team-logo :team="$match->awayTeam" :size="96" />
            <div class="min-w-0">
                <p class="team-name">{{ $match->awayTeam->name }}</p>
                <div class="team-meta mb-2">{{ $match->awayTeam->city ?? 'Away' }}</div>
                <div class="d-flex align-items-center gap-2 justify-content-end">
                    <span class="broadcast-tag">
                        <span data-court="away">{{ $match->away_players_on_court }}</span>
                        on mat
                    </span>
                    <span class="court-dots away" data-court-dots="away"></span>
                </div>
                <span class="raiding-flag mt-2 d-none" data-raiding="away">
                    <i class="bi bi-arrow-left-circle-fill"></i> raiding
                </span>
            </div>
            <div class="scoreboard-score me-auto" data-score="away">{{ $match->away_score }}</div>
        </div>
    </div>

    {{-- ------------------------------------------------ last raid + feed --}}
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="broadcast-feed p-3 h-100">
                <div class="broadcast-tag mb-2">Last raid</div>
                <div data-last-raid>
                    <p class="text-white-50 mb-0">No raids yet.</p>
                </div>

                <hr class="border-secondary">

                <div class="broadcast-tag mb-2">Match</div>
                <div class="d-flex justify-content-between small text-white-50">
                    <span>Raids recorded</span>
                    <span class="text-white fw-bold" data-raid-count>{{ $match->raid_count }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="broadcast-feed p-3 h-100">
                <div class="broadcast-tag mb-2">Commentary</div>
                <div class="timeline" data-timeline style="max-height: 260px;"></div>
            </div>
        </div>
    </div>

    {{-- Shown when the match finishes --}}
    <div class="text-center mt-4 d-none" data-result-banner>
        <span class="badge bg-success fs-5 px-4 py-3" data-result-text></span>
    </div>
</div>

<script src="{{ asset('js/ktms-live.js') }}"></script>
<script>
function toggleFullscreen() {
    if (document.fullscreenElement) {
        document.exitFullscreen();
    } else {
        document.documentElement.requestFullscreen();
    }
}

(function () {
    'use strict';

    const initialState = @json($snapshot);
    const playersPerSide = initialState.match.players_per_side;

    const session = KtmsLive.session({
        stateUrl: @json(route('matches.state', $match)),
        initialState: initialState,
        // The display is read-only, so poll a little faster for responsiveness.
        pollInterval: 2000,
    });

    const el = (selector) => document.querySelector(selector);

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value == null ? '' : value);

        return div.innerHTML;
    }

    function renderCourtDots(side, count) {
        const container = el('[data-court-dots="' + side + '"]');

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

    function renderLastRaid(state) {
        const container = el('[data-last-raid]');
        const raid = state.last_raid;

        if (!raid) {
            container.innerHTML = '<p class="text-white-50 mb-0">No raids yet.</p>';

            return;
        }

        const tags = [];
        if (raid.is_super_raid) {
            tags.push('<span class="badge bg-warning text-dark">Super raid</span>');
        }
        if (raid.is_do_or_die) {
            tags.push('<span class="badge bg-danger">Do-or-die</span>');
        }

        const points = raid.raid_points > 0
            ? '+' + raid.raid_points + ' raid'
            : (raid.defending_points > 0 ? '+' + raid.defending_points + ' defence' : 'no points');

        container.innerHTML =
            '<div class="d-flex justify-content-between align-items-start gap-2">'
            + '<div><div class="fs-5 fw-bold">' + escapeHtml(raid.raider || 'Unknown') + '</div>'
            + '<div class="text-white-50 small">Raid ' + raid.raid_number + ' · '
            + escapeHtml(raid.result_label) + '</div></div>'
            + '<span class="badge bg-' + escapeHtml(raid.result_colour) + ' fs-6">' + points + '</span>'
            + '</div>'
            + (tags.length ? '<div class="mt-2 d-flex gap-1">' + tags.join('') + '</div>' : '');
    }

    function renderTimeline(state) {
        const container = el('[data-timeline]');
        container.innerHTML = '';

        if (!state.timeline.length) {
            container.innerHTML = '<p class="text-white-50 small mb-0">Waiting for the first raid.</p>';

            return;
        }

        state.timeline.forEach(function (event) {
            const row = document.createElement('div');
            row.className = 'timeline-item';
            row.innerHTML = '<span class="timeline-clock">' + escapeHtml(event.clock) + '</span>'
                + '<span><i class="bi ' + escapeHtml(event.icon) + ' me-1"></i>'
                + escapeHtml(event.description) + '</span>';
            container.appendChild(row);
        });
    }

    session.onState(function (state) {
        ['home', 'away'].forEach(function (side) {
            const team = state.teams[side];
            KtmsLive.setText('[data-score="' + side + '"]', team.score);
            KtmsLive.setText('[data-court="' + side + '"]', team.players_on_court);
            renderCourtDots(side, team.players_on_court);

            el('[data-raiding="' + side + '"]').classList.toggle(
                'd-none',
                !(team.is_raiding && state.match.is_raid_active)
            );
        });

        KtmsLive.setText('[data-half]', state.match.current_half);
        KtmsLive.setText('[data-raid-count]', state.match.raid_count);
        KtmsLive.setText('[data-game-status]', state.clocks.game.running ? 'running' : 'paused');
        KtmsLive.setText('[data-status-label]', state.match.status_label);

        const badge = el('[data-status-badge]');
        badge.className = 'badge fs-6 px-3 py-2 bg-' + state.match.status_colour;
        el('[data-live-dot]').classList.toggle('d-none', state.match.status !== 'live');

        renderLastRaid(state);
        renderTimeline(state);

        // Final score banner
        const banner = el('[data-result-banner]');
        if (state.match.status === 'completed') {
            const home = state.teams.home;
            const away = state.teams.away;
            let text;

            if (state.match.winner_team_id === home.id) {
                text = home.name + ' win ' + home.score + '–' + away.score;
            } else if (state.match.winner_team_id === away.id) {
                text = away.name + ' win ' + away.score + '–' + home.score;
            } else {
                text = 'Match tied ' + home.score + '–' + away.score;
            }

            el('[data-result-text]').textContent = 'Full time · ' + text;
            banner.classList.remove('d-none');
        } else {
            banner.classList.add('d-none');
        }
    });

    session.onTick(function (live) {
        KtmsLive.setText('[data-game-clock]', live.gameClock.formatted());

        // The raid clock shows whole seconds inside a depleting ring.
        const remaining = live.raidClock.value();
        KtmsLive.setText('[data-raid-clock]', Math.ceil(remaining));

        const ring = el('[data-raid-ring]');
        ring.style.setProperty('--pct', (live.raidClock.fraction() * 100).toFixed(1));
        ring.classList.toggle('danger', live.raidClock.running && remaining <= 5);
    });

    session.apply(initialState);
    session.start();
})();
</script>
</body>
</html>
