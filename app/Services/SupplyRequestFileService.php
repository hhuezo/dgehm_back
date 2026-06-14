<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SupplyRequestFileService
{
    public const DIRECTORY = 'supply_requests';

    public const FILE_RULE = 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,gif|max:10240';

    public const ROLE_REQUESTER = 'requester';

    public const ROLE_APPROVER = 'approver';

    public const ROLE_WAREHOUSE_MANAGER = 'warehouse_manager';

    public const ROLE_COLUMNS = [
        self::ROLE_REQUESTER => 'requester_file',
        self::ROLE_APPROVER => 'approver_file',
        self::ROLE_WAREHOUSE_MANAGER => 'warehouse_manager_file',
    ];

    public function store(UploadedFile $uploadedFile, int $supplyRequestId, string $role, ?string $previousFile = null): string
    {
        if (! isset(self::ROLE_COLUMNS[$role])) {
            throw new \InvalidArgumentException("Invalid supply request file role: {$role}");
        }

        $this->delete($previousFile);

        $filename = $this->buildFilename($supplyRequestId, $role, $uploadedFile);
        $uploadedFile->storeAs(self::DIRECTORY, $filename, 'local');

        return $filename;
    }

    public function buildFilename(int $supplyRequestId, string $role, UploadedFile $uploadedFile): string
    {
        return $supplyRequestId . '_' . $role . '.' . $this->resolveExtension($uploadedFile);
    }

    public function delete(?string $filename): void
    {
        if (! $filename) {
            return;
        }

        $path = self::DIRECTORY . '/' . $filename;

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    public function downloadResponse(string $filename)
    {
        $path = self::DIRECTORY . '/' . $filename;

        if (! Storage::disk('local')->exists($path)) {
            return null;
        }

        return Storage::disk('local')->download($path, $filename);
    }

    private function resolveExtension(UploadedFile $uploadedFile): string
    {
        return strtolower(
            $uploadedFile->getClientOriginalExtension()
            ?: $uploadedFile->guessExtension()
            ?: 'bin'
        );
    }
}
