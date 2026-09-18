<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @once
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css"
            integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
        >
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"
            integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
        ></script>
    @endonce

    <div
        wire:ignore
        x-data="kdbMapPicker({
            statePath: @js($getStatePath()),
            initial: $wire.$get(@js($getStatePath())),
            defaultLat: @js($getDefaultLatitude()),
            defaultLng: @js($getDefaultLongitude()),
            defaultZoom: @js($getDefaultZoom()),
        })"
        x-init="boot()"
        {{ $getExtraAttributeBag() }}
    >
        <div class="flex flex-col gap-2 sm:flex-row">
            <input
                type="text"
                x-model="query"
                x-on:keydown.enter.prevent="search()"
                placeholder="Adres veya yer adı arayın (ör. Şişli, İstanbul)"
                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-950 shadow-sm outline-none transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/20 dark:bg-white/5 dark:text-white"
            >
            <x-filament::button type="button" x-on:click="search()" x-bind:disabled="searching">
                <span x-show="! searching">Ara</span>
                <span x-show="searching" x-cloak>Aranıyor…</span>
            </x-filament::button>
        </div>

        <p x-show="error" x-cloak x-text="error" class="mt-2 text-sm text-danger-600 dark:text-danger-400"></p>

        <div
            x-ref="map"
            style="height: {{ $getHeight() }}px"
            class="mt-3 w-full overflow-hidden rounded-xl border border-gray-300 dark:border-white/20"
        ></div>

        <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-sm">
            <p class="text-gray-500 dark:text-gray-400">
                Haritaya tıklayarak veya işaretçiyi sürükleyerek konumu belirleyin.
            </p>
            <div class="flex items-center gap-3">
                <span x-show="hasLocation" x-cloak class="font-medium text-gray-950 dark:text-white" x-text="label"></span>
                <span x-show="! hasLocation" x-cloak class="text-gray-500 dark:text-gray-400">Konum seçilmedi</span>
                <button
                    type="button"
                    x-show="hasLocation"
                    x-cloak
                    x-on:click="clear()"
                    class="text-danger-600 underline-offset-2 hover:underline dark:text-danger-400"
                >
                    Temizle
                </button>
            </div>
        </div>
    </div>

    @once
        <script>
            function kdbMapPicker(config) {
                return {
                    map: null,
                    marker: null,
                    query: '',
                    error: '',
                    searching: false,
                    lat: null,
                    lng: null,

                    get hasLocation() {
                        return this.lat !== null && this.lng !== null;
                    },

                    get label() {
                        return this.hasLocation
                            ? `${Number(this.lat).toFixed(6)}, ${Number(this.lng).toFixed(6)}`
                            : '';
                    },

                    boot() {
                        // Leaflet betiği CDN'den geç yüklenebilir; hazır olana kadar beklenir.
                        if (typeof L === 'undefined') {
                            setTimeout(() => this.boot(), 100);

                            return;
                        }

                        const initial = config.initial || {};
                        const hasInitial = initial.lat != null && initial.lng != null;

                        this.lat = hasInitial ? Number(initial.lat) : null;
                        this.lng = hasInitial ? Number(initial.lng) : null;

                        this.map = L.map(this.$refs.map).setView(
                            hasInitial ? [this.lat, this.lng] : [config.defaultLat, config.defaultLng],
                            hasInitial ? 16 : config.defaultZoom,
                        );

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap katkıda bulunanları',
                        }).addTo(this.map);

                        if (hasInitial) {
                            this.placeMarker(this.lat, this.lng, false);
                        }

                        this.map.on('click', (event) => {
                            this.placeMarker(event.latlng.lat, event.latlng.lng);
                        });

                        // Sekme/bölüm içinde gizliyken açıldığında harita boyutu bozulmasın.
                        setTimeout(() => this.map.invalidateSize(), 250);
                    },

                    placeMarker(lat, lng, updateState = true) {
                        this.lat = lat;
                        this.lng = lng;

                        if (this.marker) {
                            this.marker.setLatLng([lat, lng]);
                        } else {
                            this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                            this.marker.on('dragend', () => {
                                const position = this.marker.getLatLng();
                                this.placeMarker(position.lat, position.lng);
                            });
                        }

                        if (updateState) {
                            this.sync();
                        }
                    },

                    sync() {
                        this.$wire.set(
                            config.statePath,
                            this.hasLocation
                                ? { lat: Number(Number(this.lat).toFixed(7)), lng: Number(Number(this.lng).toFixed(7)) }
                                : null,
                            false,
                        );
                    },

                    clear() {
                        if (this.marker) {
                            this.map.removeLayer(this.marker);
                            this.marker = null;
                        }

                        this.lat = null;
                        this.lng = null;
                        this.sync();
                    },

                    async search() {
                        const term = this.query.trim();

                        if (term === '') {
                            return;
                        }

                        this.searching = true;
                        this.error = '';

                        try {
                            const response = await fetch(
                                'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' +
                                    encodeURIComponent(term),
                                { headers: { 'Accept-Language': 'tr' } },
                            );

                            if (! response.ok) {
                                throw new Error('arama başarısız');
                            }

                            const results = await response.json();

                            if (! results.length) {
                                this.error = 'Bu arama için sonuç bulunamadı. Haritadan elle de seçebilirsiniz.';

                                return;
                            }

                            const found = results[0];
                            this.map.setView([Number(found.lat), Number(found.lon)], 17);
                            this.placeMarker(Number(found.lat), Number(found.lon));
                        } catch (exception) {
                            this.error = 'Adres araması şu anda yapılamadı. Haritadan elle seçebilirsiniz.';
                        } finally {
                            this.searching = false;
                        }
                    },
                };
            }
        </script>
    @endonce
</x-dynamic-component>
