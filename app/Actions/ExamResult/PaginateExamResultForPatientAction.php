<?php

namespace App\Actions\ExamResult;

use App\Constants\CommonConstants;
use App\Models\ExamResult;
use App\Models\Patient;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaginateExamResultForPatientAction
{
    public function execute(Request $request, Patient $patient): Paginator|Collection
    {
        $pageSize = (int) $request->query('take', CommonConstants::$defaultPageSize);
        $orderField = $request->query('order-field', 'released_at');
        $order = $request->query('order', CommonConstants::$defaultOrderBy);

        $query = ExamResult::query()
            ->with('order.orderExams')
            ->where('patient_id', $patient->id)
            ->whereNotNull('released_at')
            ->where('released_at', '<=', now())
            ->orderBy($orderField, $order);

        return $pageSize === 0 ? $query->get() : $query->simplePaginate($pageSize);
    }
}
