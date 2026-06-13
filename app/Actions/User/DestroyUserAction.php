<?php

namespace App\Actions\User;

use App\Common\Actions\IDestroyAction;
use Illuminate\Database\Eloquent\Model;

class DestroyUserAction implements IDestroyAction
{
    public function execute(Model $model): void
    {
        $model->delete();
    }
}
