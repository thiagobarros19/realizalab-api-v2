<?php

namespace App\Actions\Patient;

use App\Common\Actions\IDestroyAction;
use Illuminate\Database\Eloquent\Model;

class DestroyPatientAction implements IDestroyAction
{
    public function execute(Model $model): void
    {
        $model->delete();
    }
}
