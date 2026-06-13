<?php

namespace App\Http\Controllers;

use App\Actions\Patient\DestroyPatientAction;
use App\Actions\Patient\PaginatePatientAction;
use App\Actions\Patient\ShowPatientAction;
use App\Actions\Patient\StorePatientAction;
use App\Actions\Patient\UpdatePatientAction;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PatientController extends Controller
{
    public function index(Request $request, PaginatePatientAction $action): ResourceCollection
    {
        $result = $action->execute($request);

        return new PaginationResource($result);
    }

    public function store(StorePatientRequest $request, StorePatientAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return $this->response(
            message: __('messages.patient.store'),
            data: new PatientResource($result),
            status: HttpResponse::HTTP_CREATED
        );
    }

    public function show(Patient $patient, ShowPatientAction $action): JsonResponse
    {
        $result = $action->execute($patient);

        return $this->response(
            message: __('messages.patient.show'),
            data: new PatientResource($result),
        );
    }

    public function update(UpdatePatientRequest $request, Patient $patient, UpdatePatientAction $action): JsonResponse
    {
        $result = $action->execute($request->validated(), $patient);

        return $this->response(
            message: __('messages.patient.update'),
            data: new PatientResource($result),
        );
    }

    public function destroy(Patient $patient, DestroyPatientAction $action): JsonResponse
    {
        $action->execute($patient);

        return $this->response(
            message: __('messages.patient.destroy'),
            status: HttpResponse::HTTP_NO_CONTENT
        );
    }
}
