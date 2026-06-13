<?php

namespace App\Actions\Exam;

use App\Common\Actions\IDestroyAction;
use Illuminate\Database\Eloquent\Model;

class DestroyExamAction implements IDestroyAction
{
    public function execute(Model $model): void
    {
        $model->delete();
    }
}
