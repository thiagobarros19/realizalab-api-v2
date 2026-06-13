<?php

namespace App\Common\Actions;

use Illuminate\Database\Eloquent\Model;

interface IUpdateAction
{
    public function execute(array $data, Model $model): Model;
}
