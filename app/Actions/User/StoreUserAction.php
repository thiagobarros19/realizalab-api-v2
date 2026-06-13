<?php

namespace App\Actions\User;

use App\Common\Actions\IStoreAction;
use App\Models\User;

class StoreUserAction implements IStoreAction
{
    public function execute(array $data): User
    {
        return User::create($data);
    }
}
