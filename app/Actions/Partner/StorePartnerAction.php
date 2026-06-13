<?php

namespace App\Actions\Partner;

use App\Common\Actions\IStoreAction;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Model;

class StorePartnerAction implements IStoreAction
{

    public function execute(array $data): Partner
    {
        return Partner::create($data);
    }
}
