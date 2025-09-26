<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SpotifyUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnalyzeChurn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:analyze-churn 
                            {type : The analysis type: \'country\', \'subscription\', \'behavior\', \'distribution\', \'age-distribution\'} 
                            {--column= : The column to get distribution for (e.g., country, gender)}
                            {--filter= : Filter data by a key:value pair (e.g., "subscription_type:Premium")}
                            {--limit=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyzes Spotify churn data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (SpotifyUser::count() == 0) {
            $this->error('The spotify_users table is empty. Please import the data first.');
            return 1;
        }

        $type = $this->argument('type');
        $filter = $this->option('filter');

        switch ($type) {
            case 'country':
            case 'subscription':
                $this->analyzeByCategory($type, $this->option('limit'));
                break;
            case 'behavior':
                $this->analyzeBehavior();
                break;
            case 'distribution':
                $this->analyzeDistribution($this->option('column'), $this->option('limit'));
                break;
            case 'age-distribution':
                $this->analyzeAgeDistribution($filter);
                break;
            default:
                $this->error('Invalid analysis type. Please use one of the available types.');
                return 1;
        }

        return 0;
    }

    /**
     * Menganalisis distribusi frekuensi berdasarkan kelompok usia dengan filter.
     */
    protected function analyzeAgeDistribution(?string $filter)
    {
        $this->info('Analyzing User Distribution by Age Group...');
        
        $query = SpotifyUser::query();

        if ($filter) {
            if (!str_contains($filter, ':')) {
                $this->error('Invalid filter format. Please use key:value (e.g., --filter="country:US")');
                return;
            }
            list($key, $value) = explode(':', $filter, 2);
            
            $this->info("--> Applying filter: [{$key} = {$value}]");
            $query->where($key, $value);
        }

        $results = $query->select(
                DB::raw("CASE 
                    WHEN age <= 20 THEN '20 and Below'
                    WHEN age BETWEEN 21 AND 30 THEN '21 - 30'
                    WHEN age BETWEEN 31 AND 40 THEN '31 - 40'
                    WHEN age BETWEEN 41 AND 50 THEN '41 - 50'
                    ELSE '51 and Above'
                END as age_group"),
                DB::raw('COUNT(*) as total_users')
            )
            ->groupBy('age_group')
            ->orderBy(DB::raw('MIN(age)'))
            ->get();
        
        if ($results->isEmpty()) {
            $this->warn('No users found matching the specified criteria.');
            return;
        }

        $headers = ['Age Group', 'Total Users'];
        $data = $results->map(fn($item) => [$item->age_group, $item->total_users]);

        $this->table($headers, $data);
    }

    /**
     * Menganalisis distribusi frekuensi untuk kolom tertentu.
     */
    protected function analyzeDistribution(?string $column, int $limit)
    {
        $allowedColumns = ['country', 'gender', 'subscription_type', 'device_type'];
        if (empty($column) || !in_array($column, $allowedColumns)) {
            $this->error('Please specify a valid column for distribution using --column=');
            $this->line('Allowed columns: ' . implode(', ', $allowedColumns));
            return;
        }

        $titleName = Str::title(str_replace('_', ' ', $column));
        $this->info("Analyzing User Distribution by {$titleName}...");

        $results = SpotifyUser::query()
            ->select($column, DB::raw('COUNT(*) as total_users'))
            ->groupBy($column)
            ->orderBy('total_users', 'desc')
            ->limit($limit)
            ->get();
        
        $headers = [$titleName, 'Total Users'];
        $data = $results->map(fn($item) => [$item->{$column}, $item->total_users]);

        $this->table($headers, $data);
    }

    /**
     * Menganalisis data churn berdasarkan kategori (negara atau tipe langganan).
     */
    protected function analyzeByCategory(string $category, int $limit)
    {
        $columnName = ($category === 'subscription') ? 'subscription_type' : 'country';
        $titleName = ucwords(str_replace('_', ' ', $columnName));

        $this->info("Analyzing Churn Rate by {$titleName}...");

        $results = SpotifyUser::query()
            ->select(
                $columnName,
                DB::raw('COUNT(*) as total_users'),
                DB::raw('SUM(CAST(is_churned AS INT)) as churned_users'),
                DB::raw('(SUM(CAST(is_churned AS INT)) * 100.0 / COUNT(*)) as churn_rate_percentage')
            )
            ->groupBy($columnName)
            ->orderBy('churn_rate_percentage', 'desc')
            ->limit($limit)
            ->get();

        if ($results->isEmpty()) {
            $this->warn("No data found to analyze for category: {$category}");
            return;
        }

        $headers = [$titleName, 'Total Users', 'Churned Users', 'Churn Rate (%)'];
        
        $data = $results->map(function ($item) use ($columnName) {
            return [
                $item->{$columnName},
                $item->total_users,
                $item->churned_users,
                number_format($item->churn_rate_percentage, 2) . ' %',
            ];
        });

        $this->table($headers, $data);
    }

    /**
     * Menganalisis perilaku rata-rata pengguna yang churn vs. yang aktif.
     */
    protected function analyzeBehavior()
    {
        $this->info('Analyzing Average Behavior of Active vs. Churned Users...');

        $results = SpotifyUser::query()
            ->select(
                'is_churned',
                DB::raw('AVG(listening_time) as avg_listening_time'),
                DB::raw('AVG(skip_rate) as avg_skip_rate'),
                DB::raw('AVG(ads_listened_per_week) as avg_ads_listened')
            )
            ->groupBy('is_churned')
            ->get();

        if ($results->count() < 2) {
            $this->warn('Not enough data to compare active and churned users.');
            return;
        }

        $headers = ['User Status', 'Avg. Listening Time (minutes)', 'Avg. Skip Rate', 'Avg. Ads Listened'];
        
        $data = $results->map(function ($item) {
            return [
                $item->is_churned ? 'Churned Users' : 'Active Users',
                number_format($item->avg_listening_time, 2),
                number_format($item->avg_skip_rate, 2),
                number_format($item->avg_ads_listened, 2),
            ];
        });

        $this->table($headers, $data);
    }
}