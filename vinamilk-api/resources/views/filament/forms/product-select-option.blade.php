@php
    $imagePath = $image ?? $product->main_image ?? null;
    $imageUrl = $imagePath
        ? asset('storage/' . $imagePath)
        : null;
@endphp
<div class="flex items-center gap-3 py-1">
    @if ($imageUrl)
        <img src="{{ $imageUrl }}" alt="" class="w-10 h-10 rounded-lg object-cover shrink-0 border border-gray-200" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
        <div class="w-10 h-10 rounded-lg bg-gray-100 shrink-0 hidden"></div>
    @else
        <div class="w-10 h-10 rounded-lg bg-gray-100 shrink-0"></div>
    @endif
    <span class="text-sm font-medium truncate">{{ $product->name }}</span>
</div>
