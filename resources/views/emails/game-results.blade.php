

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Playard Curling Results</title>
</head>
<body style="margin:0; padding:0; background:#111111; font-family:Arial, sans-serif; color:#ffffff;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#111111; padding:24px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:640px; background:#18181b; border-radius:24px; overflow:hidden;">
                    <tr>
                        <td style="background:#dc2626; padding:28px; text-align:center;">
                            <div style="display:inline-block; background:#ffffff; color:#dc2626; font-size:30px; font-weight:900; padding:10px 18px; border-radius:12px; letter-spacing:-1px;">
                                PLAYARD
                            </div>
                            <p style="margin:14px 0 0; color:#ffffff; font-weight:800; letter-spacing:3px; text-transform:uppercase; font-size:12px;">
                                Curling Results
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px; text-align:center;">
                            <p style="margin:0; color:#f87171; font-weight:800; text-transform:uppercase; font-size:12px; letter-spacing:2px;">
                                Winner
                            </p>
                            <h1 style="margin:8px 0 8px; font-size:42px; line-height:1.05; color:#ffffff;">
                                {{ $session->winner_team_name ?: 'Your game results are ready' }}
                            </h1>
                            <p style="margin:0; color:#d4d4d8; font-size:18px;">
                                {{ $session->resource->name }} at Playard
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    @foreach ($session->teams as $team)
                                        <td width="50%" style="padding:8px; vertical-align:top;">
                                            <div style="background:{{ $team->colour === 'red' ? '#dc2626' : '#facc15' }}; color:{{ $team->colour === 'red' ? '#ffffff' : '#000000' }}; border-radius:20px; padding:20px; text-align:center;">
                                                <p style="margin:0; font-weight:900; font-size:20px;">{{ $team->name }}</p>
                                                <p style="margin:12px 0 0; font-weight:900; font-size:64px; line-height:1;">{{ $team->total_score }}</p>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 32px; text-align:center;">
                            <a href="{{ $shareUrl }}" style="display:block; background:#ffffff; color:#000000; text-decoration:none; font-weight:900; border-radius:18px; padding:18px 22px; font-size:18px;">
                                Open and Share Scorecard
                            </a>
                            <p style="margin:18px 0 0; color:#a1a1aa; font-size:14px; line-height:1.5;">
                                Share your result on WhatsApp or Facebook. For Instagram, open the scorecard, take a screenshot and tag Playard.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#0a0a0a; padding:22px; text-align:center; color:#a1a1aa; font-size:13px;">
                            Games. Drinks. Good times. Played at Playard Peterborough.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>