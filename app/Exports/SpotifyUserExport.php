<?php

namespace App\Exports;

use App\Models\SpotifyUser;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping; // Tambahkan ini

class SpotifyUserExport implements FromCollection, WithHeadings, WithMapping // Tambahkan ini
{
    public function collection()
    {
        return SpotifyUser::all();
    }

    public function headings(): array
    {
        return [
            'user_id', 'gender', 'age', 'country', 'subscription_type', 'listening_time',
            'songs_played_per_day', 'skip_rate', 'device_type', 'ads_listened_per_week',
            'offline_listening', 'is_churned'
        ];
    }

    /**
    * @param SpotifyUser $user
    *
    * @return array
    */
    public function map($user): array
    {
        // --- PERUBAHAN DI SINI ---
        // Mengubah nilai 1/0 menjadi 'TRUE'/'FALSE' saat ekspor
        return [
            $user->user_id,
            $user->gender,
            $user->age,
            $user->country,
            $user->subscription_type,
            $user->listening_time,
            $user->songs_played_per_day,
            $user->skip_rate,
            $user->device_type,
            $user->ads_listened_per_week,
            $user->offline_listening ? 'TRUE' : 'FALSE',
            $user->is_churned ? 'TRUE' : 'FALSE',
        ];
    }
}