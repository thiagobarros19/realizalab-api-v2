<?php

namespace App\Models;

use App\Enum\FinancialCategoryEnum;
use App\Enum\FinancialTypeEnum;
use App\Enum\PaymentMethodEnum;
use Database\Factories\FinancialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['amount', 'paid_at', 'type', 'category', 'payment_method', 'description', 'financialable_id', 'financialable_type'])]
class Financial extends Model
{
    /** @use HasFactory<FinancialFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'paid_at' => 'date',
            'type' => FinancialTypeEnum::class,
            'category' => FinancialCategoryEnum::class,
            'payment_method' => PaymentMethodEnum::class,
        ];
    }

    public function financialable(): MorphTo
    {
        return $this->morphTo();
    }
}
