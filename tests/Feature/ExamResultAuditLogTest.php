<?php

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamResultAccessLog;
use App\Models\Order;
use App\Models\OrderExam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('uploading and releasing an exam result writes audit log entries with the staff actor', function () {
    Storage::fake('local');

    $user = User::factory()->create(['password' => bcrypt('password')]);
    $token = $user->createToken('access-token', ['access'], now()->addMinutes(60))->plainTextToken;

    $order = Order::factory()->create(['type' => 'particular']);
    $exam = Exam::factory()->create();
    $orderExam = OrderExam::factory()->create([
        'order_id' => $order->id,
        'exam_id' => $exam->id,
        'exam_name' => $exam->name,
        'exam_price' => 30,
    ]);

    $storeResponse = $this->withToken($token)->postJson('/exam-result', [
        'order_id' => $order->id,
        'file' => UploadedFile::fake()->create('resultado.pdf', 100, 'application/pdf'),
    ]);

    $examResultId = $storeResponse->json('data.id');

    expect(ExamResultAccessLog::query()
        ->where('exam_result_id', $examResultId)
        ->where('actor_type', 'user')
        ->where('actor_id', $user->id)
        ->where('action', 'uploaded')
        ->exists())->toBeTrue();

    $this->withToken($token)->patchJson("/exam-result/{$examResultId}/release");

    expect(ExamResultAccessLog::query()
        ->where('exam_result_id', $examResultId)
        ->where('action', 'released')
        ->exists())->toBeTrue();
});

test('deleting an exam result writes an audit log entry before removal', function () {
    Storage::fake('local');

    $user = User::factory()->create(['password' => bcrypt('password')]);
    $token = $user->createToken('access-token', ['access'], now()->addMinutes(60))->plainTextToken;

    $order = Order::factory()->create(['type' => 'particular']);
    $exam = Exam::factory()->create();
    $orderExam = OrderExam::factory()->create([
        'order_id' => $order->id,
        'exam_id' => $exam->id,
        'exam_name' => $exam->name,
        'exam_price' => 30,
    ]);
    $examResult = ExamResult::factory()->create([
        'order_id' => $order->id,
        'patient_id' => $order->patient_id,
    ]);

    $this->withToken($token)->deleteJson("/exam-result/{$examResult->id}");

    expect(ExamResultAccessLog::query()
        ->where('exam_result_id', $examResult->id)
        ->where('action', 'deleted')
        ->exists())->toBeTrue();
});
