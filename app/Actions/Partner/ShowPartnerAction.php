<?php

namespace App\Actions\Partner;

use App\Common\Actions\IShowAction;
use Illuminate\Database\Eloquent\Model;

class ShowPartnerAction implements IShowAction
{

    public function execute(Model $model): Model
    {
        return $model;
    }
}
