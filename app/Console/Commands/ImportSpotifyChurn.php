<?php

namespace App\Console\Commands;

use App\Imports\SpotifyUserImport;
use Illuminate\Console\Command;
class ImportSpotifyChurn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-spotify-churn {file : The path to the CSV file relative to the storage/app folder.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports Spotify churn data from a given CSV file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $file = $this->argument('file');
        
        $filePath = storage_path('app/' . $file);

        if (!file_exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            return 1; 
        }

        $this->info("Starting import for file: {$file}");

        try {
            $import = new SpotifyUserImport;
            $import->withOutput($this->output);
            $import->import($filePath);

            $this->output->success('Import completed successfully!');

        } catch (\Exception $e) {
            $this->error('An error occurred during the import: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}