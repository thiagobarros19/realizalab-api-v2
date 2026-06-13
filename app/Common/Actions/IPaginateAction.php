<?php

namespace App\Common\Actions;

use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface IPaginateAction
{
    public function execute(Request $request): Paginator|Collection;
}
