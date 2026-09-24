<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'patient_id' => $this->patient_id,
            'exam_names' => $this->order?->orderExams->pluck('exam_name')->values(),
            'original_filename' => $this->original_filename,
            'file_size' => $this->file_size,
            'uploaded_by' => $this->uploadedBy?->name,
            'released_at' => $this->released_at,
            'created_at' => $this->created_at,
        ];
    }
}
