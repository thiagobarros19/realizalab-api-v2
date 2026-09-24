<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientExamResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_names' => $this->order?->orderExams->pluck('exam_name')->values(),
            'original_filename' => $this->original_filename,
            'released_at' => $this->released_at,
        ];
    }
}
