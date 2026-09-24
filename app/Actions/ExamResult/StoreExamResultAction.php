<?php

namespace App\Actions\ExamResult;

use App\Common\Actions\IStoreAction;
use App\Models\ExamResult;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class StoreExamResultAction implements IStoreAction
{
    public function execute(array $data): Model
    {
        /** @var UploadedFile $file */
        $file = $data['file'];

        $order = Order::findOrFail($data['order_id']);

        $disk = config('filesystems.default') === 's3' ? 's3' : config('filesystems.default');
        $path = $file->store('exam-results/'.$order->patient_id, $disk);

        return ExamResult::create([
            'order_id' => $order->id,
            'patient_id' => $order->patient_id,
            'uploaded_by_user_id' => $data['uploaded_by_user_id'] ?? null,
            'file_disk' => $disk,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/pdf',
            'file_size' => $file->getSize(),
            'file_hash' => hash_file('sha256', $file->getRealPath()) ?: Str::random(64),
        ]);
    }
}
