<?php

namespace App\Actions\Patient;

use App\Common\Actions\IUpdateAction;
use Illuminate\Database\Eloquent\Model;

class UpdatePatientAction implements IUpdateAction
{
    public function execute(array $data, Model $model): Model
    {
        $model->update($data);

        return $model->fresh();
    }
}
