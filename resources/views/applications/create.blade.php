<x-app-layout>

@php
    $field = 'block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-[oklch(45%_0.15_151.711)] focus:ring-[oklch(45%_0.15_151.711)] disabled:bg-gray-100 disabled:text-gray-400';
    $label = 'block text-sm font-medium text-gray-700 mb-1';
@endphp

<div class="max-w-3xl mx-auto">

<div class="mb-8">
<a href="{{ route('applications.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
<svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
Back to Applications
</a>
<h1 class="mt-3 text-2xl font-bold text-gray-900">Register New Application</h1>
<p class="mt-1 text-sm text-gray-500">Fill out the applicant's details below.</p>
</div>

@if ($errors->any())
<div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
<p class="text-sm font-medium text-red-800">Please fix the following:</p>
<ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<form method="POST" action="{{ route('applications.store') }}" id="application-form" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
@csrf

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

<div class="sm:col-span-2">
<label for="full_name" class="{{ $label }}">Full Name</label>
<input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required class="{{ $field }}" placeholder="Juan Dela Cruz">
</div>

<div>
<label for="birth_date" class="{{ $label }}">Birth Date</label>
<input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}" required class="{{ $field }}">
<p class="mt-1 text-xs text-gray-400">Age is computed automatically from this date.</p>
</div>

<div>
<label for="gender" class="{{ $label }}">Gender</label>
<select name="gender" id="gender" required class="{{ $field }}">
<option value="">Select gender</option>
<option value="male" @selected(old('gender') === 'male')>Male</option>
<option value="female" @selected(old('gender') === 'female')>Female</option>
</select>
</div>

{{-- PSGC cascading address selects. Each visible <select> carries the
     official PSGC code as its value; a hidden input alongside it mirrors
     the selected option's display name (kept in sync by the script below)
     so both the code and the readable name are saved. --}}

<div>
<label for="region_code" class="{{ $label }}">Region</label>
<select id="region_code" name="region_code" required class="{{ $field }}" data-placeholder="Loading regions…">
<option value="">Loading regions…</option>
</select>
<input type="hidden" name="region" id="region_name">
</div>

<div id="province-wrapper">
<label for="province_code" class="{{ $label }}">Province</label>
<select id="province_code" name="province_code" class="{{ $field }}" disabled data-placeholder="Select a region first">
<option value="">Select a region first</option>
</select>
<input type="hidden" name="province" id="province_name">
</div>

<div>
<label for="municipality_code" class="{{ $label }}">City / Municipality</label>
<select id="municipality_code" name="municipality_code" required class="{{ $field }}" disabled data-placeholder="Select a province first">
<option value="">Select a province first</option>
</select>
<input type="hidden" name="municipality" id="municipality_name">
</div>

<div>
<label for="barangay_code" class="{{ $label }}">Barangay</label>
<select id="barangay_code" name="barangay_code" required class="{{ $field }}" disabled data-placeholder="Select a city/municipality first">
<option value="">Select a city/municipality first</option>
</select>
<input type="hidden" name="barangay" id="barangay_name">
</div>

<div class="sm:col-span-2">
<label for="address" class="{{ $label }}">House No. / Street (optional)</label>
<input type="text" name="address" id="address" value="{{ old('address') }}" class="{{ $field }}" placeholder="123 Rizal St.">
</div>

<div>
<label for="contact_number" class="{{ $label }}">Contact Number</label>
<input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number') }}" class="{{ $field }}" placeholder="09XXXXXXXXX">
</div>

<div>
<label for="status" class="{{ $label }}">Status</label>
<select name="status" id="status" required class="{{ $field }}">
<option value="pending" @selected(old('status', 'pending') === 'pending')>Pending</option>
<option value="approved" @selected(old('status') === 'approved')>Approved</option>
<option value="rejected" @selected(old('status') === 'rejected')>Rejected</option>
</select>
<p class="mt-1 text-xs text-gray-400">Setting this to "Approved" adds the applicant directly to the Senior Citizens list.</p>
</div>

</div>

<div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
<a href="{{ route('applications.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Cancel</a>
<button type="submit" class="rounded-xl bg-[oklch(45%_0.15_151.711)] px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[oklch(38%_0.15_151.711)]">Save Application</button>
</div>

</form>

</div>

@once
<script>
/**
 * Cascading PSGC address selects.
 *
 * Assumes each PsgcController endpoint returns a JSON array of objects
 * shaped like { code: "...", name: "..." }. If your controller returns
 * different keys (e.g. "psgc_code" or "description"), adjust the `code`
 * and `name` property names in the four render() calls below.
 */
document.addEventListener('DOMContentLoaded', function () {
    const routes = {
        regions:         '{{ route('psgc.regions') }}',
        provinces:       (regionCode) => '{{ url('/psgc/regions') }}/' + regionCode + '/provinces',
        citiesInRegion:  (regionCode) => '{{ url('/psgc/regions') }}/' + regionCode + '/cities',
        cities:          (provinceCode) => '{{ url('/psgc/provinces') }}/' + provinceCode + '/cities',
        barangays:       (cityCode) => '{{ url('/psgc/cities') }}/' + cityCode + '/barangays',
    };

    const regionSelect       = document.getElementById('region_code');
    const provinceWrapper    = document.getElementById('province-wrapper');
    const provinceSelect     = document.getElementById('province_code');
    const municipalitySelect = document.getElementById('municipality_code');
    const barangaySelect     = document.getElementById('barangay_code');

    const regionName       = document.getElementById('region_name');
    const provinceName     = document.getElementById('province_name');
    const municipalityName = document.getElementById('municipality_name');
    const barangayName     = document.getElementById('barangay_name');

    function resetSelect(select, placeholder, disabled = true) {
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        select.disabled = disabled;
    }

    function render(select, items, placeholder) {
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        items.forEach(function (item) {
            const opt = document.createElement('option');
            opt.value = item.code;
            opt.textContent = item.name;
            select.appendChild(opt);
        });
        select.disabled = items.length === 0;
    }

    function syncHiddenName(select, hiddenInput) {
        const opt = select.options[select.selectedIndex];
        hiddenInput.value = (opt && opt.value) ? opt.textContent : '';
    }

    // 1) Load regions on page load.
    fetch(routes.regions)
        .then((r) => r.json())
        .then((regions) => render(regionSelect, regions, 'Select a region'))
        .catch(() => resetSelect(regionSelect, 'Could not load regions', true));

    // 2) Region -> Province (or straight to cities if the region has none, e.g. NCR).
    regionSelect.addEventListener('change', function () {
        syncHiddenName(regionSelect, regionName);
        resetSelect(provinceSelect, 'Loading…');
        resetSelect(municipalitySelect, 'Select a province first');
        resetSelect(barangaySelect, 'Select a city/municipality first');
        municipalityName.value = '';
        barangayName.value = '';
        provinceName.value = '';

        if (!regionSelect.value) {
            resetSelect(provinceSelect, 'Select a region first');
            provinceWrapper.classList.remove('hidden');
            return;
        }

        fetch(routes.provinces(regionSelect.value))
            .then((r) => r.json())
            .then((provinces) => {
                if (provinces.length === 0) {
                    // No provinces under this region (e.g. NCR) — go straight
                    // to that region's cities/municipalities instead.
                    provinceWrapper.classList.add('hidden');
                    provinceSelect.removeAttribute('required');
                    fetch(routes.citiesInRegion(regionSelect.value))
                        .then((r) => r.json())
                        .then((cities) => render(municipalitySelect, cities, 'Select a city/municipality'))
                        .catch(() => resetSelect(municipalitySelect, 'Could not load cities', true));
                } else {
                    provinceWrapper.classList.remove('hidden');
                    provinceSelect.setAttribute('required', 'required');
                    render(provinceSelect, provinces, 'Select a province');
                }
            })
            .catch(() => resetSelect(provinceSelect, 'Could not load provinces', true));
    });

    // 3) Province -> City/Municipality.
    provinceSelect.addEventListener('change', function () {
        syncHiddenName(provinceSelect, provinceName);
        resetSelect(municipalitySelect, 'Loading…');
        resetSelect(barangaySelect, 'Select a city/municipality first');
        barangayName.value = '';

        if (!provinceSelect.value) {
            resetSelect(municipalitySelect, 'Select a province first');
            return;
        }

        fetch(routes.cities(provinceSelect.value))
            .then((r) => r.json())
            .then((cities) => render(municipalitySelect, cities, 'Select a city/municipality'))
            .catch(() => resetSelect(municipalitySelect, 'Could not load cities', true));
    });

    // 4) City/Municipality -> Barangay.
    municipalitySelect.addEventListener('change', function () {
        syncHiddenName(municipalitySelect, municipalityName);
        resetSelect(barangaySelect, 'Loading…');

        if (!municipalitySelect.value) {
            resetSelect(barangaySelect, 'Select a city/municipality first');
            return;
        }

        fetch(routes.barangays(municipalitySelect.value))
            .then((r) => r.json())
            .then((barangays) => render(barangaySelect, barangays, 'Select a barangay'))
            .catch(() => resetSelect(barangaySelect, 'Could not load barangays', true));
    });

    barangaySelect.addEventListener('change', function () {
        syncHiddenName(barangaySelect, barangayName);
    });
});
</script>
@endonce

</x-app-layout>