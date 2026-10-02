<?php

namespace App\Services;

use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BatchQrCodeService
{
    public function generate(Batch $batch): string
    {
        $disk = Storage::disk('public');
        $format = extension_loaded('imagick') ? 'png' : 'svg';
        $path = 'qrcodes/batches/' . $batch->id . '.' . $format;
        $payload = route('admin.batches.traceability', $batch);

        $image = QrCode::format($format)
            ->size(300)
            ->margin(1)
            ->generate($payload);

        if ($batch->qr_code_path) {
            $disk->delete($batch->qr_code_path);
        }

        $disk->put($path, (string) $image);

        DB::table('batches')->where('id', $batch->getKey())->update(['qr_code_path' => $path]);
        $batch->setAttribute('qr_code_path', $path);
        $batch->syncOriginalAttribute('qr_code_path');

        return $path;
    }
}