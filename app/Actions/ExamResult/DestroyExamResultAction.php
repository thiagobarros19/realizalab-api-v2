<?php

namespace App\Actions\ExamResult;

use App\Common\Actions\IDestroyAction;
use Illuminate\Database\Eloquent\Model;

class DestroyExamResultAction implements IDestroyAction
{
    public function execute(Model $model): void
    {
        $model->delete();
    }
}
