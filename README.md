# KTMS — Kabaddi Tournament Management System

A Laravel application for running kabaddi leagues: tournaments, squads, live match
scoring with two operators working side by side, a broadcast scoreboard, and player
statistics reporting.

## Requirements

- PHP 8.3+
- Composer 2
- MySQL 8 (or MariaDB 10.4+)

No Node build step is needed. Bootstrap 5 and Bootstrap Icons load from a CDN, and
the project's own CSS and JS are plain files under `public/`.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create the database and point `.env` at it:

```sql
CREATE DATABASE ktms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ktms
DB_USERNAME=root
DB_PASSWORD=
```

Then migrate, seed and link storage so team logos are served:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Open http://127.0.0.1:8000.

## What the seed data gives you

One tournament, *National Kabaddi Championship*, with six squads of twelve players
and generated crests, plus five matches:

| Match | Fixture | State |
| ----- | ------- | ----- |
| 1 | Mumbai Maulers vs Delhi Dynamos | completed |
| 2 | Jaipur Jaguars vs Chennai Cheetahs | completed |
| 3 | Bengaluru Blasters vs Kolkata Kings | **live, second half, clock running** |
| 4 | Mumbai Maulers vs Jaipur Jaguars | scheduled |
| 5 | Delhi Dynamos vs Bengaluru Blasters | scheduled (semi-final) |

The finished and in-progress matches are played out through the real scoring
service rather than written straight to the database, so every score, timeline
entry and statistic in the seed data is exactly what the operator consoles would
have produced.

Match 3 is the best starting point — open its three screens side by side.

## The three live screens

Every match has three URLs that share one server-side snapshot:

| Screen | URL | Who uses it |
| ------ | --- | ----------- |
| Scoring console | `/matches/{id}/scoring` | Operator recording raids, tackles and points |
| Timer console | `/matches/{id}/timer` | Operator running the raid clock and game clock |
| Broadcast display | `/matches/{id}/live` | Projector or second screen for the crowd |

They all poll `GET /matches/{id}/state`, and every operator action replies with a
complete fresh snapshot. The server owns both clocks: it stores the seconds
remaining together with the timestamp that value was true, so each browser derives
the same countdown and no screen can drift out of step. Clocks are interpolated
locally between polls so they tick smoothly.

### Scoring operator

- Adjust either team's score by hand (±1, ±2)
- Toggle the raid on and off, which arms the raid clock
- Record a raid: pick the raider, then mark it successful, tackled or empty
  - Successful: touch points, bonus point, which defenders went out, which were beaten
  - Tackled: who made the tackle, the tackle type, whether the raider still took the bonus
- Log a defensive action on its own
- Correct the number of players on the mat, or revive a side to full strength
- Undo the last action
- Move the match between live, half time and full time

### Timer operator

- Game clock: start, pause, reset, and ±10s / ±60s corrections
- Raid clock: new raid, start, pause, reset
- First half / second half toggle
- Players on the mat for each side, with the count set directly

## Kabaddi rules the engine applies

- A successful raid scores one point per defender touched, plus one for the bonus
- Three or more points in a raid is recorded as a **super raid**
- Tackling the raider scores one point, or **two** when the defence is down to three
  or fewer, which is also recorded as a super tackle
- Two consecutive empty raids by a side make the next one a **do-or-die** raid
- Clearing the mat is an **all out**: the opposition takes two points and the cleared
  side returns at full strength
- One point always equals one player off the mat, so the score and the court count
  can never disagree
- When the game clock runs out the match moves to half time, and to full time after
  the second half

## Statistics and reports

Per player, per tournament: matches played, raids attempted, successful, failed and
empty, touch and bonus points, raid points, super raids, do-or-die attempts and
conversions, defences attempted, successful and failed, defence points, super
tackles, and total points. Success rates, points per match and points per raid are
derived from those.

- `/reports` — leaderboard across all tournaments, filterable and sortable
- `/reports/tournaments/{id}` — scoped to one tournament, filterable by team and role
- `/players/{id}` — full report for one player, print friendly
- `/reports/export` — the current report as CSV

Aggregates are recomputed from the underlying raid and defensive-action rows after
every scoring action rather than nudged up and down. A recalculation touches only a
handful of indexed rows, and in exchange an undo or a correction can never leave the
totals drifting. To rebuild them at any time:

```bash
php artisan ktms:rebuild-stats                # every tournament
php artisan ktms:rebuild-stats --tournament=1 # just one
```

## Layout

```
app/
  Console/Commands/RebuildStatisticsCommand.php
  Http/Controllers/    Dashboard, Tournament, Team, Player, Match,
                       Scoring, Timer, Live, Report
  Http/Requests/       Tournament, Team, Player, Match
  Models/              Tournament, Team, Player, GameMatch, Raid,
                       DefensiveAction, PlayerStatistic, MatchEvent
  Services/            MatchService       match lifecycle, lineups, both clocks
                       ScoringService     raids, tackles, all outs, undo
                       StatisticsService  aggregate rebuilds
                       MatchStateService  the snapshot every live screen reads
database/
  migrations/          tournaments, teams, players, matches, match_player,
                       raids, defensive_actions, player_statistics, match_events
  seeders/             KabaddiTournamentSeeder
resources/views/
  layouts/             app (admin), console (operator)
  live/                scoring, timer, display
  tournaments/ teams/ players/ matches/ reports/ components/
public/
  css/ktms.css  js/ktms-live.js
```

The match model is called `GameMatch` because `match` is a reserved keyword in
PHP 8; it maps explicitly onto the `matches` table.

## Notes

- `player_statistics` holds one row per player per tournament, so a player's record
  is scoped to the competition they earned it in.
- `match_events` keeps a timeline of everything that happened, with a snapshot of
  the counters before each action. That snapshot is what undo replays.
- Resetting a match (`/matches/{id}/edit` → Reset match) clears its scoring and
  rebuilds the affected players' statistics.
- The app ships without authentication. Put the operator consoles behind a login or
  network restriction before using it anywhere public, since anyone who can reach
  `/matches/{id}/scoring` can change a live score.
