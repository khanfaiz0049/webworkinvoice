<?php

namespace App\Repositories;

use App\Models\Invoice;

class InvoiceRepository extends BaseRepository
{
    public function __construct(Invoice $model)
    {
        parent::__construct($model);
    }

    public function getCompanyInvoices($companyId)
    {
        return $this->model->where('company_id', $companyId)->latest()->get();
    }
}
