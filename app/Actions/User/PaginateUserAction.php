<?php

namespace App\Actions\User;

use App\Common\Actions\IPaginateAction;
use App\Constants\CommonConstants;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaginateUserAction implements IPaginateAction
{
    public function execute(Request $request): Paginator|Collection
    {
        $pageSize = (int) $request->query('take', CommonConstants::$defaultPageSize);
        $orderField = $request->query('order-field', CommonConstants::$defaultOrderField);
        $order = $request->query('order', CommonConstants::$defaultOrderBy);

        $query = User::query()->orderBy($orderField, $order);

        return $pageSize === 0 ? $query->get() : $query->simplePaginate($pageSize);
    }
}
