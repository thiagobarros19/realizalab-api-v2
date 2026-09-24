<?php

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'document', 'email', 'phone', 'birthday', 'observations'])]
class Patient extends Model implements AuthenticatableContract
{
    /** @use HasFactory<PatientFactory> */
    use Authenticatable, HasApiTokens, HasFactory, HasUlids, SoftDeletes;

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }
}
