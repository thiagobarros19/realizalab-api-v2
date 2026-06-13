<?php

namespace App\Actions\Patient;

use App\Common\Actions\IPaginateAction;
use App\Constants\CommonConstants;
use App\Models\Patient;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaginatePatientAction implements IPaginateAction
{
    public function execute(Request $request): Paginator|Collection
    {
        $pageSize = (int) $request->query('take', CommonConstants::$defaultPageSize);
        $orderField = $request->query('order-field', CommonConstants::$defaultOrderField);
        $order = $request->query('order', CommonConstants::$defaultOrderBy);

        $query = Patient::query()
            ->when($request->filled('search_term'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->whereAny(
                        ['name', 'document', 'email', 'phone', 'observations'],
                        'like',
                        "%{$request->search_term}%"
                    );
                });
            })
            ->orderBy($orderField, $order);

        return $pageSize === 0 ? $query->get() : $query->simplePaginate($pageSize);
    }
}
