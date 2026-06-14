<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AdmEmployeeDocumentType extends Pivot
{
    public $incrementing = true;

    protected $table = 'adm_document_type_adm_employee';

    protected $fillable = [
        'value',
        'adm_employee_id',
        'adm_document_type_id',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'adm_employee_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(AdmDocumentType::class, 'adm_document_type_id');
    }
}
