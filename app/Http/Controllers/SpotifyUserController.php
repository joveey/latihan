<?php

namespace App\Http\Controllers;

use App\Models\SpotifyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Exports\SpotifyUserExport;
use App\Imports\SpotifyUserImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class SpotifyUserController extends Controller
{
    public function index(Request $request)
    {
        $query = SpotifyUser::query();

        if ($request->has('search')) {
            foreach ($request->search as $field => $value) {
                if ($value !== null && $value !== '') {
                    if ($field === 'is_churned') {
                        $query->where($field, $value);
                    } else {
                        $query->where($field, 'like', '%' . $value . '%');
                    }
                }
            }
        }

        $spotifyUsers = $query->paginate(10);

        $genders = SpotifyUser::distinct()->orderBy('gender')->pluck('gender');
        $countries = SpotifyUser::distinct()->orderBy('country')->pluck('country');
        $subscriptions = SpotifyUser::distinct()->orderBy('subscription_type')->pluck('subscription_type');
        $devices = SpotifyUser::distinct()->orderBy('device_type')->pluck('device_type');

        return view('spotify.index', compact('spotifyUsers', 'genders', 'countries', 'subscriptions', 'devices'));
    }

    public function preview(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx']);
        $file = $request->file('file');

        try {
            $import = new SpotifyUserImport();
            $data = Excel::toCollection($import, $file)->first();

            if ($data->isEmpty() || $data->first() === null) {
                 return response()->json(['message' => 'File Excel kosong atau format header tidak terbaca.'], 422);
            }

            $actualHeaders = array_keys($data->first()->toArray());
            $expectedHeaderKeys = array_keys((new SpotifyUserImport)->rules());

            $missingHeaders = array_diff($expectedHeaderKeys, $actualHeaders);
            if (!empty($missingHeaders)) {
                return response()->json(['message' => 'Header file tidak cocok. Header yang hilang: ' . implode(', ', $missingHeaders)], 422);
            }

            $path = $file->store('temp');
            session(['import_file_path' => $path]);
            $previewData = $data->take(10)->toArray();

            return response()->json(['headings' => $actualHeaders, 'data' => $previewData]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses file. Error: ' . $e->getMessage()], 422);
        }
    }

    public function store()
    {
        $path = session('import_file_path');
        if (!$path || !Storage::disk('local')->exists($path)) {
            return response()->json(['error' => 'File tidak ditemukan atau sesi telah berakhir. Silakan unggah ulang.'], 404);
        }

        try {
            (new SpotifyUserImport)->import($path);
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            Storage::disk('local')->delete($path);
            session()->forget('import_file_path');
            return response()->json(['validation_errors' => $errors], 422);
        } catch (\Exception $e) {
            Storage::disk('local')->delete($path);
            session()->forget('import_file_path');
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage()], 500);
        }

        Storage::disk('local')->delete($path);
        session()->forget('import_file_path');
        return response()->json(['success' => 'Data berhasil diimpor!']);
    }

    public function export()
    {
        // Pastikan Anda sudah membuat Export Class ini
        return Excel::download(new SpotifyUserExport, 'spotify_users.xlsx');
    }
}