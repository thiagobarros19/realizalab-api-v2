<?php

namespace App\Actions\Order;

use App\Common\Actions\IDestroyAction;
use Illuminate\Database\Eloquent\Model;

class DestroyOrderAction implements IDestroyAction
{
    public function execute(Model $model): void
    {
        $model->delete();
    }
}
