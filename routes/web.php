<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LiveController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScoringController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TimerController;
use App\Http\Controllers\TournamentController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

/*
|--------------------------------------------------------------------------
| Tournament administration
|--------------------------------------------------------------------------
| Teams, players and matches are created inside their parent but read and
| edited through shallow routes, which keeps the URLs short.
*/

Route::resource('tournaments', TournamentController::class);

Route::controller(TeamController::class)->group(function () {
    Route::get('tournaments/{tournament}/teams/create', 'create')->name('teams.create');
    Route::post('tournaments/{tournament}/teams', 'store')->name('teams.store');
    Route::get('teams/{team}', 'show')->name('teams.show');
    Route::get('teams/{team}/edit', 'edit')->name('teams.edit');
    Route::put('teams/{team}', 'update')->name('teams.update');
    Route::delete('teams/{team}', 'destroy')->name('teams.destroy');
});

Route::controller(PlayerController::class)->group(function () {
    Route::get('teams/{team}/players/create', 'create')->name('players.create');
    Route::post('teams/{team}/players', 'store')->name('players.store');
    Route::get('players/{player}', 'show')->name('players.show');
    Route::get('players/{player}/edit', 'edit')->name('players.edit');
    Route::put('players/{player}', 'update')->name('players.update');
    Route::delete('players/{player}', 'destroy')->name('players.destroy');
});

Route::controller(MatchController::class)->group(function () {
    Route::get('matches', 'index')->name('matches.index');
    Route::get('tournaments/{tournament}/matches/create', 'create')->name('matches.create');
    Route::post('tournaments/{tournament}/matches', 'store')->name('matches.store');
    Route::get('matches/{match}', 'show')->name('matches.show');
    Route::get('matches/{match}/edit', 'edit')->name('matches.edit');
    Route::put('matches/{match}', 'update')->name('matches.update');
    Route::delete('matches/{match}', 'destroy')->name('matches.destroy');
    Route::post('matches/{match}/reset', 'reset')->name('matches.reset');
});

/*
|--------------------------------------------------------------------------
| Live match operation
|--------------------------------------------------------------------------
| Three screens share one match: the scoring operator records what happens,
| the timer operator runs both clocks, and the display shows the scoreboard.
| They all read the same snapshot from `matches.state`.
*/

Route::get('matches/{match}/live', [LiveController::class, 'show'])->name('matches.live');
Route::get('matches/{match}/state', [LiveController::class, 'state'])->name('matches.state');

Route::controller(ScoringController::class)
    ->prefix('matches/{match}/scoring')
    ->name('scoring.')
    ->group(function () {
        Route::get('/', 'console')->name('console');
        Route::post('score', 'adjustScore')->name('score');
        Route::post('raid/toggle', 'toggleRaid')->name('raid.toggle');
        Route::post('raid', 'recordRaid')->name('raid.record');
        Route::post('defense', 'recordDefense')->name('defense.record');
        Route::post('court', 'adjustCourt')->name('court');
        Route::post('revive', 'revive')->name('revive');
        Route::post('undo', 'undo')->name('undo');
        Route::post('status', 'setStatus')->name('status');
    });

Route::controller(TimerController::class)
    ->prefix('matches/{match}/timer')
    ->name('timer.')
    ->group(function () {
        Route::get('/', 'console')->name('console');
        Route::post('game/start', 'startGameClock')->name('game.start');
        Route::post('game/pause', 'pauseGameClock')->name('game.pause');
        Route::post('game/reset', 'resetGameClock')->name('game.reset');
        Route::post('game/adjust', 'adjustGameClock')->name('game.adjust');
        Route::post('raid/start', 'startRaidClock')->name('raid.start');
        Route::post('raid/pause', 'pauseRaidClock')->name('raid.pause');
        Route::post('raid/reset', 'resetRaidClock')->name('raid.reset');
        Route::post('half', 'setHalf')->name('half');
        Route::post('court', 'setCourt')->name('court');
    });

/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
| Built from the stored player_statistics aggregates.
*/

Route::controller(ReportController::class)->name('reports.')->group(function () {
    Route::get('reports', 'index')->name('index');
    Route::get('reports/export', 'export')->name('export');
    Route::get('reports/tournaments/{tournament}', 'tournament')->name('tournament');
});
