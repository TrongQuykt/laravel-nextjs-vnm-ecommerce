<div
    x-data="{
        state: $wire.entangle('{{ $getStatePath() }}'),
        lat: $wire.entangle('data.latitude'),
        lng: $wire.entangle('data.longitude'),
        map: null,
        marker: null,
        init() {
            this.map = L.map($refs.map).setView([this.lat || 10.762622, this.lng || 106.660172], this.lat ? 16 : 6);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(this.map);

            if (this.lat && this.lng) {
                this.marker = L.marker([this.lat, this.lng], {draggable: true}).addTo(this.map);
                this.marker.on('dragend', (e) => {
                    const position = e.target.getLatLng();
                    this.lat = position.lat;
                    this.lng = position.lng;
                });
            }

            this.map.on('click', (e) => {
                if (this.marker) {
                    this.marker.setLatLng(e.latlng);
                } else {
                    this.marker = L.marker(e.latlng, {draggable: true}).addTo(this.map);
                    this.marker.on('dragend', (ev) => {
                        const position = ev.target.getLatLng();
                        this.lat = position.lat;
                        this.lng = position.lng;
                    });
                }
                this.lat = e.latlng.lat;
                this.lng = e.latlng.lng;
            });

            $watch('lat', value => {
                if (value && this.lng && this.marker) {
                    const newLatLng = new L.LatLng(value, this.lng);
                    this.marker.setLatLng(newLatLng);
                    this.map.panTo(newLatLng);
                }
            });
        }
    }"
    class="w-full"
    wire:ignore
>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <div x-ref="map" style="height: 400px; width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db;" class="mb-2"></div>
    <p class="text-xs text-gray-500">Bấm vào bản đồ hoặc kéo ghim để chọn vị trí chính xác của cửa hàng.</p>
</div>
