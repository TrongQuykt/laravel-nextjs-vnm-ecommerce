<?php
require 'vendor/autoload.php';

use Filament\Resources\Resource;

$reflection = new ReflectionClass(Resource::class);

$properties = ['model', 'navigationIcon', 'navigationGroup', 'navigationLabel', 'slug'];

foreach ($properties as $prop) {
    if ($reflection->hasProperty($prop)) {
        $p = $reflection->getProperty($prop);
        echo "Property: $prop\n";
        echo "Type: " . $p->getType() . "\n";
        echo "-------------------\n";
    }
}
