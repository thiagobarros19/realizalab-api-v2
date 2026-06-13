<?php

namespace App\Http\Controllers;

use App\Actions\Financial\DestroyFinancialAction;
use App\Actions\Financial\PaginateFinancialAction;
use App\Actions\Financial\ShowFinancialAction;
use App\Actions\Financial\StoreFinancialAction;
use App\Actions\Financial\UpdateFinancialAction;
use App\Http\Requests\StoreFinancialRequest;
use App\Http\Requests\UpdateFinancialRequest;
use App\Http\Resources\FinancialResource;
use App\Http\Resources\PaginationResource;
use App\Models\Financial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class FinancialController extends Controller
{
    public function index(Request $request, PaginateFinancialAction $action): ResourceCollection
    {
        $result = $action->execute($request);

        return new PaginationResource($result);
    }

    public function store(StoreFinancialRequest $request, StoreFinancialAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return $this->response(
            message: __('messages.financial.store'),
            data: new FinancialResource($result),
            status: HttpResponse::HTTP_CREATED
        );
    }

    public function show(Financial $financial, ShowFinancialAction $action): JsonResponse
    {
        $result = $action->execute($financial);

        return $this->response(
            message: __('messages.financial.show'),
            data: new FinancialResource($result),
        );
    }

    public function update(UpdateFinancialRequest $request, Financial $financial, UpdateFinancialAction $action): JsonResponse
    {
        $result = $action->execute($request->validated(), $financial);

        return $this->response(
            message: __('messages.financial.update'),
            data: new FinancialResource($result),
        );
    }

    public function destroy(Financial $financial, DestroyFinancialAction $action): JsonResponse
    {
        $action->execute($financial);

        return $this->response(
            message: __('messages.financial.destroy'),
            status: HttpResponse::HTTP_NO_CONTENT
        );
    }
}
