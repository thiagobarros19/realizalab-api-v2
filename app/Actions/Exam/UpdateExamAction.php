<?php

namespace App\Actions\Exam;

use App\Common\Actions\IUpdateAction;
use Illuminate\Database\Eloquent\Model;

class UpdateExamAction implements IUpdateAction
{
    public function execute(array $data, Model $model): Model
    {
        $model->update($data);

        return $model->fresh();
    }
}
