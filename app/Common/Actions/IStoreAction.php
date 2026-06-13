<?php

namespace App\Common\Actions;

use Illuminate\Database\Eloquent\Model;

interface IStoreAction
{
    public function execute(array $data): Model;
}
