@php
    if (!isset($record) || !$record->productVariant) {
        echo '';
        return;
    }
    
    $variant = $record->productVariant;
    $volume = $variant->volume?->name ?? '';
    $packaging = $variant->packagingType?->name ?? '';
    
    $parts = array_filter([$volume, $packaging]);
    $display = !empty($parts) ? implode(' - ', $parts) : $variant->name;
@endphp

{{ $display }}
