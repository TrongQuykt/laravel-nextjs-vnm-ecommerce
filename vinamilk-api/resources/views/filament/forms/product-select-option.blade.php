@php
    $image = $product->main_image
        ? asset('storage/' . $product->main_image)
        : null;
@endphp
<div class="flex items-center gap-3 py-1">
    @if ($image)
        <img src="{{ $image }}" alt="" class="w-10 h-10 rounded-lg object-cover shrink-0 border border-gray-200" />
    @else
        <div class="w-10 h-10 rounded-lg bg-gray-100 shrink-0"></div>
    @endif
    <span class="text-sm font-medium truncate">{{ $product->name }}</span>
</div>
