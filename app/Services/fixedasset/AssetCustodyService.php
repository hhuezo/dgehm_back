<?php

namespace App\Services\fixedasset;

use App\Models\fixedasset\Assignment;
use App\Models\fixedasset\FixedAsset;
use App\Models\fixedasset\MovementStatus;
use App\Models\fixedasset\Transfer;
use Illuminate\Support\Collection;

class AssetCustodyService
{
    /**
     * @return array<int, int> asset_id => person_id
     */
    public function resolveCustodyMap(?int $excludeTransferId = null): array
    {
        $custody = [];

        $assignments = Assignment::query()
            ->where('status_id', MovementStatus::FINALIZED)
            ->with('details:id,fa_assignment_id,fa_fixed_asset_id')
            ->orderBy('date')
            ->orderBy('id')
            ->get(['id', 'date', 'person_id']);

        foreach ($assignments as $assignment) {
            foreach ($assignment->details as $detail) {
                $custody[(int) $detail->fa_fixed_asset_id] = (int) $assignment->person_id;
            }
        }

        $transfersQuery = Transfer::query()
            ->where('status_id', MovementStatus::FINALIZED)
            ->with('details:id,fa_transfer_id,fa_fixed_asset_id')
            ->orderBy('date')
            ->orderBy('id');

        if ($excludeTransferId) {
            $transfersQuery->where('id', '!=', $excludeTransferId);
        }

        foreach ($transfersQuery->get(['id', 'date', 'person_delivers_id', 'person_receives_id']) as $transfer) {
            foreach ($transfer->details as $detail) {
                $custody[(int) $detail->fa_fixed_asset_id] = (int) $transfer->person_receives_id;
            }
        }

        return $custody;
    }

    /**
     * @return array<int, int>
     */
    public function getAssetIdsForPerson(int $personId, ?int $excludeTransferId = null): array
    {
        $custody = $this->resolveCustodyMap($excludeTransferId);

        return array_keys(array_filter(
            $custody,
            fn ($ownerId) => (int) $ownerId === $personId
        ));
    }

    public function getAssetsForPerson(int $personId, ?int $excludeTransferId = null): Collection
    {
        $assetIds = $this->getAssetIdsForPerson($personId, $excludeTransferId);

        if (empty($assetIds)) {
            return collect();
        }

        return FixedAsset::query()
            ->whereIn('id', $assetIds)
            ->with(['category:id,name,code'])
            ->select('id', 'code', 'correlative', 'description', 'fa_category_id')
            ->orderBy('code')
            ->get();
    }

    public function personOwnsAsset(int $personId, int $assetId, ?int $excludeTransferId = null): bool
    {
        $custody = $this->resolveCustodyMap($excludeTransferId);

        return isset($custody[$assetId]) && (int) $custody[$assetId] === $personId;
    }
}
