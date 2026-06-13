<?php

namespace App\Actions\User;

use App\Common\Actions\IUpdateAction;
use Illuminate\Database\Eloquent\Model;

class UpdateUserAction implements IUpdateAction
{
    public function execute(array $data, Model $model): Model
    {
        $model->update($data);

        return $model->fresh();
    }
}
