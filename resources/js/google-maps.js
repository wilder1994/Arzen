export function whenGoogleMapsReady() {
    if (window.google?.maps?.Map) {
        return Promise.resolve(window.google.maps);
    }

    if (!window.arzenGoogleMapsConfigured) {
        return Promise.reject(new Error('missing-google-maps-key'));
    }

    return new Promise((resolve, reject) => {
        const timer = window.setTimeout(() => reject(new Error('google-maps-timeout')), 15000);
        window.addEventListener('arzen-google-maps-ready', () => {
            window.clearTimeout(timer);
            if (window.google?.maps?.Map) {
                resolve(window.google.maps);
                return;
            }
            reject(new Error('google-maps-unavailable'));
        }, { once: true });
    });
}

export function locationMarkerIcon() {
    return {
        url: '/images/map/Icono_Ubicacion.png',
        scaledSize: new google.maps.Size(36, 52),
        anchor: new google.maps.Point(18, 52),
    };
}

export function addressComponent(components, type) {
    const match = (components || []).find((item) => item.types?.includes(type));

    return match?.long_name || '';
}
