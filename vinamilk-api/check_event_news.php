<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check event news data
$events = \App\Models\EventNews::all();

echo "Total Event News: " . $events->count() . "\n\n";

foreach ($events as $event) {
    echo "ID: " . $event->id . "\n";
    echo "Title: " . $event->title . "\n";
    echo "Slug: " . $event->slug . "\n";
    echo "Is Published: " . ($event->is_published ? 'Yes' : 'No') . "\n";
    echo "Published At: " . ($event->published_at ? $event->published_at->format('Y-m-d H:i:s') : 'null') . "\n";
    echo "Created At: " . $event->created_at->format('Y-m-d H:i:s') . "\n";
    echo "---\n";
}

// Check published scope
$published = \App\Models\EventNews::published()->get();
echo "\nPublished Event News: " . $published->count() . "\n";
