<?php

namespace App\Actions\User;

use App\Common\Actions\IShowAction;
use Illuminate\Database\Eloquent\Model;

class ShowUserAction implements IShowAction
{
    public function execute(Model $model): Model
    {
        return $model;
    }
}
