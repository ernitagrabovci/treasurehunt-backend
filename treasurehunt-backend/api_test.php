<?php
// Quick test of the API endpoint
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$levels = App\Models\Level::orderBy('order')->get();
echo "Count: " . $levels->count() . "\n";
foreach ($levels as $l) {
    $title = json_encode($l->title);
    echo "  id={$l->id} title={$title} image_path={$l->image_path} scene_id={$l->scene_id} order={$l->order}\n";
}
