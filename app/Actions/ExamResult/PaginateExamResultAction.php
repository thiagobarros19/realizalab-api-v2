<?php

namespace App\Actions\ExamResult;

use App\Common\Actions\IPaginateAction;
use App\Constants\CommonConstants;
use App\Models\ExamResult;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaginateExamResultAction implements IPaginateAction
{
    public function execute(Request $request): Paginator|Collection
    {
        $pageSize = (int) $request->query('take', CommonConstants::$defaultPageSize);
        $orderField = $request->query('order-field', CommonConstants::$defaultOrderField);
        $order = $request->query('order', CommonConstants::$defaultOrderBy);

        $query = ExamResult::query()
            ->with(['order.orderExams', 'patient', 'uploadedBy'])
            ->when($request->filled('patient_id'), fn ($query) => $query->where('patient_id', $request->query('patient_id')))
            ->when($request->filled('order_id'), fn ($query) => $query->where('order_id', $request->query('order_id')))
            ->orderBy($orderField, $order);

        return $pageSize === 0 ? $query->get() : $query->simplePaginate($pageSize);
    }
}
