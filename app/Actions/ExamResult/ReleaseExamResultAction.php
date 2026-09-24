<?php

namespace App\Actions\ExamResult;

use App\Models\ExamResult;

class ReleaseExamResultAction
{
    public function execute(ExamResult $examResult): ExamResult
    {
        $examResult->update(['released_at' => now()]);

        return $examResult->fresh();
    }
}
