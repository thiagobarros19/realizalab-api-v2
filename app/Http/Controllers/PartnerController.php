<?php

namespace App\Http\Controllers;

use App\Actions\Partner\DestroyPartnerAction;
use App\Actions\Partner\PaginatePartnerAction;
use App\Actions\Partner\ShowPartnerAction;
use App\Actions\Partner\StorePartnerAction;
use App\Actions\Partner\UpdatePartnerAction;
use App\Http\Requests\StorePartnerRequest;
use App\Http\Requests\UpdatePartnerRequest;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PaginatePartnerAction $action): ResourceCollection
    {
        $result = $action->execute($request);
        return new PaginationResource($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePartnerRequest $request, StorePartnerAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());
        return $this->response(
            message: __('messages.partner.store'),
            data: new PartnerResource($result),
            status: HttpResponse::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Partner $partner, ShowPartnerAction $action): JsonResponse
    {
        $result = $action->execute($partner);
        return $this->response(
            message: __('messages.partner.show'),
            data: new PartnerResource($result),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePartnerRequest $request, Partner $partner, UpdatePartnerAction $action): JsonResponse
    {
        $result = $action->execute($request->validated(), $partner);
        return $this->response(
            message: __('messages.partner.update'),
            data: new PartnerResource($result),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partner $partner, DestroyPartnerAction $action): JsonResponse
    {
        $action->execute($partner);
        return $this->response(
            message: __('messages.partner.destroy'),
            status: HttpResponse::HTTP_NO_CONTENT
        );
    }
}
