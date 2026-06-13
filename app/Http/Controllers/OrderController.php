<?php

namespace App\Http\Controllers;

use App\Actions\Order\DestroyOrderAction;
use App\Actions\Order\PaginateOrderAction;
use App\Actions\Order\ShowOrderAction;
use App\Actions\Order\StoreOrderAction;
use App\Actions\Order\UpdateOrderAction;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaginationResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class OrderController extends Controller
{
    public function index(Request $request, PaginateOrderAction $action): ResourceCollection
    {
        $result = $action->execute($request);

        return new PaginationResource($result);
    }

    public function store(StoreOrderRequest $request, StoreOrderAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return $this->response(
            message: __('messages.order.store'),
            data: new OrderResource($result),
            status: HttpResponse::HTTP_CREATED
        );
    }

    public function show(Order $order, ShowOrderAction $action): JsonResponse
    {
        $result = $action->execute($order);

        return $this->response(
            message: __('messages.order.show'),
            data: new OrderResource($result),
        );
    }

    public function update(UpdateOrderRequest $request, Order $order, UpdateOrderAction $action): JsonResponse
    {
        $result = $action->execute($request->validated(), $order);

        return $this->response(
            message: __('messages.order.update'),
            data: new OrderResource($result),
        );
    }

    public function destroy(Order $order, DestroyOrderAction $action): JsonResponse
    {
        $action->execute($order);

        return $this->response(
            message: __('messages.order.destroy'),
            status: HttpResponse::HTTP_NO_CONTENT
        );
    }
}
