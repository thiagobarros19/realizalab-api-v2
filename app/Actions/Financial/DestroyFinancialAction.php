<?php

namespace App\Actions\Financial;

use App\Common\Actions\IDestroyAction;
use Illuminate\Database\Eloquent\Model;

class DestroyFinancialAction implements IDestroyAction
{
    public function execute(Model $model): void
    {
        $model->delete();
    }
}
