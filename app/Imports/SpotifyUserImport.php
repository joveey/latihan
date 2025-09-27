<?php

namespace App\Imports;

use App\Models\SpotifyUser;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SpotifyUserImport implements ToModel, WithHeadingRow, WithUpserts, WithValidation
{
    use Importable;

    public function model(array $row)
    {
        $offlineListening = $row['offline_listening'];
        $isChurned = $row['is_churned'];

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
            'offline_listening'   => strtolower($offlineListening) === 'true' || $offlineListening === 1 || $offlineListening === '1',
            'is_churned'          => strtolower($isChurned) === 'true' || $isChurned === 1 || $isChurned === '1',
        ]);
    }

    public function uniqueBy()
    {
        return 'user_id';
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer',
            'gender' => 'required|string',
            'age' => 'required|integer',
            'country' => 'required|string',
            'subscription_type' => 'required|string',
            'listening_time' => 'required|integer',
            'songs_played_per_day' => 'required|integer',
            'skip_rate' => 'required|numeric',
            'device_type' => 'required|string',
            'ads_listened_per_week' => 'required|integer',
            'offline_listening' => 'required',
            'is_churned' => 'required',
        ];
    }
}