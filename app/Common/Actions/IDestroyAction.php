<?php

namespace App\Common\Actions;

use Illuminate\Database\Eloquent\Model;

interface IDestroyAction
{
    public function execute(Model $model): void;
}
