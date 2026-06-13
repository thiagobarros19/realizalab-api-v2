<?php

namespace App\Http\Controllers;

use App\Actions\Exam\DestroyExamAction;
use App\Actions\Exam\PaginateExamAction;
use App\Actions\Exam\ShowExamAction;
use App\Actions\Exam\StoreExamAction;
use App\Actions\Exam\UpdateExamAction;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Http\Resources\ExamResource;
use App\Http\Resources\PaginationResource;
use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ExamController extends Controller
{
    public function index(Request $request, PaginateExamAction $action): ResourceCollection
    {
        $result = $action->execute($request);

        return new PaginationResource($result);
    }

    public function store(StoreExamRequest $request, StoreExamAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return $this->response(
            message: __('messages.exam.store'),
            data: new ExamResource($result),
            status: HttpResponse::HTTP_CREATED
        );
    }

    public function show(Exam $exam, ShowExamAction $action): JsonResponse
    {
        $result = $action->execute($exam);

        return $this->response(
            message: __('messages.exam.show'),
            data: new ExamResource($result),
        );
    }

    public function update(UpdateExamRequest $request, Exam $exam, UpdateExamAction $action): JsonResponse
    {
        $result = $action->execute($request->validated(), $exam);

        return $this->response(
            message: __('messages.exam.update'),
            data: new ExamResource($result),
        );
    }

    public function destroy(Exam $exam, DestroyExamAction $action): JsonResponse
    {
        $action->execute($exam);

        return $this->response(
            message: __('messages.exam.destroy'),
            status: HttpResponse::HTTP_NO_CONTENT
        );
    }
}
