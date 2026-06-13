<?php

namespace App\Actions\Exam;

use App\Common\Actions\IPaginateAction;
use App\Constants\CommonConstants;
use App\Models\Exam;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaginateExamAction implements IPaginateAction
{
    public function execute(Request $request): Paginator|Collection
    {
        $pageSize = (int) $request->query('take', CommonConstants::$defaultPageSize);
        $orderField = $request->query('order-field', CommonConstants::$defaultOrderField);
        $order = $request->query('order', CommonConstants::$defaultOrderBy);

        $query = Exam::query()
            ->with('partner')
            ->when($request->filled('search_term'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->whereAny(
                        ['name', 'code', 'cost', 'price_sus', 'price_particular'],
                        'like',
                        "%{$request->search_term}%"
                    );
                });
            })
            ->when($request->filled('partner_id'), function ($query) use ($request) {
                $partners = explode(",", $request->partner_id);
                $query->whereIn('partner_id', $partners);
            })
            ->orderBy($orderField, $order);

        return $pageSize === 0 ? $query->get() : $query->simplePaginate($pageSize);
    }
}
