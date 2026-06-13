<?php

namespace App\Actions\Financial;

use App\Common\Actions\IShowAction;
use Illuminate\Database\Eloquent\Model;

class ShowFinancialAction implements IShowAction
{
    public function execute(Model $model): Model
    {
        return $model;
    }
}
