<?php

namespace App\Http\Controllers\Share;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\GameSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ShareController extends Controller
{
    public function show(GameSession $session): View
    {
        if ($session->shareHasExpired()) {
            abort(404, 'This Playard scorecard has expired.');
        }

        $session->load(['activity', 'resource', 'teams.players', 'rounds.winningTeam']);

        return view('share.show', [
            'session' => $session,
        ]);
    }

    public function exportCustomers(Request $request)
    {
        $fileName = 'playard-games-customers-' . now()->format('Y-m-d-His') . '.csv';

        return Response::streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'first_name',
                'last_name',
                'email',
                'marketing_consent',
                'marketing_consent_at',
                'marketing_source',
                'last_game_at',
                'games_played',
            ]);

            Customer::query()
                ->where('marketing_consent', true)
                ->orderBy('email')
                ->chunk(200, function ($customers) use ($handle) {
                    foreach ($customers as $customer) {
                        fputcsv($handle, [
                            $customer->first_name,
                            $customer->last_name,
                            $customer->email,
                            $customer->marketing_consent ? 'yes' : 'no',
                            optional($customer->marketing_consent_at)->toDateTimeString(),
                            $customer->marketing_source,
                            optional($customer->last_game_at)->toDateTimeString(),
                            $customer->games_played,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
