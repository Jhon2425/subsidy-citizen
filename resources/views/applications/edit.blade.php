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
<h1 class="mt-3 text-2xl font-bold text-gray-900">Edit Application</h1>
<p class="mt-1 text-sm text-gray-500">Update {{ $application->full_name }}'s details.</p>
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

<form method="POST" action="{{ route('applications.update', $application) }}" id="application-form"
      onsubmit="return this.status.value === 'approved'
          ? confirm('Approve {{ $application->full_name }}? They will be added to the Senior Citizens master list.')
          : true;"
      class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
@csrf
@method('PUT')

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

<div class="sm:col-span-2">
<label for="full_name" class="{{ $label }}">Full Name</label>
<input type="text" name="full_name" id="full_name" value="{{ old('full_name', $application->full_name) }}" required class="{{ $field }}">
</div>

<div>
<label for="birth_date" class="{{ $label }}">Birth Date</label>
<input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date', $application->birth_date->format('Y-m-d')) }}" required class="{{ $field }}">
<p class="mt-1 text-xs text-gray-400">Age is computed automatically from this date.</p>
</div>

<div>
<label for="gender" class="{{ $label }}">Gender</label>
<select name="gender" id="gender" required class="{{ $field }}">
<option value="male" @selected(old('gender', $application->gender) === 'male')>Male</option>
<option value="female" @selected(old('gender', $application->gender) === 'female')>Female</option>
</select>
</div>

<div>
<label for="region_code" class="{{ $label }}">Region</label>
<select id="region_code" name="region_code" required class="{{ $field }}">
<option value="">Loading regions…</option>
</select>
<input type="hidden" name="region" id="region_name" value="{{ old('region', $application->region) }}">
</div>

<div id="province-wrapper">
<label for="province_code" class="{{ $label }}">Province</label>
<select id="province_code" name="province_code" class="{{ $field }}" disabled>
<option value="">Select a region first</option>
</select>
<input type="hidden" name="province" id="province_name" value="{{ old('province', $application->province) }}">
</div>

<div>
<label for="municipality_code" class="{{ $label }}">City / Municipality</label>
<select id="municipality_code" name="municipality_code" required class="{{ $field }}" disabled>
<option value="">Select a province first</option>
</select>
<input type="hidden" name="municipality" id="municipality_name" value="{{ old('municipality', $application->municipality) }}">
</div>

<div>
<label for="barangay_code" class="{{ $label }}">Barangay</label>
<select id="barangay_code" name="barangay_code" required class="{{ $field }}" disabled>
<option value="">Select a city/municipality first</option>
</select>
<input type="hidden" name="barangay" id="barangay_name" value="{{ old('barangay', $application->barangay) }}">
</div>

<div class="sm:col-span-2">
<label for="address" class="{{ $label }}">House No. / Street (optional)</label>
<input type="text" name="address" id="address" value="{{ old('address', $application->address) }}" class="{{ $field }}">
</div>

<div>
<label for="contact_number" class="{{ $label }}">Contact Number</label>
<input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number', $application->contact_number) }}" class="{{ $field }}">
</div>

<div>
<label for="status" class="{{ $label }}">Status</label>
<select name="status" id="status" required class="{{ $field }}">
<option value="pending" @selected(old('status', $application->status) === 'pending')>Pending</option>
<option value="approved" @selected(old('status', $application->status) === 'approved')>Approved</option>
<option value="rejected" @selected(old('status', $application->status) === 'rejected')>Rejected</option>
</select>
<p class="mt-1 text-xs text-gray-400">Setting this to "Approved" adds the applicant directly to the Senior Citizens list.</p>
</div>

</div>

<div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
<a href="{{ route('applications.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Cancel</a>
<button type="submit" class="rounded-xl bg-[oklch(45%_0.15_151.711)] px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[oklch(38%_0.15_151.711)]">Save Changes</button>
</div>

</form>

</div>

<script>
/**
 * Same cascading PSGC logic as the create form, but pre-populates each
 * level from the application's existing *_code values before wiring up
 * the change listeners, so editing doesn't force a re-pick from scratch.
 *
 * Same assumption as create.blade.php: each endpoint returns
 * [{ code, name }, ...]. Adjust property names below if yours differ.
 */
document.addEventListener('DOMContentLoaded', function () {
    const existing = {
        regionCode:       @json(old('region_code', $application->region_code)),
        provinceCode:     @json(old('province_code', $application->province_code)),
        municipalityCode: @json(old('municipality_code', $application->municipality_code)),
        barangayCode:     @json(old('barangay_code', $application->barangay_code)),
    };

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

    function render(select, items, placeholder, selectedCode = null) {
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        items.forEach(function (item) {
            const opt = document.createElement('option');
            opt.value = item.code;
            opt.textContent = item.name;
            if (selectedCode && String(item.code) === String(selectedCode)) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });
        select.disabled = items.length === 0;
    }

    function syncHiddenName(select, hiddenInput) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) hiddenInput.value = opt.textContent;
    }

    async function fetchJson(url) {
        const res = await fetch(url);
        return res.json();
    }

    async function init() {
        // 1) Regions, pre-select the existing one.
        const regions = await fetchJson(routes.regions).catch(() => []);
        render(regionSelect, regions, 'Select a region', existing.regionCode);

        if (!existing.regionCode) return;

        // 2) Provinces for that region (or fall back to cities-in-region).
        const provinces = await fetchJson(routes.provinces(existing.regionCode)).catch(() => []);

        if (provinces.length === 0) {
            provinceWrapper.classList.add('hidden');
            provinceSelect.removeAttribute('required');

            const cities = await fetchJson(routes.citiesInRegion(existing.regionCode)).catch(() => []);
            render(municipalitySelect, cities, 'Select a city/municipality', existing.municipalityCode);
        } else {
            render(provinceSelect, provinces, 'Select a province', existing.provinceCode);

            if (existing.provinceCode) {
                const cities = await fetchJson(routes.cities(existing.provinceCode)).catch(() => []);
                render(municipalitySelect, cities, 'Select a city/municipality', existing.municipalityCode);
            }
        }

        // 3) Barangays for the selected city/municipality.
        if (existing.municipalityCode) {
            const barangays = await fetchJson(routes.barangays(existing.municipalityCode)).catch(() => []);
            render(barangaySelect, barangays, 'Select a barangay', existing.barangayCode);
        }
    }

    init();

    // Same cascading change listeners as the create form.
    regionSelect.addEventListener('change', function () {
        syncHiddenName(regionSelect, regionName);
        provinceSelect.innerHTML = '<option value="">Loading…</option>';
        municipalitySelect.innerHTML = '<option value="">Select a province first</option>';
        municipalitySelect.disabled = true;
        barangaySelect.innerHTML = '<option value="">Select a city/municipality first</option>';
        barangaySelect.disabled = true;
        municipalityName.value = '';
        barangayName.value = '';
        provinceName.value = '';

        if (!regionSelect.value) return;

        fetchJson(routes.provinces(regionSelect.value)).then((provinces) => {
            if (provinces.length === 0) {
                provinceWrapper.classList.add('hidden');
                provinceSelect.removeAttribute('required');
                fetchJson(routes.citiesInRegion(regionSelect.value))
                    .then((cities) => render(municipalitySelect, cities, 'Select a city/municipality'));
            } else {
                provinceWrapper.classList.remove('hidden');
                provinceSelect.setAttribute('required', 'required');
                render(provinceSelect, provinces, 'Select a province');
            }
        });
    });

    provinceSelect.addEventListener('change', function () {
        syncHiddenName(provinceSelect, provinceName);
        municipalitySelect.innerHTML = '<option value="">Loading…</option>';
        barangaySelect.innerHTML = '<option value="">Select a city/municipality first</option>';
        barangaySelect.disabled = true;
        barangayName.value = '';

        if (!provinceSelect.value) return;

        fetchJson(routes.cities(provinceSelect.value))
            .then((cities) => render(municipalitySelect, cities, 'Select a city/municipality'));
    });

    municipalitySelect.addEventListener('change', function () {
        syncHiddenName(municipalitySelect, municipalityName);
        barangaySelect.innerHTML = '<option value="">Loading…</option>';

        if (!municipalitySelect.value) return;

        fetchJson(routes.barangays(municipalitySelect.value))
            .then((barangays) => render(barangaySelect, barangays, 'Select a barangay'));
    });

    barangaySelect.addEventListener('change', function () {
        syncHiddenName(barangaySelect, barangayName);
    });
});
</script>

</x-app-layout>