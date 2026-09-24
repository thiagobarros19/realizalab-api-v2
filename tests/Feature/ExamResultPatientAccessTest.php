<?php

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamResultAccessLog;
use App\Models\Order;
use App\Models\OrderExam;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function orderForPatient(Patient $patient): Order
{
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

function patientToken(Patient $patient): string
{
    return $patient->createToken('patient-access-token', ['patient-access'], now()->addMinutes(30))->plainTextToken;
}

test('patient sees only their own released results', function () {
    Storage::fake('local');

    $patientA = Patient::factory()->create();
    $orderA = orderForPatient($patientA);
    ExamResult::factory()->released()->create(['order_id' => $orderA->id, 'patient_id' => $patientA->id]);
    ExamResult::factory()->create(['order_id' => $orderA->id, 'patient_id' => $patientA->id]); // not released

    $patientB = Patient::factory()->create();
    $orderB = orderForPatient($patientB);
    ExamResult::factory()->released()->create(['order_id' => $orderB->id, 'patient_id' => $patientB->id]);

    $response = $this->withToken(patientToken($patientA))->getJson('/patient/results');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
});

test('patient cannot download another patients result even with a known id', function () {
    Storage::fake('local');

    $patientA = Patient::factory()->create();
    $patientB = Patient::factory()->create();
    $orderB = orderForPatient($patientB);
    $resultB = ExamResult::factory()->released()->create(['order_id' => $orderB->id, 'patient_id' => $patientB->id]);

    $response = $this->withToken(patientToken($patientA))->getJson("/patient/results/{$resultB->id}/download");

    $response->assertNotFound();
});

test('unreleased result is not downloadable by its own patient', function () {
    Storage::fake('local');

    $patient = Patient::factory()->create();
    $order = orderForPatient($patient);
    $result = ExamResult::factory()->create(['order_id' => $order->id, 'patient_id' => $patient->id]);

    $response = $this->withToken(patientToken($patient))->getJson("/patient/results/{$result->id}/download");

    $response->assertNotFound();
});

test('download of an owned released result succeeds and is audited', function () {
    Storage::fake('local');

    $patient = Patient::factory()->create();
    $order = orderForPatient($patient);
    $result = ExamResult::factory()->released()->create([
        'order_id' => $order->id,
        'patient_id' => $patient->id,
        'file_disk' => 'local',
    ]);

    $response = $this->withToken(patientToken($patient))->getJson("/patient/results/{$result->id}/download");

    $response->assertOk()->assertJsonStructure(['data' => ['url']]);

    expect(ExamResultAccessLog::where('exam_result_id', $result->id)->where('action', 'downloaded')->exists())->toBeTrue();
});
