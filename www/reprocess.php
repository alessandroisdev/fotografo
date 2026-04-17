<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$photos = App\Models\Photo::all();
foreach($photos as $photo) {
    if (file_exists(storage_path('app/public/' . $photo->watermark_path))) {
        @unlink(storage_path('app/public/' . $photo->watermark_path));
    }
    $photo->update(['status' => 'pending']);
    dispatch(new App\Jobs\ProcessImageJob($photo));
}
echo "Dispatched " . $photos->count() . " jobs for re-processing with Watermark.\n";
