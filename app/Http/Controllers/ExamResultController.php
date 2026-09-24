<?php

namespace App\Http\Controllers;

use App\Actions\ExamResult\DestroyExamResultAction;
use App\Actions\ExamResult\PaginateExamResultAction;
use App\Actions\ExamResult\PaginateExamResultForPatientAction;
use App\Actions\ExamResult\ReleaseExamResultAction;
use App\Actions\ExamResult\StoreExamResultAction;
use App\Http\Requests\StoreExamResultRequest;
use App\Http\Resources\ExamResultResource;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\PatientExamResultResource;
use App\Models\ExamResult;
use App\Models\ExamResultAccessLog;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamResultController extends Controller
{
    public function index(Request $request, PaginateExamResultAction $action): ResourceCollection
    {
        $result = $action->execute($request);

        return new PaginationResource($result);
    }

    public function store(StoreExamResultRequest $request, StoreExamResultAction $action): JsonResponse
    {
        $result = $action->execute([
            ...$request->validated(),
            'uploaded_by_user_id' => $request->user()->id,
        ]);

        $this->log($result, 'user', $request->user()->id, 'uploaded', $request);

        return $this->response(
            message: __('messages.exam_result.store'),
            data: new ExamResultResource($result),
            status: HttpResponse::HTTP_CREATED,
        );
    }

    public function release(ExamResult $examResult, Request $request, ReleaseExamResultAction $action): JsonResponse
    {
        $result = $action->execute($examResult);

        $this->log($result, 'user', $request->user()->id, 'released', $request);

        return $this->response(
            message: __('messages.exam_result.release'),
            data: new ExamResultResource($result),
        );
    }

    public function destroy(ExamResult $examResult, Request $request, DestroyExamResultAction $action): JsonResponse
    {
        $this->log($examResult, 'user', $request->user()->id, 'deleted', $request);

        $action->execute($examResult);

        return $this->response(
            message: __('messages.exam_result.destroy'),
            status: HttpResponse::HTTP_NO_CONTENT,
        );
    }

    public function indexForPatient(Request $request, PaginateExamResultForPatientAction $action): ResourceCollection
    {
        /** @var Patient $patient */
        $patient = $request->user('patient');

        $result = $action->execute($request, $patient);

        return PatientExamResultResource::collection($result);
    }

    public function download(ExamResult $examResult, Request $request): JsonResponse
    {
        /** @var Patient $patient */
        $patient = $request->user('patient');
        if ($examResult->patient_id !== $patient->id || ! $examResult->isReleased()) {
            abort(HttpResponse::HTTP_NOT_FOUND);
        }

        $this->log($examResult, 'patient', $patient->id, 'downloaded', $request);

        $isRemoteDisk = config("filesystems.disks.{$examResult->file_disk}.driver") === 's3';

        $url = $isRemoteDisk
            ? Storage::disk($examResult->file_disk)->temporaryUrl(
                $examResult->file_path,
                now()->addSeconds(60),
                ['ResponseContentDisposition' => 'attachment; filename="'.$examResult->original_filename.'"'],
            )
            : URL::temporarySignedRoute('exam-results.stream', now()->addSeconds(60), ['examResult' => $examResult->id]);

        return $this->response(
            message: __('messages.exam_result.download'),
            data: ['url' => $url],
        );
    }

    public function stream(ExamResult $examResult): StreamedResponse
    {
        return Storage::disk($examResult->file_disk)->download($examResult->file_path, $examResult->original_filename);
    }

    private function log(ExamResult $examResult, string $actorType, string $actorId, string $action, Request $request): void
    {
        ExamResultAccessLog::create([
            'exam_result_id' => $examResult->id,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
