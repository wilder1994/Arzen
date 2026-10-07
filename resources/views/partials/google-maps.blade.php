@once
    @php($googleMapsKey = trim((string) config('services.google.maps_key')))
    <script>
        window.arzenGoogleMapsConfigured = @json($googleMapsKey !== '');
        window.arzenGoogleMapsReady = function () {
            window.arzenGoogleMapsLoaded = true;
            window.dispatchEvent(new Event('arzen-google-maps-ready'));
        };
    </script>
    @if ($googleMapsKey !== '')
        <script async src="https://maps.googleapis.com/maps/api/js?key={{ urlencode($googleMapsKey) }}&language={{ str_starts_with(app()->getLocale(), 'en') ? 'en' : 'es' }}&region=CO&v=weekly&callback=arzenGoogleMapsReady"></script>
    @endif
@endonce
