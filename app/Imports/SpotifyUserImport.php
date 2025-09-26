<?php

namespace App\Imports;

use App\Models\SpotifyUser;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithUpserts;

class SpotifyUserImport implements ToModel, WithHeadingRow, WithUpserts, WithProgressBar
{
    use Importable;

    public function model(array $row)
    {
        return new SpotifyUser([
            'user_id'             => $row['user_id'],
            'gender'              => $row['gender'],
            'age'                 => $row['age'],
            'country'             => $row['country'],
            'subscription_type'   => $row['subscription_type'],
            'listening_time'      => $row['listening_time'],
            'songs_played_per_day' => $row['songs_played_per_day'],
            'skip_rate'           => $row['skip_rate'],
            'device_type'         => $row['device_type'],
            'ads_listened_per_week' => $row['ads_listened_per_week'],
            'offline_listening'   => $row['offline_listening'],
            'is_churned'          => $row['is_churned'],
        ]);
    }

    public function uniqueBy()
    {
        return 'user_id';
    }
}