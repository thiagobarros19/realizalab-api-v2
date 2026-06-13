<?php

namespace App\Actions\Order;

use App\Common\Actions\IPaginateAction;
use App\Constants\CommonConstants;
use App\Models\Order;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaginateOrderAction implements IPaginateAction
{
    public function execute(Request $request): Paginator|Collection
    {
        $pageSize = (int) $request->query('take', CommonConstants::$defaultPageSize);
        $orderField = $request->query('order-field', CommonConstants::$defaultOrderField);
        $order = $request->query('order', CommonConstants::$defaultOrderBy);

        $query = Order::query()
            ->with('patient', 'exams')
            ->when($request->filled('search_term'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->whereHas('patient', function ($query) use ($request) {
                        $query->where('name', 'like', "%{$request->search_term}%");
                    });
                });
            })
            ->when($request->filled('patient_id'), function ($query) use ($request) {
                $patients = explode(",", $request->patient_id);
                $query->whereIn('patient_id', $patients);
            })
            ->orderBy($orderField, $order);

        return $pageSize === 0 ? $query->get() : $query->simplePaginate($pageSize);
    }
}
