<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    
    <div class="mb-4">
        <p class="text-sm text-gray-500">
            Kéo thả các banner để sắp xếp vị trí hiển thị ngoài trang chủ. 
            Rê chuột vào banner và chọn các nút bên dưới để tùy chỉnh nhanh độ rộng/cao (1x1, 1x2, 2x1, 2x2).
        </p>
    </div>

    <div
        x-data="{
            initSortable() {
                new Sortable(this.$refs.grid, {
                    animation: 150,
                    ghostClass: 'opacity-50',
                    onEnd: (evt) => {
                        let items = Array.from(this.$refs.grid.children).map(el => el.dataset.id);
                        $wire.updateOrder(items);
                    }
                });
            }
        }"
        x-init="initSortable"
        x-ref="grid"
        class="grid gap-4 bg-gray-50 dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800"
        style="grid-template-columns: repeat(2, minmax(0, 1fr)); grid-auto-rows: 240px; max-width: 900px; margin: 0 auto; width: 100%;"
    >
        @foreach($banners as $banner)
            @php
                $col = min(max($banner['col_span'] ?? 1, 1), 2);
                $row = min(max($banner['row_span'] ?? 1, 1), 2);
            @endphp
            <div 
                data-id="{{ $banner['id'] }}"
                class="relative rounded-2xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700 group cursor-move bg-white dark:bg-gray-800 flex items-center justify-center transition-all"
                style="grid-column: span {{ $col }}; grid-row: span {{ $row }};"
            >
                <img src="{{ Storage::url($banner['image_path']) }}" alt="" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-60" />
                
                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex flex-col items-center justify-center gap-4">
                    
                    <div class="text-center">
                        <span class="text-white font-bold text-sm bg-black/50 px-3 py-1 rounded-full border border-white/20">
                            {{ $col }} cột x {{ $row }} hàng
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 p-2 bg-white/10 backdrop-blur-md rounded-xl border border-white/20">
                        <button wire:click="updateSize({{ $banner['id'] }}, 1, 1)" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-colors {{ $col==1 && $row==1 ? 'bg-primary-600 text-white' : 'bg-white text-gray-800 hover:bg-gray-200' }}">Vuông nhỏ (1x1)</button>
                        <button wire:click="updateSize({{ $banner['id'] }}, 2, 1)" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-colors {{ $col==2 && $row==1 ? 'bg-primary-600 text-white' : 'bg-white text-gray-800 hover:bg-gray-200' }}">Ngang rộng (2x1)</button>
                        <button wire:click="updateSize({{ $banner['id'] }}, 1, 2)" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-colors {{ $col==1 && $row==2 ? 'bg-primary-600 text-white' : 'bg-white text-gray-800 hover:bg-gray-200' }}">Dọc cao (1x2)</button>
                        <button wire:click="updateSize({{ $banner['id'] }}, 2, 2)" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-colors {{ $col==2 && $row==2 ? 'bg-primary-600 text-white' : 'bg-white text-gray-800 hover:bg-gray-200' }}">Vuông to (2x2)</button>
                    </div>

                </div>
                
                @if(!$banner['is_active'])
                    <div class="absolute top-3 left-3 bg-red-500/90 backdrop-blur-sm text-white text-xs font-bold px-2 py-1 rounded">Đang Ẩn</div>
                @endif

                <div class="absolute top-3 right-3 bg-black/40 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-1 rounded">
                    #{{ $banner['sort_order'] }}
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
