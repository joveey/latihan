<?php
// File: app/Imports/SpotifyUserImport.php

namespace App\Imports;

use App\Models\SpotifyUser;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SpotifyUserImport implements ToModel, WithHeadingRow, WithUpserts, WithValidation, WithBatchInserts, WithChunkReading
{
    use Importable;

    /**
     * Transform a row into a model instance
     */
    public function model(array $row)
    {
        // Handle boolean values
        $offlineListening = $this->parseBoolean($row['offline_listening'] ?? false);
        $isChurned = $this->parseBoolean($row['is_churned'] ?? false);

        return new SpotifyUser([
            'user_id'               => (int) ($row['user_id'] ?? 0),
            'gender'                => $row['gender'] ?? '',
            'age'                   => (int) ($row['age'] ?? 0),
            'country'               => $row['country'] ?? '',
            'subscription_type'     => $row['subscription_type'] ?? '',
            'listening_time'        => (int) ($row['listening_time'] ?? 0),
            'songs_played_per_day'  => (int) ($row['songs_played_per_day'] ?? 0),
            'skip_rate'             => (float) ($row['skip_rate'] ?? 0),
            'device_type'           => $row['device_type'] ?? '',
            'ads_listened_per_week' => (int) ($row['ads_listened_per_week'] ?? 0),
            'offline_listening'     => $offlineListening,
            'is_churned'            => $isChurned,
        ]);
    }

    /**
     * Specify the unique column for upserts
     */
    public function uniqueBy()
    {
        return 'user_id';
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|min:1',
            'gender' => 'required|string|max:10',
            'age' => 'required|integer|min:13|max:100',
            'country' => 'required|string|max:10',
            'subscription_type' => 'required|string|max:20',
            'listening_time' => 'required|integer|min:0',
            'songs_played_per_day' => 'required|integer|min:0',
            'skip_rate' => 'required|numeric|min:0|max:1',
            'device_type' => 'required|string|max:20',
            'ads_listened_per_week' => 'required|integer|min:0',
            'offline_listening' => 'required',
            'is_churned' => 'required',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'user_id.required' => 'User ID is required',
            'user_id.integer' => 'User ID must be a number',
            'age.min' => 'Age must be at least 13',
            'age.max' => 'Age cannot be more than 100',
            'skip_rate.max' => 'Skip rate cannot be more than 1.0',
        ];
    }

    /**
     * Batch size for inserts
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Parse boolean values from various formats
     */
    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['true', '1', 'yes', 'y', 'on']);
        }

        return false;
    }
}