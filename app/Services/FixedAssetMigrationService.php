<?php

namespace App\Services;

use App\Imports\FixedAssetImport;
use App\Models\Employee;
use App\Models\User;
use App\Models\fixedasset\FixedAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FixedAssetMigrationService
{
    private const CACHE_TTL_HOURS = 6;

    private const CHUNK_SIZE = 40;

    public function start(UploadedFile $file, bool $fresh = false): array
    {
        $jobId = (string) Str::uuid();
        $relativePath = $file->storeAs('imports', "{$jobId}.xlsx");

        if ($fresh) {
            $this->wipePreviousImport();
        }

        $absolutePath = Storage::path($relativePath);
        $rows = $this->extractRows($absolutePath);
        $jsonPath = "imports/{$jobId}.json";
        Storage::put($jsonPath, json_encode($rows, JSON_UNESCAPED_UNICODE));

        $state = [
            'status' => 'ready',
            'message' => 'Archivo listo para migrar.',
            'file' => $relativePath,
            'json' => $jsonPath,
            'total' => count($rows),
            'processed' => 0,
            'offset' => 0,
            'imported' => 0,
            'skipped' => 0,
            'duplicates' => 0,
            'persons_created' => 0,
            'persons_reused' => 0,
            'errors' => [],
            'percent' => 0,
            'fresh' => $fresh,
        ];

        $this->putState($jobId, $state);

        return [
            'job_id' => $jobId,
            'total' => $state['total'],
            'chunk_size' => self::CHUNK_SIZE,
            'fresh' => $fresh,
        ];
    }

    public function process(string $jobId, ?int $chunkSize = null): array
    {
        $state = $this->getState($jobId);
        if (!$state) {
            throw new \RuntimeException('La migración no existe o expiró. Vuelve a subir el archivo.');
        }

        if (($state['status'] ?? '') === 'completed') {
            return $state;
        }

        if (($state['status'] ?? '') === 'failed') {
            return $state;
        }

        $chunkSize = $chunkSize && $chunkSize > 0 ? min($chunkSize, 100) : self::CHUNK_SIZE;
        $allRows = json_decode(Storage::get($state['json']), true) ?? [];
        $offset = (int) $state['offset'];
        $slice = array_slice($allRows, $offset, $chunkSize);

        if ($slice === []) {
            $state['status'] = 'completed';
            $state['message'] = 'Migración completada.';
            $state['percent'] = 100;
            $this->putState($jobId, $state);
            $this->cleanupFiles($state);

            return $state;
        }

        $state['status'] = 'processing';
        $state['message'] = 'Migrando activos...';
        $this->putState($jobId, $state);

        set_time_limit(120);

        try {
            $import = new FixedAssetImport();
            $import->processRows(collect($slice), $offset);

            $state['offset'] = $offset + count($slice);
            $state['processed'] = min($state['total'], $state['offset']);
            $state['imported'] += $import->imported;
            $state['skipped'] += $import->skipped;
            $state['duplicates'] += $import->duplicates;
            $state['persons_created'] = Employee::query()
                ->where('email', 'like', '%@import.activo-fijo.local')
                ->count();
            $state['persons_reused'] += $import->personsReused;
            $state['errors'] = array_slice(array_merge($state['errors'], $import->errors), 0, 100);
            $state['percent'] = $state['total'] > 0
                ? (int) floor(($state['processed'] / $state['total']) * 100)
                : 100;

            if ($state['offset'] >= $state['total']) {
                $state['status'] = 'completed';
                $state['message'] = 'Migración completada.';
                $state['percent'] = 100;
                $this->putState($jobId, $state);
                $this->cleanupFiles($state);
            } else {
                $state['message'] = "Procesados {$state['processed']} de {$state['total']}.";
                $this->putState($jobId, $state);
            }
        } catch (\Throwable $e) {
            $state['status'] = 'failed';
            $state['message'] = $e->getMessage();
            $state['errors'][] = $e->getMessage();
            $this->putState($jobId, $state);
        }

        return $state;
    }

    public function progress(string $jobId): array
    {
        $state = $this->getState($jobId);
        if (!$state) {
            throw new \RuntimeException('La migración no existe o expiró.');
        }

        return $state;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractRows(string $absolutePath): array
    {
        $spreadsheet = IOFactory::load($absolutePath);
        $sheet = $spreadsheet->getSheetByName('BASE') ?? $spreadsheet->getActiveSheet();
        // formatData=false: fechas como serial de Excel (número), no como texto localizado
        $matrix = $sheet->toArray(null, true, false, false);

        if ($matrix === []) {
            return [];
        }

        $headerRow = array_shift($matrix);
        $headers = [];
        foreach ($headerRow as $index => $header) {
            $headers[$index] = $this->normalizeKey((string) ($header ?? "col_{$index}"));
        }

        $rows = [];
        foreach ($matrix as $row) {
            $assoc = [];
            $hasValue = false;
            foreach ($headers as $index => $key) {
                if ($key === '') {
                    continue;
                }
                $value = $row[$index] ?? null;
                if ($value !== null && $value !== '') {
                    $hasValue = true;
                }
                $assoc[$key] = $value;
            }
            if ($hasValue) {
                $rows[] = $assoc;
            }
        }

        return $rows;
    }

    private function normalizeKey(string $key): string
    {
        $key = mb_strtolower($key);
        $key = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $key);
        $key = preg_replace('/[^a-z0-9]+/', '_', $key) ?? '';

        return trim($key, '_');
    }

    public function wipePreviousImport(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('fa_assignment_details')) {
            DB::table('fa_assignment_details')->truncate();
        }
        if (Schema::hasTable('fa_assignments')) {
            DB::table('fa_assignments')->truncate();
        }
        if (Schema::hasTable('fa_transfer_details')) {
            DB::table('fa_transfer_details')->truncate();
        }
        if (Schema::hasTable('fa_transfers')) {
            DB::table('fa_transfers')->truncate();
        }

        FixedAsset::query()->delete();

        $importedEmployees = Employee::withTrashed()
            ->where('email', 'like', '%@import.activo-fijo.local')
            ->get();

        $userIds = $importedEmployees->pluck('user_id')->filter()->unique()->values();

        foreach ($importedEmployees as $employee) {
            DB::table('fa_category_employee')->where('adm_employee_id', $employee->id)->delete();
            $employee->forceDelete();
        }

        if ($userIds->isNotEmpty()) {
            DB::table('model_has_roles')
                ->whereIn('model_id', $userIds)
                ->where('model_type', User::class)
                ->delete();
            User::whereIn('id', $userIds)->delete();
        }

        Schema::enableForeignKeyConstraints();
    }

    private function putState(string $jobId, array $state): void
    {
        Cache::put($this->cacheKey($jobId), $state, now()->addHours(self::CACHE_TTL_HOURS));
    }

    private function getState(string $jobId): ?array
    {
        $state = Cache::get($this->cacheKey($jobId));

        return is_array($state) ? $state : null;
    }

    private function cacheKey(string $jobId): string
    {
        return "fa_import:{$jobId}";
    }

    private function cleanupFiles(array $state): void
    {
        if (!empty($state['file'])) {
            Storage::delete($state['file']);
        }
        if (!empty($state['json'])) {
            Storage::delete($state['json']);
        }
    }
}
