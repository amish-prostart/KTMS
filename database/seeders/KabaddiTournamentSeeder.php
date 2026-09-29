<?php

namespace Database\Seeders;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Raid;
use App\Models\Team;
use App\Models\Tournament;
use App\Services\MatchService;
use App\Services\ScoringService;
use App\Services\StatisticsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Builds a ready-to-explore tournament: six squads, full player lists, two
 * finished matches, one match live in the second half and two fixtures still to
 * come.
 *
 * Matches are played out through the real ScoringService rather than by writing
 * rows directly, so every score, timeline entry and statistic in the seeded data
 * is exactly what the live consoles would have produced.
 */
class KabaddiTournamentSeeder extends Seeder
{
    public function __construct(
        protected MatchService $matches,
        protected ScoringService $scoring,
        protected StatisticsService $statistics,
    ) {}

    /**
     * @var array<int, array{name: string, short: string, city: string, coach: string, primary: string, secondary: string}>
     */
    protected array $teamBlueprint = [
        ['name' => 'Mumbai Maulers', 'short' => 'MUM', 'city' => 'Mumbai', 'coach' => 'Ashok Shinde', 'primary' => '#1b4dff', 'secondary' => '#ffd400'],
        ['name' => 'Delhi Dynamos', 'short' => 'DEL', 'city' => 'New Delhi', 'coach' => 'Ramesh Gulia', 'primary' => '#d62828', 'secondary' => '#ffffff'],
        ['name' => 'Jaipur Jaguars', 'short' => 'JAI', 'city' => 'Jaipur', 'coach' => 'Mahipal Singh', 'primary' => '#ff6b1a', 'secondary' => '#1d2439'],
        ['name' => 'Chennai Cheetahs', 'short' => 'CHE', 'city' => 'Chennai', 'coach' => 'Karthik Raja', 'primary' => '#00897b', 'secondary' => '#ffe082'],
        ['name' => 'Bengaluru Blasters', 'short' => 'BLR', 'city' => 'Bengaluru', 'coach' => 'Suresh Kumar', 'primary' => '#6a1b9a', 'secondary' => '#f3e5f5'],
        ['name' => 'Kolkata Kings', 'short' => 'KOL', 'city' => 'Kolkata', 'coach' => 'Bikash Ghosh', 'primary' => '#00695c', 'secondary' => '#b2dfdb'],
    ];

    /**
     * Twelve names per squad, in the order they are registered.
     *
     * @var array<int, array<int, string>>
     */
    protected array $squadNames = [
        ['Rohit Pawar', 'Sandeep More', 'Akash Jadhav', 'Nitin Kamble', 'Yash Bhosale', 'Vikram Salunkhe', 'Omkar Patil', 'Tushar Shelke', 'Pravin Gaikwad', 'Ganesh Mane', 'Sagar Bhoir', 'Kunal Wagh'],
        ['Deepak Hooda', 'Manjeet Dahiya', 'Vikas Kandola', 'Naveen Sehrawat', 'Ravinder Pahal', 'Joginder Narwal', 'Sunil Kumar', 'Anil Chaudhary', 'Rahul Sangwan', 'Meetu Mehra', 'Ashu Malik', 'Vijay Rathi'],
        ['Arjun Deshwal', 'Rahul Chaudhari', 'Sahil Gulia', 'Nitin Rawal', 'Sunil Siddhgavali', 'Ankush Rathee', 'Lucky Sharma', 'Deepak Sankar', 'Bhavani Rajput', 'Amit Nirwal', 'Sourav Gulia', 'Reza Mirbagheri'],
        ['Ajith Kumar', 'Surjeet Singh', 'Mohit Chhillar', 'Selvamani Karuppaiah', 'Prapanjan Vellaisamy', 'Manikandan Raj', 'Jeeva Kumar', 'Dinesh Naik', 'Harish Naik', 'Sathish Kannan', 'Vinoth Kumar', 'Bala Murugan'],
        ['Pawan Sehrawat', 'Bharat Hooda', 'Aman Antil', 'Saurabh Nandal', 'Mahender Singh', 'Amit Sheoran', 'Rohit Kumar', 'Chandran Ranjit', 'Aashish Sangwan', 'Neeraj Narwal', 'Parteek Dahiya', 'Monu Goyat'],
        ['Maninder Singh', 'Shrikant Jadhav', 'Nabibakhsh Mohammad', 'Girish Ernak', 'Jaideep Dahiya', 'Rinku Narwal', 'Sukesh Hegde', 'Ran Singh', 'Abhishek Singh', 'Guman Singh', 'Vishal Bhardwaj', 'Aslam Inamdar'],
    ];

    public function run(): void
    {
        // Deterministic seed data makes screenshots and demos repeatable.
        mt_srand(20260928);

        $tournament = Tournament::create([
            'name' => 'National Kabaddi Championship',
            'season' => '2026',
            'description' => 'Six-team round robin played over two weeks, with 20 minute halves and a 30 second raid clock.',
            'venue' => 'Balewadi Sports Complex',
            'city' => 'Pune',
            'start_date' => now()->subDays(6)->toDateString(),
            'end_date' => now()->addDays(8)->toDateString(),
            'status' => Tournament::STATUS_ONGOING,
            'half_duration_seconds' => 1200,
            'raid_duration_seconds' => 30,
            'players_per_side' => 7,
        ]);

        $teams = $this->createTeams($tournament);

        [$mumbai, $delhi, $jaipur, $chennai, $bengaluru, $kolkata] = $teams->values()->all();

        // --- Two finished matches -------------------------------------
        $completedOne = $this->buildMatch($tournament, $mumbai, $delhi, 1, 'League', now()->subDays(5)->setTime(19, 0));
        $this->playMatch($completedOne, 38);
        $this->matches->complete($completedOne->refresh());

        $completedTwo = $this->buildMatch($tournament, $jaipur, $chennai, 2, 'League', now()->subDays(3)->setTime(20, 0));
        $this->playMatch($completedTwo, 34);
        $this->matches->complete($completedTwo->refresh());

        // --- One match live right now ---------------------------------
        $live = $this->buildMatch($tournament, $bengaluru, $kolkata, 3, 'League', now()->subMinutes(35));
        $this->matches->start($live);
        $this->playMatch($live, 16);
        $this->putIntoSecondHalf($live);

        // --- Fixtures still to come -----------------------------------
        $this->buildMatch($tournament, $mumbai, $jaipur, 4, 'League', now()->addDays(1)->setTime(19, 30));
        $this->buildMatch($tournament, $delhi, $bengaluru, 5, 'Semi-final', now()->addDays(3)->setTime(20, 0));

        // Rebuild every aggregate so the reports open with complete numbers.
        $this->statistics->recalculateTournament($tournament);

        $this->command?->info('Seeded "'.$tournament->name.'" with '.$teams->count().' teams and 5 matches.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, Team>
     */
    protected function createTeams(Tournament $tournament)
    {
        return collect($this->teamBlueprint)->map(function (array $blueprint, int $index) use ($tournament) {
            $team = $tournament->teams()->create([
                'name' => $blueprint['name'],
                'short_name' => $blueprint['short'],
                'city' => $blueprint['city'],
                'coach_name' => $blueprint['coach'],
                'primary_color' => $blueprint['primary'],
                'secondary_color' => $blueprint['secondary'],
                'logo_path' => $this->writeLogo($blueprint),
            ]);

            $this->createSquad($team, $this->squadNames[$index]);

            return $team;
        });
    }

    /**
     * Write a simple crest to the public disk so every team ships with a real logo file.
     *
     * @param  array{name: string, short: string, primary: string, secondary: string}  $blueprint
     */
    protected function writeLogo(array $blueprint): string
    {
        $path = 'team-logos/'.str($blueprint['short'])->lower().'.svg';

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="120" height="120" role="img" aria-label="{$blueprint['name']} crest">
            <defs>
                <linearGradient id="g" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="{$blueprint['primary']}"/>
                    <stop offset="100%" stop-color="{$blueprint['secondary']}"/>
                </linearGradient>
            </defs>
            <path d="M60 4 108 20v44c0 28-20 44-48 52C32 108 12 92 12 64V20z" fill="url(#g)" stroke="{$blueprint['primary']}" stroke-width="4"/>
            <circle cx="60" cy="52" r="26" fill="#ffffff" opacity="0.92"/>
            <text x="60" y="61" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="20" font-weight="700" fill="{$blueprint['primary']}">{$blueprint['short']}</text>
        </svg>
        SVG;

        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    /**
     * @param  array<int, string>  $names
     */
    protected function createSquad(Team $team, array $names): void
    {
        // A believable spread: three raiders, four defenders, the rest all-rounders.
        $roles = [
            Player::ROLE_RAIDER, Player::ROLE_RAIDER, Player::ROLE_RAIDER,
            Player::ROLE_DEFENDER, Player::ROLE_DEFENDER, Player::ROLE_DEFENDER, Player::ROLE_DEFENDER,
            Player::ROLE_ALL_ROUNDER, Player::ROLE_ALL_ROUNDER,
            Player::ROLE_RAIDER, Player::ROLE_DEFENDER, Player::ROLE_ALL_ROUNDER,
        ];

        $positions = array_keys(Player::POSITIONS);

        foreach ($names as $index => $name) {
            $team->players()->create([
                'name' => $name,
                'jersey_number' => $index + 1,
                'role' => $roles[$index] ?? Player::ROLE_ALL_ROUNDER,
                'position' => $positions[$index % count($positions)],
                'date_of_birth' => now()->subYears(mt_rand(20, 32))->subDays(mt_rand(0, 364))->toDateString(),
                'height_cm' => mt_rand(165, 190),
                'weight_kg' => mt_rand(68, 88),
                'nationality' => 'India',
                'is_captain' => $index === 0,
                'is_active' => true,
            ]);
        }
    }

    protected function buildMatch(
        Tournament $tournament,
        Team $home,
        Team $away,
        int $number,
        string $round,
        \DateTimeInterface $scheduledAt,
    ): GameMatch {
        return $this->matches->createForTournament($tournament, [
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'match_number' => $number,
            'round' => $round,
            'scheduled_at' => $scheduledAt,
            'half_duration_seconds' => $tournament->half_duration_seconds,
            'raid_duration_seconds' => $tournament->raid_duration_seconds,
            'players_per_side' => $tournament->players_per_side,
        ]);
    }

    /**
     * Play out a believable sequence of raids through the scoring engine.
     */
    protected function playMatch(GameMatch $match, int $raidCount): void
    {
        $match->refresh();

        if ($match->status === GameMatch::STATUS_SCHEDULED) {
            $match->forceFill(['status' => GameMatch::STATUS_LIVE, 'started_at' => now()])->save();
        }

        for ($i = 0; $i < $raidCount; $i++) {
            $match->refresh();

            // Halfway through, switch ends as the teams would at the interval.
            if ($i === intdiv($raidCount, 2)) {
                $this->matches->setHalf($match, 2);
                $match->refresh();
            }

            $raidingTeamId = $match->raiding_team_id ?? $match->home_team_id;
            $defendingTeamId = $match->opponentIdOf($raidingTeamId);

            $attackers = $this->onCourt($match, $raidingTeamId);
            $defenders = $this->onCourt($match, $defendingTeamId);

            if ($attackers->isEmpty() || $defenders->isEmpty()) {
                // Should not happen thanks to all-out revivals, but never gamble on it.
                $this->scoring->reviveTeam($match, $attackers->isEmpty() ? $raidingTeamId : $defendingTeamId);
                continue;
            }

            $raider = $this->pickRaider($attackers);
            $roll = mt_rand(1, 100);

            if ($roll <= 46) {
                // Successful raid: usually a single touch, occasionally a multi-point one.
                $touches = $roll <= 36 ? 1 : 2;
                $touches = min($touches, $defenders->count());
                $withBonus = $defenders->count() >= 6 && mt_rand(1, 100) <= 30;
                $shuffled = $defenders->shuffle();
                $out = $shuffled->take($touches);

                // Often a defender lunged in and was beaten; logging that gives
                // the reports a realistic failed-defence column.
                $beaten = $shuffled->skip($touches)->take(mt_rand(1, 100) <= 55 ? 1 : 0);

                $this->scoring->recordRaid($match, [
                    'raider_id' => $raider->id,
                    'result' => Raid::RESULT_SUCCESSFUL,
                    'touch_points' => $touches,
                    'is_bonus' => $withBonus,
                    'defender_out_ids' => $out->pluck('id')->all(),
                    'beaten_defender_ids' => $beaten->pluck('id')->all(),
                ]);
            } elseif ($roll <= 76) {
                // The defence gets him: one tackler, sometimes a two-man chain.
                $tacklers = $defenders->shuffle()->take(mt_rand(1, min(2, $defenders->count())));

                $this->scoring->recordRaid($match, [
                    'raider_id' => $raider->id,
                    'result' => Raid::RESULT_UNSUCCESSFUL,
                    'tackler_ids' => $tacklers->pluck('id')->all(),
                    'action_type' => collect(['tackle', 'ankle_hold', 'thigh_hold', 'dash', 'block'])->random(),
                ]);
            } else {
                $this->scoring->recordRaid($match, [
                    'raider_id' => $raider->id,
                    'result' => Raid::RESULT_EMPTY,
                ]);
            }
        }
    }

    /**
     * Leave a match mid second-half with the clock ticking, so the live display
     * and both operator consoles have something real to show straight away.
     */
    protected function putIntoSecondHalf(GameMatch $match): void
    {
        $match->refresh();

        $match->forceFill([
            'status' => GameMatch::STATUS_LIVE,
            'current_half' => 2,
            'game_clock_remaining_seconds' => 512,
            'game_timer_running' => true,
            'game_timer_updated_at' => now(),
            'raid_clock_remaining_seconds' => $match->raid_duration_seconds,
            'raid_timer_running' => false,
            'raid_timer_updated_at' => now(),
            'is_raid_active' => false,
        ])->save();
    }

    /**
     * Players currently standing on the mat for a side.
     *
     * @return \Illuminate\Support\Collection<int, Player>
     */
    protected function onCourt(GameMatch $match, int $teamId)
    {
        return $match->lineup()
            ->wherePivot('team_id', $teamId)
            ->wherePivot('is_on_court', true)
            ->get();
    }

    /**
     * Raiders carry most of the raids, which keeps the leaderboards realistic.
     *
     * @param  \Illuminate\Support\Collection<int, Player>  $available
     */
    protected function pickRaider($available): Player
    {
        $specialists = $available->where('role', Player::ROLE_RAIDER);

        if ($specialists->isNotEmpty() && mt_rand(1, 100) <= 70) {
            return $specialists->random();
        }

        $allRounders = $available->where('role', Player::ROLE_ALL_ROUNDER);

        if ($allRounders->isNotEmpty() && mt_rand(1, 100) <= 60) {
            return $allRounders->random();
        }

        return $available->random();
    }
}
