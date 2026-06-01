<div class="flex items-center gap-3">
    @if($image)
        <img src="{{ asset('storage/' . $image) }}" alt="{{ $product_name }}" class="w-10 h-10 rounded object-cover" onerror="this.src='https://via.placeholder.com/40'">
    @else
        <div class="w-10 h-10 rounded bg-gray-200 flex items-center justify-center">
            <span class="text-gray-500 text-xs">No img</span>
        </div>
    @endif
    
    <div class="flex-1">
        <div class="font-medium text-sm">{{ $product_name }}</div>
        <div class="text-xs text-gray-500">
            {{ $variant_name }}
            @if($flavor) <span class="text-gray-400">• {{ $flavor }}</span> @endif
            @if($volume) <span class="text-gray-400">• {{ $volume }}</span> @endif
        </div>
    </div>
</div>
