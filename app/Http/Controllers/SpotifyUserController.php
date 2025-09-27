<?php

namespace App\Http\Controllers;

use App\Models\SpotifyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Exports\SpotifyUserExport;
use App\Imports\SpotifyUserImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SpotifyUserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = SpotifyUser::query();

            if ($request->has('search')) {
                foreach ($request->search as $field => $value) {
                    if ($value !== null && $value !== '') {
                        if ($field === 'is_churned') {
                            $query->where($field, $value === '1' ? true : false);
                        } elseif (in_array($field, ['user_id', 'age', 'listening_time', 'songs_played_per_day', 'ads_listened_per_week'])) {
                            $query->where($field, $value);
                        } elseif ($field === 'skip_rate') {
                            $query->where($field, 'LIKE', '%' . $value . '%');
                        } else {
                            $query->where(DB::raw('LOWER(' . $field . ')'), 'LIKE', '%' . strtolower($value) . '%');
                        }
                    }
                }
            }
            
            $query->orderBy('user_id', 'asc');
            $spotifyUsers = $query->orderBy('id', 'desc')->paginate(10);

            $genders = SpotifyUser::distinct()->orderBy('gender')->pluck('gender')->filter();
            $countries = SpotifyUser::distinct()->orderBy('country')->pluck('country')->filter();
            $subscriptions = SpotifyUser::distinct()->orderBy('subscription_type')->pluck('subscription_type')->filter();
            $devices = SpotifyUser::distinct()->orderBy('device_type')->pluck('device_type')->filter();

            return view('spotify.index', compact('spotifyUsers', 'genders', 'countries', 'subscriptions', 'devices'));

        } catch (\Exception $e) {
            Log::error('Index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan saat memuat data: ' . $e->getMessage());
        }
    }

    public function preview(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:10240'
            ]);

            Log::info('File preview started', ['original_name' => $request->file('file')->getClientOriginalName()]);

            $file = $request->file('file');
            if (!$file->isValid()) {
                return response()->json(['message' => 'File upload gagal. Silakan coba lagi.'], 422);
            }
            $import = new SpotifyUserImport();
            $collections = Excel::toCollection($import, $file);
            
            if ($collections->isEmpty() || $collections->first()->isEmpty()) {
                return response()->json(['message' => 'File Excel kosong atau tidak dapat dibaca.'], 422);
            }

            $data = $collections->first();
            $firstRow = $data->first();

            if (!$firstRow) {
                return response()->json(['message' => 'File Excel tidak memiliki data.'], 422);
            }

            // Get headers
            $actualHeaders = array_keys($firstRow->toArray());
            $expectedHeaders = array_keys((new SpotifyUserImport)->rules());

            // Check required headers
            $missingHeaders = array_diff($expectedHeaders, $actualHeaders);
            if (!empty($missingHeaders)) {
                return response()->json([
                    'message' => 'Header file tidak sesuai. Header yang diperlukan: ' . implode(', ', $expectedHeaders) . '. Header yang hilang: ' . implode(', ', $missingHeaders)
                ], 422);
            }

            // Store file temporarily with unique name
            $filename = 'preview_' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('temp', $filename, 'local');
            
            // Store in session
            session(['import_file_path' => $path]);
            
            // Get preview data (first 10 rows)
            $previewData = $data->take(10)->toArray();

            Log::info('File preview successful', [
                'path' => $path,
                'rows_count' => $data->count(),
                'headers' => $actualHeaders
            ]);

            return response()->json([
                'headings' => $actualHeaders,
                'data' => $previewData,
                'total_rows' => $data->count()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'File tidak valid',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Preview error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file_info' => $request->hasFile('file') ? [
                    'original_name' => $request->file('file')->getClientOriginalName(),
                    'size' => $request->file('file')->getSize(),
                    'mime' => $request->file('file')->getMimeType()
                ] : 'No file'
            ]);

            return response()->json([
                'message' => 'Gagal memproses file Excel: ' . $e->getMessage()
            ], 422);
        }
    }

    public function store()
    {
        try {
            $path = session('import_file_path');
            
            if (!$path || !Storage::disk('local')->exists($path)) {
                return response()->json([
                    'error' => 'File tidak ditemukan atau sesi telah berakhir. Silakan unggah ulang file.'
                ], 404);
            }

            Log::info('Import started', ['path' => $path]);

            $fullPath = Storage::disk('local')->path($path);

            // Start database transaction for PostgreSQL
            DB::beginTransaction();

            try {
                // Import with error handling
                $import = new SpotifyUserImport();
                Excel::import($import, $fullPath);

                DB::commit();

                Log::info('Import completed successfully');

                // Clean up
                Storage::disk('local')->delete($path);
                session()->forget('import_file_path');

                return response()->json([
                    'success' => 'Data berhasil diimpor! Halaman akan dimuat ulang.'
                ]);

            } catch (ValidationException $e) {
                DB::rollback();
                
                $failures = $e->failures();
                $errors = [];
                
                foreach ($failures as $failure) {
                    $errors[] = 'Baris ' . $failure->row() . ', Kolom ' . implode(', ', $failure->attribute()) . ': ' . implode(', ', $failure->errors());
                }

                Log::warning('Import validation failed', ['errors' => $errors]);

                // Clean up
                Storage::disk('local')->delete($path);
                session()->forget('import_file_path');
                
                return response()->json([
                    'validation_errors' => $errors
                ], 422);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Clean up on any error
            if (isset($path) && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
            session()->forget('import_file_path');
            
            return response()->json([
                'error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export()
    {
        try {
            $userCount = SpotifyUser::count();
            
            if ($userCount === 0) {
                return back()->with('error', 'Tidak ada data untuk diekspor. Silakan import data terlebih dahulu.');
            }

            Log::info('Export started', ['user_count' => $userCount]);

            $filename = 'spotify_users_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            return Excel::download(new SpotifyUserExport, $filename);

        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

    /**
     * Get application statistics - PostgreSQL optimized
     */
    public function stats()
    {
        try {
            // Use PostgreSQL efficient aggregation
            $aggregates = SpotifyUser::selectRaw('
                COUNT(*) as total_users,
                COUNT(CASE WHEN is_churned = true THEN 1 END) as churned_users,
                COUNT(CASE WHEN is_churned = false THEN 1 END) as active_users,
                COUNT(DISTINCT country) as countries,
                COUNT(DISTINCT subscription_type) as subscription_types,
                COUNT(DISTINCT device_type) as device_types
            ')->first();

            $stats = [
                'total_users' => (int) $aggregates->total_users,
                'churned_users' => (int) $aggregates->churned_users,
                'active_users' => (int) $aggregates->active_users,
                'countries' => (int) $aggregates->countries,
                'subscription_types' => (int) $aggregates->subscription_types,
                'device_types' => (int) $aggregates->device_types,
                'churn_rate' => 0
            ];

            if ($stats['total_users'] > 0) {
                $stats['churn_rate'] = round(($stats['churned_users'] / $stats['total_users']) * 100, 2);
            }

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Stats error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get statistics'], 500);
        }
    }

    /**
     * Debug endpoint - PostgreSQL version info
     */
    public function debug()
    {
        try {
            // Get PostgreSQL version
            $pgVersion = null;
            try {
                $pgVersion = DB::select("SELECT version()")[0]->version ?? 'Unknown';
            } catch (\Exception $e) {
                $pgVersion = 'Connection failed: ' . $e->getMessage();
            }

            $info = [
                'import_class_exists' => class_exists('App\Imports\SpotifyUserImport'),
                'export_class_exists' => class_exists('App\Exports\SpotifyUserExport'),
                'excel_facade_exists' => class_exists('Maatwebsite\Excel\Facades\Excel'),
                'model_exists' => class_exists('App\Models\SpotifyUser'),
                'storage_temp_exists' => Storage::disk('local')->exists('temp'),
                'storage_temp_writable' => is_writable(storage_path('app')),
                'user_count' => SpotifyUser::count(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'database_connection' => config('database.default'),
                'postgres_version' => $pgVersion,
                'pdo_pgsql_loaded' => extension_loaded('pdo_pgsql')
            ];
        } catch (\Exception $e) {
            $info = [
                'error' => 'Debug failed: ' . $e->getMessage(),
                'database_connection' => config('database.default'),
                'pdo_pgsql_loaded' => extension_loaded('pdo_pgsql')
            ];
        }
        
        return response()->json($info);
    }
}