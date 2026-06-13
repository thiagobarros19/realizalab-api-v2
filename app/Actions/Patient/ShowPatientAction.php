<?php

namespace App\Actions\Patient;

use App\Common\Actions\IShowAction;
use Illuminate\Database\Eloquent\Model;

class ShowPatientAction implements IShowAction
{
    public function execute(Model $model): Model
    {
        return $model->load('orders');
    }
}
