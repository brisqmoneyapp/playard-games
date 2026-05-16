<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\GameResource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $curling = Activity::updateOrCreate(
            ['slug' => 'curling'],
            [
                'name' => 'Curling',
                'resource_label' => 'Lane',
                'is_active' => true,
                'how_to_play' => "Split into two teams. Take turns sliding stones towards the target. The team with the closest stone to the centre wins the round. Only that team scores. Score one point for each stone closer than the opponent's closest stone. Highest score wins.",
                'rules' => [
                    'Only one team scores per round.',
                    'Closest stone to the centre wins the round.',
                    'That team scores one point for each stone closer than the opponent closest stone.',
                    'If the game is tied, play a sudden death final stone.',
                ],
            ]
        );

        foreach (range(1, 4) as $number) {
            GameResource::updateOrCreate(
                ['slug' => 'curling-lane-' . $number],
                [
                    'activity_id' => $curling->id,
                    'name' => 'Lane ' . $number,
                    'sort_order' => $number,
                    'is_active' => true,
                ]
            );
        }
    }
}