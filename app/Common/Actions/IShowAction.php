<?php

namespace App\Common\Actions;

use Illuminate\Database\Eloquent\Model;

interface IShowAction
{
    public function execute(Model $model): Model;
}
