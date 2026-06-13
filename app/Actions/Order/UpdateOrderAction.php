<?php

namespace App\Actions\Order;

use App\Common\Actions\IUpdateAction;
use App\Enum\OrderTypeEnum;
use App\Models\Exam;
use App\Models\Order;
use App\Models\OrderExam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateOrderAction implements IUpdateAction
{
    public function execute(array $data, Model $model): Model
    {
        $hasExams = array_key_exists('exams', $data);
        $examIds = array_unique(Arr::pull($data, 'exams', []));

        return DB::transaction(function () use ($data, $model, $hasExams, $examIds) {
            /** @var Order $order */
            $order = Order::lockForUpdate()->findOrFail($model->id);

            $order->update($data);

            if ($hasExams) {
                $order->orderExams()->delete();

                if (!empty($examIds)) {
                    $this->insertOrderExams($order->fresh(), $examIds);
                }
            }

            return $order->fresh();
        });
    }

    /** @param array<int, string> $examIds */
    private function insertOrderExams(Order $order, array $examIds): void
    {
        $exams = Exam::whereIn('id', $examIds)->get();
        $now = now();

        $rows = $exams->map(fn(Exam $exam) => [
            'id' => (string) Str::ulid(),
            'order_id' => $order->id,
            'exam_id' => $exam->id,
            'exam_name' => $exam->name,
            'exam_price' => $order->type === OrderTypeEnum::Sus ? $exam->price_sus : $exam->price_particular,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        OrderExam::insert($rows);
    }
}
