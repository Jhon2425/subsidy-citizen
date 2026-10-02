import './bootstrap';
import '../css/app.css';

// Only pull in the React + Redux bundle on pages that render it, so every
// other page keeps loading just Alpine.js.
if (document.getElementById('dashboard-root')) {
    import('./dashboard/index.jsx');
}

document.addEventListener('alpine:init', () => {
    Alpine.data('psgcAddress', () => ({

        regions: [],
        provinces: [],
        cities: [],
        barangays: [],

        regionCode: '',
        provinceCode: '',
        cityCode: '',
        barangayCode: '',

        skipProvince: false,
        error: null,

        loading: {
            regions: false,
            provinces: false,
            cities: false,
            barangays: false,
        },

        async init() {
            // Restored values after a failed submit, passed via data attributes.
            this.regionCode = this.$el.dataset.region || '';
            this.provinceCode = this.$el.dataset.province || '';
            this.cityCode = this.$el.dataset.city || '';
            this.barangayCode = this.$el.dataset.barangay || '';

            await this.load('regions', '/psgc/regions', 'regions');

            if (! this.regionCode) return;

            await this.loadProvinces();

            if (this.provinceCode) {
                await this.load('cities', `/psgc/provinces/${this.provinceCode}/cities`, 'cities');
            }

            if (this.cityCode) {
                await this.load('barangays', `/psgc/cities/${this.cityCode}/barangays`, 'barangays');
            }
        },

        async load(key, url, target) {
            this.loading[key] = true;
            this.error = null;

            try {
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                });

                if (! response.ok) throw new Error('Lookup failed');

                this[target] = await response.json();

                if (this[target].length === 0) {
                    this.error = 'Address lookup returned no results.';
                }
            } catch (e) {
                this.error = 'Could not load address data. Please try again.';
                this[target] = [];
            } finally {
                this.loading[key] = false;
            }
        },

        async loadProvinces() {
            this.loading.provinces = true;
            this.error = null;

            try {
                const response = await fetch(`/psgc/regions/${this.regionCode}/provinces`, {
                    headers: { 'Accept': 'application/json' },
                });

                const data = await response.json();

                this.provinces = data.provinces;
                this.skipProvince = data.skip_province;

                // NCR has districts, not provinces.
                if (this.skipProvince) {
                    await this.load('cities', `/psgc/regions/${this.regionCode}/cities`, 'cities');
                }
            } catch (e) {
                this.error = 'Could not load provinces.';
                this.provinces = [];
            } finally {
                this.loading.provinces = false;
            }
        },

        async onRegionChange() {
            this.provinceCode = '';
            this.cityCode = '';
            this.barangayCode = '';
            this.provinces = [];
            this.cities = [];
            this.barangays = [];
            this.skipProvince = false;

            if (! this.regionCode) return;

            await this.loadProvinces();
        },

        async onProvinceChange() {
            this.cityCode = '';
            this.barangayCode = '';
            this.cities = [];
            this.barangays = [];

            if (! this.provinceCode) return;

            await this.load('cities', `/psgc/provinces/${this.provinceCode}/cities`, 'cities');
        },

        async onCityChange() {
            this.barangayCode = '';
            this.barangays = [];

            if (! this.cityCode) return;

            await this.load('barangays', `/psgc/cities/${this.cityCode}/barangays`, 'barangays');
        },

        nameOf(list, code) {
            const match = list.find(i => i.code === code);
            return match ? match.name : '';
        },

    }));
});