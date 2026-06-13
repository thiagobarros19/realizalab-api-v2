<?php

namespace App\Actions\Exam;

use App\Common\Actions\IShowAction;
use Illuminate\Database\Eloquent\Model;

class ShowExamAction implements IShowAction
{
    public function execute(Model $model): Model
    {
        return $model;
    }
}
