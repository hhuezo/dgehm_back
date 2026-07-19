<?php

namespace App\Services\fixedasset;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FixedAssetAttachmentService
{
    public const DIRECTORY_ASSIGNMENTS = 'fa_assignments';
    public const DIRECTORY_ASSIGNMENT_RECEPTION_ACTS = 'fa_assignments_reception_acts';
    public const DIRECTORY_TRANSFERS = 'fa_transfers';

    public const FILE_RULE = 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,gif|max:10240';
    public const REQUIRED_FILE_RULE = 'required|file|mimes:pdf,jpg,jpeg,png,webp,gif|max:10240';

    public function store(
        UploadedFile $uploadedFile,
        string $directory,
        int|string $entityId,
        ?string $previousFile = null
    ): string {
        $this->delete($directory, $previousFile);

        $filename = $this->buildFilename($entityId, $uploadedFile);
        $uploadedFile->storeAs($directory, $filename, 'local');

        return $filename;
    }

    public function delete(string $directory, ?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = $directory . '/' . $filename;

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    public function download(string $directory, string $filename)
    {
        $path = $directory . '/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Archivo no encontrado.');
        }

        return Storage::disk('local')->download($path, $filename);
    }

    private function buildFilename(int|string $entityId, UploadedFile $uploadedFile): string
    {
        $extension = strtolower(
            $uploadedFile->getClientOriginalExtension()
                ?: $uploadedFile->guessExtension()
                ?: 'bin'
        );

        $safeId = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) $entityId);

        return $safeId . '_' . time() . '.' . $extension;
    }
}
