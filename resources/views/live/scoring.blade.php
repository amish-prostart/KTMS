@extends('layouts.console')

@section('title', 'Scoring console')
@section('console-name', 'Scoring operator')

@section('console-switch')
    <a href="{{ route('timer.console', $match) }}" class="btn btn-sm btn-outline-info">
        <i class="bi bi-stopwatch me-1"></i><span class="d-none d-md-inline">Timer</span>
    </a>
@endsection

@section('content')
    {{-- ---------------------------------------------- scoreboard + score pads --}}
    <div class="row g-3 mb-3 console-actions">
        @foreach (['home' => $match->homeTeam, 'away' => $match->awayTeam] as $side => $team)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <x-team-logo :team="$team" :size="42" />
                                <div>
                                    <div class="fw-bold">{{ $team->name }}</div>
                                    <div class="small text-muted text-uppercase">{{ ucfirst($side) }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="console-score" data-score="{{ $side }}">
                                    {{ $match->scoreFor($team->id) }}
                                </div>
                                <span class="raid-indicator off" data-raiding="{{ $side }}" hidden>
                                    <i class="bi bi-arrow-right-circle"></i> raiding
                                </span>
                            </div>
                        </div>

                        <div class="score-pad mb-3">
                            <button type="button" class="btn btn-success btn-score"
                                    data-action="score" data-team="{{ $team->id }}" data-delta="1">
                                +1
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-score"
                                    data-action="score" data-team="{{ $team->id }}" data-delta="-1">
                                −1
                            </button>
                            <button type="button" class="btn btn-outline-success btn-score"
                                    data-action="score" data-team="{{ $team->id }}" data-delta="2">
                                +2
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-score"
                                    data-action="score" data-team="{{ $team->id }}" data-delta="-2">
                                −2
                            </button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-muted text-uppercase">On the mat</div>
                                <div class="fs-4 fw-bold">
                                    <span data-court="{{ $side }}">{{ $match->playersOnCourtFor($team->id) }}</span>
                                    <span class="text-muted fs-6">/ {{ $match->playersPerSide() }}</span>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-light"
                                        data-action="court" data-team="{{ $team->id }}" data-delta="-1"
                                        title="One player out">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-light"
                                        data-action="court" data-team="{{ $team->id }}" data-delta="1"
                                        title="One player revived">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning"
                                        data-action="revive" data-team="{{ $team->id }}"
                                        title="Back to full strength">
                                    <i class="bi bi-arrow-clockwise"></i> All
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        {{-- ---------------------------------------------- raid recording --}}
        <div class="col-xl-8">
            <div class="card mb-3 console-actions">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span><i class="bi bi-person-arms-up me-1"></i>Raid in progress</span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Raid clock</span>
                        <span class="fw-bold fs-5 clock-face" data-raid-clock>--:--</span>
                        <button type="button" class="btn btn-sm btn-warning" data-action="raid-toggle">
                            <span data-raid-toggle-label>Start raid</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger py-2 d-none" data-do-or-die>
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Do-or-die raid.</strong> This side must score or the raider is out.
                    </div>

                    {{-- Which side is raiding --}}
                    <div class="mb-3">
                        <label class="form-label small text-uppercase text-muted">Raiding side</label>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-light" data-raid-side="home"
                                    data-team-id="{{ $match->home_team_id }}">
                                {{ $match->homeTeam->short_name ?: $match->homeTeam->name }}
                            </button>
                            <button type="button" class="btn btn-outline-light" data-raid-side="away"
                                    data-team-id="{{ $match->away_team_id }}">
                                {{ $match->awayTeam->short_name ?: $match->awayTeam->name }}
                            </button>
                        </div>
                    </div>

                    {{-- Raider --}}
                    <div class="mb-3">
                        <label class="form-label small text-uppercase text-muted">
                            Raider <span class="text-danger">*</span>
                        </label>
                        <div class="player-pick" data-pick="raider"></div>
                    </div>

                    {{-- Outcome --}}
                    <div class="mb-3">
                        <label class="form-label small text-uppercase text-muted">Outcome</label>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-success" data-result="successful">
                                <i class="bi bi-check-lg"></i> Successful
                            </button>
                            <button type="button" class="btn btn-outline-danger" data-result="unsuccessful">
                                <i class="bi bi-x-lg"></i> Tackled
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-result="empty">
                                <i class="bi bi-dash"></i> Empty
                            </button>
                        </div>
                    </div>

                    {{-- Successful raid detail --}}
                    <div data-when-result="successful">
                        <div class="row g-3">
                            <div class="col-sm-5">
                                <label for="touchPoints" class="form-label small text-uppercase text-muted">
                                    Touch points
                                </label>
                                <input type="number" class="form-control" id="touchPoints" min="0" max="7" value="1"
                                       data-field="touch_points">
                                <div class="form-text">One point per defender sent out.</div>
                            </div>
                            <div class="col-sm-7 d-flex align-items-center">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="isBonus"
                                           data-field="is_bonus">
                                    <label class="form-check-label" for="isBonus">
                                        Bonus point taken
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label small text-uppercase text-muted">
                                Defenders sent out <span class="text-muted">(optional)</span>
                            </label>
                            <div class="player-pick" data-pick="defender_out"></div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label small text-uppercase text-muted">
                                Defenders beaten <span class="text-muted">(logged as a failed defence)</span>
                            </label>
                            <div class="player-pick" data-pick="beaten"></div>
                        </div>
                    </div>

                    {{-- Tackled detail --}}
                    <div data-when-result="unsuccessful">
                        <label class="form-label small text-uppercase text-muted">
                            Who made the tackle? <span class="text-muted">(points are shared across a chain)</span>
                        </label>
                        <div class="player-pick" data-pick="tackler"></div>

                        <div class="row g-3 mt-2">
                            <div class="col-sm-6">
                                <label for="tackleType" class="form-label small text-uppercase text-muted">Tackle type</label>
                                <select class="form-select" id="tackleType" data-field="action_type">
                                    @foreach (\App\Models\DefensiveAction::ACTION_TYPES as $value => $label)
                                        @if ($value !== 'super_tackle')
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    Upgraded to a super tackle automatically when the defence is down to
                                    {{ \App\Services\ScoringService::SUPER_TACKLE_THRESHOLD }} or fewer.
                                </div>
                            </div>
                            <div class="col-sm-6 d-flex align-items-center">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="isBonusTackled"
                                           data-field="is_bonus_tackled">
                                    <label class="form-check-label" for="isBonusTackled">
                                        Raider still took the bonus
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div data-when-result="empty">
                        <p class="text-muted mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Nobody scores. Two empty raids in a row make the next one do-or-die.
                        </p>
                    </div>

                    <hr>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary btn-lg flex-grow-1" data-action="record-raid">
                            <i class="bi bi-save me-1"></i>Record raid
                        </button>
                        <button type="button" class="btn btn-outline-light btn-lg" data-action="clear-raid">
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            {{-- ---------------------------------------------- standalone defence --}}
            <div class="card console-actions">
                <div class="card-header"><i class="bi bi-shield-check me-1"></i>Log a defensive action on its own</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="defenderSelect" class="form-label small text-uppercase text-muted">Defender</label>
                            <select class="form-select" id="defenderSelect" data-field="defender_id"></select>
                        </div>
                        <div class="col-md-4">
                            <label for="defenceType" class="form-label small text-uppercase text-muted">Action</label>
                            <select class="form-select" id="defenceType" data-field="defence_action_type">
                                @foreach (\App\Models\DefensiveAction::ACTION_TYPES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="defenceSuccess"
                                       data-field="defence_successful" checked>
                                <label class="form-check-label" for="defenceSuccess">Stopped the raider</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-outline-warning" data-action="record-defence">
                                <i class="bi bi-plus-lg me-1"></i>Record defensive action
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ---------------------------------------------- timeline + match control --}}
        <div class="col-xl-4">
            <div class="card mb-3 console-actions">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Match control</span>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-light" data-action="undo">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Undo last action
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-success"
                                    data-action="status" data-status="live">Live</button>
                            <button type="button" class="btn btn-sm btn-outline-warning"
                                    data-action="status" data-status="half_time">Half time</button>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-action="status" data-status="completed">Full time</button>
                        </div>
                    </div>
                    <hr>
                    <dl class="row mb-0 small">
                        <dt class="col-7 fw-normal text-muted">Game clock</dt>
                        <dd class="col-5 text-end fw-bold clock-face" data-game-clock>--:--</dd>
                        <dt class="col-7 fw-normal text-muted">Half</dt>
                        <dd class="col-5 text-end" data-half>{{ $match->current_half }}</dd>
                        <dt class="col-7 fw-normal text-muted">Raids recorded</dt>
                        <dd class="col-5 text-end" data-raid-count>{{ $match->raid_count }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Timeline</div>
                <div class="card-body">
                    <div class="timeline" data-timeline></div>
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
        score: @json(route('scoring.score', $match)),
        raidToggle: @json(route('scoring.raid.toggle', $match)),
        raid: @json(route('scoring.raid.record', $match)),
        defence: @json(route('scoring.defense.record', $match)),
        court: @json(route('scoring.court', $match)),
        revive: @json(route('scoring.revive', $match)),
        undo: @json(route('scoring.undo', $match)),
        status: @json(route('scoring.status', $match)),
    };

    const initialState = @json($snapshot);

    const session = KtmsLive.session({
        stateUrl: routes.state,
        initialState: initialState,
        pollInterval: 3000,
    });

    // Operator's in-progress selections. Deliberately kept outside the render
    // pass so that a poll landing mid-entry never clears the form.
    const form = {
        raidingTeamId: initialState.match.raiding_team_id || initialState.teams.home.id,
        raiderId: null,
        result: 'successful',
        defenderOut: new Set(),
        tacklers: new Set(),
        beaten: new Set(),
    };

    let lineupSignature = '';

    const el = (selector) => document.querySelector(selector);
    const els = (selector) => Array.from(document.querySelectorAll(selector));

    function sideOf(teamId) {
        return session.state.teams.home.id === teamId ? 'home' : 'away';
    }

    function teamBySide(side) {
        return session.state.teams[side];
    }

    function raidingTeam() {
        return teamBySide(sideOf(form.raidingTeamId));
    }

    function defendingTeam() {
        return sideOf(form.raidingTeamId) === 'home'
            ? session.state.teams.away
            : session.state.teams.home;
    }

    /* ------------------------------------------------ rendering */

    function renderScoreboard(state) {
        ['home', 'away'].forEach(function (side) {
            const team = state.teams[side];
            KtmsLive.setText('[data-score="' + side + '"]', team.score);
            KtmsLive.setText('[data-court="' + side + '"]', team.players_on_court);

            const flag = el('[data-raiding="' + side + '"]');
            if (flag) {
                flag.hidden = !team.is_raiding;
                flag.classList.toggle('on', Boolean(team.is_raiding));
                flag.classList.toggle('off', !team.is_raiding);
            }
        });

        KtmsLive.setText('[data-half]', state.match.current_half);
        KtmsLive.setText('[data-raid-count]', state.match.raid_count);

        const status = el('[data-match-status]');
        if (status) {
            status.textContent = state.match.status_label;
            status.className = 'badge bg-' + state.match.status_colour;
        }

        const toggleLabel = el('[data-raid-toggle-label]');
        if (toggleLabel) {
            toggleLabel.textContent = state.match.is_raid_active ? 'Stop raid' : 'Start raid';
        }

        const doOrDie = el('[data-do-or-die]');
        if (doOrDie) {
            doOrDie.classList.toggle('d-none', !state.next_raid_is_do_or_die);
        }
    }

    function renderSideButtons() {
        els('[data-raid-side]').forEach(function (button) {
            const active = Number(button.dataset.teamId) === form.raidingTeamId;
            button.classList.toggle('btn-warning', active);
            button.classList.toggle('btn-outline-light', !active);
        });
    }

    function renderResultButtons() {
        els('[data-result]').forEach(function (button) {
            const active = button.dataset.result === form.result;
            const tone = button.dataset.result === 'successful'
                ? 'success'
                : (button.dataset.result === 'unsuccessful' ? 'danger' : 'secondary');

            button.classList.toggle('btn-' + tone, active);
            button.classList.toggle('btn-outline-' + tone, !active);
        });

        els('[data-when-result]').forEach(function (block) {
            block.hidden = block.dataset.whenResult !== form.result;
        });
    }

    /**
     * Build a grid of player buttons. `mode` is 'single' or 'multi'.
     */
    function renderPicker(container, players, mode, selection, onChange) {
        container.innerHTML = '';

        if (!players.length) {
            const empty = document.createElement('p');
            empty.className = 'text-muted small mb-0';
            empty.textContent = 'Nobody available.';
            container.appendChild(empty);

            return;
        }

        players.forEach(function (player) {
            const button = document.createElement('button');
            button.type = 'button';
            const selected = mode === 'single'
                ? selection.value === player.id
                : selection.has(player.id);

            button.className = 'btn ' + (selected ? 'btn-warning' : 'btn-outline-light')
                + (player.is_on_court ? '' : ' is-out');
            button.innerHTML = '<strong>#' + player.jersey_number + '</strong> '
                + escapeHtml(player.name)
                + '<small>' + escapeHtml(player.role_label)
                + (player.is_on_court ? '' : ' · out') + '</small>';

            button.addEventListener('click', function () {
                onChange(player.id);
            });

            container.appendChild(button);
        });
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value == null ? '' : value);

        return div.innerHTML;
    }

    function onCourt(team) {
        return (team.lineup || []).filter((player) => player.is_on_court);
    }

    function renderPickers() {
        const attackers = onCourt(raidingTeam());
        const defenders = onCourt(defendingTeam());

        // Drop any selection that is no longer on the mat.
        if (form.raiderId && !attackers.some((p) => p.id === form.raiderId)) {
            form.raiderId = null;
        }
        [form.defenderOut, form.tacklers, form.beaten].forEach(function (set) {
            Array.from(set).forEach(function (id) {
                if (!defenders.some((p) => p.id === id)) {
                    set.delete(id);
                }
            });
        });

        renderPicker(el('[data-pick="raider"]'), attackers, 'single', { value: form.raiderId }, function (id) {
            form.raiderId = form.raiderId === id ? null : id;
            renderPickers();
        });

        renderPicker(el('[data-pick="defender_out"]'), defenders, 'multi', form.defenderOut, function (id) {
            toggleSet(form.defenderOut, id);
            // Keep touch points in step with the number of defenders named.
            if (form.defenderOut.size > 0) {
                el('[data-field="touch_points"]').value = form.defenderOut.size;
            }
            renderPickers();
        });

        renderPicker(el('[data-pick="beaten"]'), defenders, 'multi', form.beaten, function (id) {
            toggleSet(form.beaten, id);
            renderPickers();
        });

        renderPicker(el('[data-pick="tackler"]'), defenders, 'multi', form.tacklers, function (id) {
            toggleSet(form.tacklers, id);
            renderPickers();
        });
    }

    function toggleSet(set, id) {
        if (set.has(id)) {
            set.delete(id);
        } else {
            set.add(id);
        }
    }

    function renderDefenderSelect(state) {
        const select = el('[data-field="defender_id"]');
        const previous = select.value;
        select.innerHTML = '';

        ['home', 'away'].forEach(function (side) {
            const team = state.teams[side];
            const group = document.createElement('optgroup');
            group.label = team.name;

            (team.lineup || []).forEach(function (player) {
                const option = document.createElement('option');
                option.value = player.id;
                option.textContent = '#' + player.jersey_number + ' ' + player.name
                    + (player.is_on_court ? '' : ' (out)');
                group.appendChild(option);
            });

            select.appendChild(group);
        });

        if (previous) {
            select.value = previous;
        }
    }

    function renderTimeline(state) {
        const container = el('[data-timeline]');
        container.innerHTML = '';

        if (!state.timeline.length) {
            container.innerHTML = '<p class="text-muted small mb-0">Nothing recorded yet.</p>';

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

    /**
     * Player grids are expensive to rebuild and rebuilding them loses focus, so
     * only do it when the squads or court occupancy actually changed.
     */
    function lineupFingerprint(state) {
        return ['home', 'away'].map(function (side) {
            return (state.teams[side].lineup || [])
                .map((p) => p.id + ':' + (p.is_on_court ? 1 : 0))
                .join(',');
        }).join('|') + '#' + form.raidingTeamId;
    }

    session.onState(function (state) {
        renderScoreboard(state);
        renderTimeline(state);

        const fingerprint = lineupFingerprint(state);
        if (fingerprint !== lineupSignature) {
            lineupSignature = fingerprint;
            renderPickers();
            renderDefenderSelect(state);
        }
    });

    // Smooth clocks between polls.
    session.onTick(function (live) {
        KtmsLive.setText('[data-game-clock]', live.gameClock.formatted());
        KtmsLive.setText('[data-raid-clock]', live.raidClock.formatted());

        const raidClock = el('[data-raid-clock]');
        if (raidClock) {
            raidClock.classList.toggle('text-danger', live.raidClock.value() <= 5);
        }
    });

    /* ------------------------------------------------ interactions */

    els('[data-raid-side]').forEach(function (button) {
        button.addEventListener('click', function () {
            form.raidingTeamId = Number(button.dataset.teamId);
            form.raiderId = null;
            form.defenderOut.clear();
            form.tacklers.clear();
            form.beaten.clear();
            renderSideButtons();
            lineupSignature = lineupFingerprint(session.state);
            renderPickers();
        });
    });

    els('[data-result]').forEach(function (button) {
        button.addEventListener('click', function () {
            form.result = button.dataset.result;
            renderResultButtons();
        });
    });

    els('[data-action="score"]').forEach(function (button) {
        button.addEventListener('click', function () {
            session.act(routes.score, {
                team_id: Number(button.dataset.team),
                delta: Number(button.dataset.delta),
            });
        });
    });

    els('[data-action="court"]').forEach(function (button) {
        button.addEventListener('click', function () {
            session.act(routes.court, {
                team_id: Number(button.dataset.team),
                delta: Number(button.dataset.delta),
            });
        });
    });

    els('[data-action="revive"]').forEach(function (button) {
        button.addEventListener('click', function () {
            session.act(routes.revive, { team_id: Number(button.dataset.team) });
        });
    });

    el('[data-action="raid-toggle"]').addEventListener('click', function () {
        session.act(routes.raidToggle, {
            active: !session.state.match.is_raid_active,
            raiding_team_id: form.raidingTeamId,
        });
    });

    el('[data-action="undo"]').addEventListener('click', function () {
        if (window.confirm('Undo the last scoring action?')) {
            session.act(routes.undo, {});
        }
    });

    els('[data-action="status"]').forEach(function (button) {
        button.addEventListener('click', function () {
            const status = button.dataset.status;

            if (status === 'completed' && !window.confirm('End the match and record the result?')) {
                return;
            }

            session.act(routes.status, { status: status });
        });
    });

    el('[data-action="clear-raid"]').addEventListener('click', resetRaidForm);

    function resetRaidForm() {
        form.raiderId = null;
        form.result = 'successful';
        form.defenderOut.clear();
        form.tacklers.clear();
        form.beaten.clear();
        el('[data-field="touch_points"]').value = 1;
        el('[data-field="is_bonus"]').checked = false;
        el('[data-field="is_bonus_tackled"]').checked = false;
        renderResultButtons();
        renderPickers();
    }

    el('[data-action="record-raid"]').addEventListener('click', function () {
        if (!form.raiderId) {
            session.toast('Pick the raider first.', 'danger');

            return;
        }

        const payload = {
            raider_id: form.raiderId,
            result: form.result,
            notes: null,
        };

        if (form.result === 'successful') {
            payload.touch_points = Number(el('[data-field="touch_points"]').value) || 0;
            payload.is_bonus = el('[data-field="is_bonus"]').checked;
            payload.defender_out_ids = Array.from(form.defenderOut);
            payload.beaten_defender_ids = Array.from(form.beaten);
        } else if (form.result === 'unsuccessful') {
            payload.tackler_ids = Array.from(form.tacklers);
            payload.action_type = el('[data-field="action_type"]').value;
            payload.is_bonus = el('[data-field="is_bonus_tackled"]').checked;
        }

        session.act(routes.raid, payload).then(function (state) {
            if (!state) {
                return;
            }

            // The raid passes to the other side, so follow it.
            form.raidingTeamId = state.match.raiding_team_id || form.raidingTeamId;
            renderSideButtons();
            resetRaidForm();
            lineupSignature = '';
            session.apply(state);
        });
    });

    el('[data-action="record-defence"]').addEventListener('click', function () {
        const defenderId = Number(el('[data-field="defender_id"]').value);

        if (!defenderId) {
            session.toast('Pick a defender.', 'danger');

            return;
        }

        session.act(routes.defence, {
            defender_id: defenderId,
            action_type: el('[data-field="defence_action_type"]').value,
            is_successful: el('[data-field="defence_successful"]').checked,
        });
    });

    /* ------------------------------------------------ boot */

    renderSideButtons();
    renderResultButtons();
    session.apply(initialState);
    session.start();
})();
</script>
@endpush
