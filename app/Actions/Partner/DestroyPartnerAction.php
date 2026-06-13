<?php

namespace App\Actions\Partner;

use App\Common\Actions\IDestroyAction;
use Illuminate\Database\Eloquent\Model;

class DestroyPartnerAction implements IDestroyAction
{

    public function execute(Model $model): void
    {
        $model->delete();
    }
}
