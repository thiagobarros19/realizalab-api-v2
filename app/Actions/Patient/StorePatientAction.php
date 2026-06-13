<?php

namespace App\Actions\Patient;

use App\Common\Actions\IStoreAction;
use App\Models\Patient;

class StorePatientAction implements IStoreAction
{
    public function execute(array $data): Patient
    {
        return Patient::create($data);
    }
}
