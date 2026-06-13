<?php

namespace App\Models;

use Database\Factories\OrderExamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'exam_id', 'exam_name', 'exam_price'])]
class OrderExam extends Model
{
    /** @use HasFactory<OrderExamFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'exam_price' => 'float',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
