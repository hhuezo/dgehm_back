<?php

namespace App\Console\Commands;

use App\Imports\FixedAssetImport;
use App\Services\FixedAssetMigrationService;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportFixedAssetsCommand extends Command
{
    protected $signature = 'fixed-assets:import
                            {file? : Ruta al Excel (default: storage/app/activo.xlsx)}
                            {--fresh : Borra activos e importados previos y vuelve a cargar}
                            {--dry-run : Solo valida sin guardar activos}';

    protected $description = 'Migra activos fijos desde Excel; crea responsables faltantes como activo-fijo-solicitante';

    public function handle(FixedAssetMigrationService $migration): int
    {
        $path = $this->argument('file') ?: storage_path('app/activo.xlsx');

        if (!is_file($path)) {
            $alt = '/Users/admin/Desktop/dgehm docs/activo.xlsx';
            if (is_file($alt)) {
                $path = $alt;
            } else {
                $this->error("No se encontró el archivo: {$path}");

                return self::FAILURE;
            }
        }

        if ($this->option('fresh') && !$this->option('dry-run')) {
            $this->warn('Limpiando activos y personas importadas previas...');
            $migration->wipePreviousImport();
        }

        $this->info('Importando: ' . $path);

        $import = new FixedAssetImport(dryRun: (bool) $this->option('dry-run'));
        Excel::import($import, $path);

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Activos importados', $import->imported],
                ['Duplicados omitidos', $import->duplicates],
                ['Filas omitidas', $import->skipped],
                ['Personas creadas', $import->personsCreated],
                ['Personas reutilizadas', $import->personsReused],
                ['Errores', count($import->errors)],
            ]
        );

        if ($import->errors !== []) {
            $this->warn('Primeros errores:');
            foreach (array_slice($import->errors, 0, 25) as $error) {
                $this->line(' - ' . $error);
            }
            if (count($import->errors) > 25) {
                $this->line(' ... y ' . (count($import->errors) - 25) . ' más');
            }
        }

        $this->info($this->option('dry-run') ? 'Dry-run terminado.' : 'Migración terminada.');

        return self::SUCCESS;
    }
}
