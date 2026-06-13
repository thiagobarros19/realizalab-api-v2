<?php

namespace App\Actions\Partner;

use App\Common\Actions\IUpdateAction;
use Illuminate\Database\Eloquent\Model;

class UpdatePartnerAction implements IUpdateAction
{

    public function execute(array $data, Model $model): Model
    {
        $model->update($data);

        return $model->fresh();
    }
}
