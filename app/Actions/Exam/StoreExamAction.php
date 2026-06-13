<?php

namespace App\Actions\Exam;

use App\Common\Actions\IStoreAction;
use App\Models\Exam;
use Illuminate\Database\Eloquent\Model;

class StoreExamAction implements IStoreAction
{
    public function execute(array $data): Exam
    {
        return Exam::create($data);
    }
}
