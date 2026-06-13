<?php

namespace App\Actions\Financial;

use App\Common\Actions\IStoreAction;
use App\Models\Financial;

class StoreFinancialAction implements IStoreAction
{
    public function execute(array $data): Financial
    {
        return Financial::create($data);
    }
}
