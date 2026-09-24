<?php

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Order;
use App\Models\OrderExam;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function staffToken(): string
{
    $user = User::factory()->create(['password' => bcrypt('password')]);

    return $user->createToken('access-token', ['access'], now()->addMinutes(60))->plainTextToken;
}

function makeOrder(): Order
{
    $patient = Patient::factory()->create();
    $order = Order::factory()->create(['type' => 'particular', 'patient_id' => $patient->id]);
    $exam = Exam::factory()->create(['name' => 'Hemograma', 'code' => 'HEM', 'cost' => 10, 'price_sus' => 20, 'price_particular' => 30]);

    OrderExam::factory()->create([
        'order_id' => $order->id,
        'exam_id' => $exam->id,
        'exam_name' => $exam->name,
        'exam_price' => 30,
    ]);

    return $order;
}

test('staff can upload a pdf exam result', function () {
    Storage::fake('local');

    $order = makeOrder();
    $token = staffToken();

    $response = $this->withToken($token)->postJson('/exam-result', [
        'order_id' => $order->id,
        'file' => UploadedFile::fake()->create('resultado.pdf', 100, 'application/pdf'),
    ]);

    $response->assertCreated();

    expect(ExamResult::count())->toBe(1);
});

test('staff can upload more than one result file for the same order', function () {
    Storage::fake('local');

    $order = makeOrder();
    $token = staffToken();

    $this->withToken($token)->postJson('/exam-result', [
        'order_id' => $order->id,
        'file' => UploadedFile::fake()->create('resultado-1.pdf', 100, 'application/pdf'),
    ])->assertCreated();

    $this->withToken($token)->postJson('/exam-result', [
        'order_id' => $order->id,
        'file' => UploadedFile::fake()->create('resultado-2.pdf', 100, 'application/pdf'),
    ])->assertCreated();

    expect(ExamResult::where('order_id', $order->id)->count())->toBe(2);
});

test('non pdf file is rejected', function () {
    Storage::fake('local');

    $order = makeOrder();
    $token = staffToken();

    $response = $this->withToken($token)->postJson('/exam-result', [
        'order_id' => $order->id,
        'file' => UploadedFile::fake()->create('resultado.txt', 10, 'text/plain'),
    ]);

    $response->assertUnprocessable();
});

test('oversized file is rejected', function () {
    Storage::fake('local');

    $order = makeOrder();
    $token = staffToken();

    $response = $this->withToken($token)->postJson('/exam-result', [
        'order_id' => $order->id,
        'file' => UploadedFile::fake()->create('resultado.pdf', 30_000, 'application/pdf'),
    ]);

    $response->assertUnprocessable();
});

test('staff can release an exam result', function () {
    Storage::fake('local');

    $order = makeOrder();
    $examResult = ExamResult::factory()->create([
        'order_id' => $order->id,
        'patient_id' => $order->patient_id,
    ]);
    $token = staffToken();

    $response = $this->withToken($token)->patchJson("/exam-result/{$examResult->id}/release");

    $response->assertOk();

    expect($examResult->fresh()->released_at)->not->toBeNull();
});
