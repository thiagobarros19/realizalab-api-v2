<?php

namespace App\Actions\Order;

use App\Common\Actions\IShowAction;
use Illuminate\Database\Eloquent\Model;

class ShowOrderAction implements IShowAction
{
    public function execute(Model $model): Model
    {
        return $model->load('patient', 'exams', 'orderExams');
    }
}
