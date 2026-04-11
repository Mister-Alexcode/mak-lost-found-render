@props(['latitude' => null, 'longitude' => null, 'readonly' => false])

<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ $readonly ? 'Location on Map' : 'Pin Location on Map' }}
        @unless($readonly)
            <span class="text-gray-400 font-normal">(click the map to place a pin)</span>
        @endunless
    </label>
    <div id="map-picker" class="w-full h-64 rounded border border-gray-300 z-0"></div>
    @unless($readonly)
        <input type="hidden" name="latitude" id="map-latitude" value="{{ old('latitude', $latitude) }}">
        <input type="hidden" name="longitude" id="map-longitude" value="{{ old('longitude', $longitude) }}">
        <p class="text-xs text-gray-400 mt-1" id="map-coords">
            @if($latitude && $longitude)
                Selected: {{ $latitude }}, {{ $longitude }}
            @else
                No location pinned yet.
            @endif
        </p>
    @endunless
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const defaultLat = 0.3348;
    const defaultLng = 32.5699;
    const initLat = {{ $latitude ?? 'null' }};
    const initLng = {{ $longitude ?? 'null' }};
    const readonly = {{ $readonly ? 'true' : 'false' }};

    const lat = initLat || defaultLat;
    const lng = initLng || defaultLng;
    const zoom = (initLat && initLng) ? 17 : 15;

    const map = L.map('map-picker').setView([lat, lng], zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    let marker = null;

    if (initLat && initLng) {
        marker = L.marker([initLat, initLng]).addTo(map);
    }

    if (!readonly) {
        map.on('click', function (e) {
            const { lat, lng } = e.latlng;

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }

            document.getElementById('map-latitude').value = lat.toFixed(7);
            document.getElementById('map-longitude').value = lng.toFixed(7);
            document.getElementById('map-coords').textContent =
                'Selected: ' + lat.toFixed(5) + ', ' + lng.toFixed(5);
        });
    }
});
</script>
@endpush
