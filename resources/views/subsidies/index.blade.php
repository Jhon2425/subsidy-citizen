<x-app-layout>

@php
    $field = 'block rounded-xl border-gray-300 text-sm shadow-sm focus:border-[oklch(45%_0.15_151.711)] focus:ring-[oklch(45%_0.15_151.711)] disabled:bg-gray-100 disabled:text-gray-400';
    $label = 'block text-sm font-medium text-gray-700';
    $subsidyErrors = $errors->getBag('subsidy');
@endphp

<div x-data="{
    showModal: {{ ($errors->any() && ! $subsidyErrors->any()) ? 'true' : 'false' }},
    target: { name: '', url: '' },
    releaseForm: {
        subsidy_id: @js(old('subsidy_id', '')),
        subsidy_name: '',
        frequency: '',
        amount: @js(old('amount', '')),
        options: [],
    },
    pickSubsidy() {
        const s = this.releaseForm.options.find(o => String(o.id) === String(this.releaseForm.subsidy_id));
        if (s) { this.releaseForm.amount = s.amount; }
    },
    openRelease(el) {
        this.target = { name: el.dataset.name, url: el.dataset.url };
        this.releaseForm = {
            subsidy_id: el.dataset.subsidyId,
            subsidy_name: el.dataset.subsidyName,
            frequency: el.dataset.frequency,
            amount: el.dataset.amount,
            options: JSON.parse(el.dataset.options || '[]'),
        };
        this.showModal = true;
    },

    showDetails: false,
    details: null,
    openDetails(data) {
        this.details = data;
        this.showDetails = true;
    },

    showManage: {{ $subsidyErrors->any() ? 'true' : 'false' }},
    subsidyForm: {
        id: null,
        name: @js($subsidyErrors->any() ? old('name', '') : ''),
        amount: @js($subsidyErrors->any() ? old('amount', '') : ''),
        description: @js($subsidyErrors->any() ? old('description', '') : ''),
        frequency: @js($subsidyErrors->any() ? old('frequency', 'one_time') : 'one_time'),
        is_active: {{ $subsidyErrors->any() ? (old('is_active') ? 'true' : 'false') : 'true' }},
    },
    editSubsidy(el) {
        this.subsidyForm = {
            id: el.dataset.id,
            name: el.dataset.name,
            amount: el.dataset.amount,
            description: el.dataset.description,
            frequency: el.dataset.frequency,
            is_active: el.dataset.active === '1',
        };
    },
    resetSubsidyForm() {
        this.subsidyForm = { id: null, name: '', amount: '', description: '', frequency: 'one_time', is_active: true };
    },
}" class="max-w-7xl mx-auto">

{{-- =====================================================
    BANNER
====================================================== --}}
<div class="relative mb-6 overflow-hidden rounded-2xl bg-[oklch(45%_0.15_151.711)] px-6 py-7 text-white sm:px-8 sm:py-9">
<svg class="pointer-events-none absolute -right-6 -bottom-10 size-48 text-white/10" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4.5 4.5 0 100-9 4.5 4.5 0 000 9zm0 2c-4.14 0-7.5 2.24-7.5 5v1.5h15V19c0-2.76-3.36-5-7.5-5z"/></svg>
<div class="flex flex-wrap items-start justify-between gap-4">
<div>
<h1 class="text-2xl font-bold sm:text-3xl">Subsidies</h1>
<p class="mt-1 max-w-xl text-sm text-white/80">Senior citizens enlisted through an announcement and waiting for a subsidy release. Released profiles move to Subsidy Releases.</p>
<p class="mt-4 text-sm text-white/90">{{ $seniors->total() }} {{ \Illuminate\Support\Str::plural('beneficiary', $seniors->total()) }} {{ request()->hasAny(['search', 'barangay', 'subsidy']) ? 'match your filters' : 'waiting for release' }}</p>
</div>
<button type="button" x-on:click="resetSubsidyForm(); showManage = true" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-[oklch(38%_0.15_151.711)] shadow-sm transition hover:bg-green-50">
<svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
Add / Manage Subsidies
</button>
</div>
</div>


@if (session('success'))
<div x-data="{ show: true }" x-show="show" x-transition class="mb-6 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4">
<svg class="mt-0.5 size-5 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
<p class="flex-1 text-sm font-medium text-green-800">{{ session('success') }}</p>
<button type="button" x-on:click="show = false" class="text-green-600 hover:text-green-800" aria-label="Dismiss">
<svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
</button>
</div>
@endif

@if (session('error'))
<div x-data="{ show: true }" x-show="show" x-transition class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4">
<svg class="mt-0.5 size-5 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
<p class="flex-1 text-sm font-medium text-red-800">{{ session('error') }}</p>
<button type="button" x-on:click="show = false" class="text-red-600 hover:text-red-800" aria-label="Dismiss">
<svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
</button>
</div>
@endif


@if ($subsidyOptions->isEmpty())
<div class="mb-6 rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
No subsidy programs exist yet, so releases are disabled and the application form has nothing to choose from. Click <strong>Add / Manage Subsidies</strong> to create one.
</div>
@endif


{{-- =====================================================
    FILTERS
====================================================== --}}
<form method="GET" action="{{ route('subsidies.index') }}" class="mb-6 flex flex-wrap items-center gap-3">
<div class="w-full sm:w-52">
<label for="barangay" class="sr-only">Barangay</label>
<select name="barangay" id="barangay" class="{{ $field }} w-full" onchange="this.form.submit()">
<option value="">All Barangays</option>
@foreach ($barangays as $barangay)
<option value="{{ $barangay }}" @selected(request('barangay') === $barangay)>{{ $barangay }}</option>
@endforeach
</select>
</div>

<div class="w-full sm:w-52">
<label for="subsidy" class="sr-only">Subsidy</label>
<select name="subsidy" id="subsidy" class="{{ $field }} w-full" onchange="this.form.submit()">
<option value="" @selected(! $selectedSubsidy)>All Subsidies</option>
@foreach ($subsidyOptions as $option)
<option value="{{ $option->id }}" @selected(optional($selectedSubsidy)->id === $option->id)>{{ $option->name }}{{ $option->is_active ? '' : ' (inactive)' }}</option>
@endforeach
</select>
</div>

<div class="relative w-full sm:w-72">
<label for="search" class="sr-only">Search by name</label>
<svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
<input type="search" name="search" id="search" value="{{ request('search') }}" placeholder="Search by name" class="{{ $field }} w-full pl-9">
</div>

<button type="submit" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800">Search</button>

@if (request()->hasAny(['search', 'barangay', 'subsidy']))
<a href="{{ route('subsidies.index') }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Clear</a>
@endif
</form>


{{-- =====================================================
    BENEFICIARIES TABLE
====================================================== --}}
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

<div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">

<thead class="bg-gray-50">
<tr>
<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No.</th>
<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Full Name</th>
<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Age</th>
<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Address</th>
<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Subsidy</th>
<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Contact</th>
<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">For Release</th>
</tr>
</thead>

<tbody class="divide-y divide-gray-100 bg-white">

@forelse ($seniors as $senior)
@php
    // Subsidies this senior is enlisted in (via an announcement) and not yet released.
    $pending    = $senior->pendingSubsidies;
    $rowSubsidy = $selectedSubsidy ? $pending->firstWhere('id', $selectedSubsidy->id) : $pending->first();

    $details = [
        'name'         => $senior->list_name,
        'gender'       => ucfirst($senior->gender),
        'birth_date'   => $senior->birth_date->format('F d, Y'),
        'age'          => (string) $senior->age,
        'contact'      => $senior->contact_number ?: '—',
        'is_active'    => (bool) $senior->is_active,
        'region'       => $senior->region ?: '—',
        'municipality' => $senior->municipality ?: '—',
        'barangay'     => $senior->barangay ?: '—',
        'address'      => $senior->address ?: '—',
        'subsidies'    => $pending->map(fn ($s) => $s->name . ' · ' . $s->frequency_label)->values()->all(),
    ];
@endphp
<tr class="cursor-pointer transition hover:bg-gray-50 focus:bg-gray-50 focus:outline-none" tabindex="0" role="button"
    x-on:click="openDetails(@js($details))" x-on:keydown.enter="openDetails(@js($details))">

<td class="whitespace-nowrap px-6 py-4">
<p class="text-sm font-semibold tabular-nums text-gray-900">{{ $seniors->firstItem() + $loop->index }}</p>
</td>

<td class="whitespace-nowrap px-6 py-4">
<p class="truncate text-sm font-semibold text-gray-900">{{ $senior->list_name }}</p>
<p class="text-xs text-gray-500">{{ ucfirst($senior->gender) }}</p>
</td>

<td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
<span class="font-medium tabular-nums text-gray-900">{{ $senior->age }}</span>
<span class="block text-xs text-gray-400">{{ $senior->birth_date->format('M d, Y') }}</span>
</td>

<td class="px-6 py-4 text-sm text-gray-600">
<p class="font-medium text-gray-900">{{ $senior->barangay }}</p>
<p class="text-xs text-gray-400">{{ $senior->municipality }}</p>
</td>

<td class="px-6 py-4 text-sm text-gray-600">
@forelse ($pending as $s)
<p class="{{ $loop->first ? 'font-medium text-gray-900' : 'text-xs text-gray-500' }}">{{ $s->name }}</p>
@empty
<span class="text-gray-400">—</span>
@endforelse
</td>

<td class="whitespace-nowrap px-6 py-4 text-sm tabular-nums text-gray-600">{{ $senior->contact_number ?: '—' }}</td>

<td class="whitespace-nowrap px-6 py-4 text-sm">
@if (! $senior->is_active)
<span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600"><span class="size-1.5 rounded-full bg-gray-400"></span>Inactive</span>
@elseif (! $rowSubsidy)
<span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600"><span class="size-1.5 rounded-full bg-gray-400"></span>No active subsidy</span>
@else
{{-- .stop keeps this click from also opening the details popup --}}
<button type="button"
        x-on:click.stop="openRelease($event.currentTarget)"
        x-on:keydown.enter.stop
        data-name="{{ $senior->list_name }}"
        data-url="{{ route('subsidies.release', $senior) }}"
        data-subsidy-id="{{ $rowSubsidy->id }}"
        data-subsidy-name="{{ $rowSubsidy->name }}"
        data-frequency="{{ $rowSubsidy->frequency_label }}"
        data-amount="{{ $rowSubsidy->amount }}"
        data-options="{{ $pending->map(fn ($s) => ['id' => $s->id, 'name' => $s->name . ' · ' . $s->frequency_label, 'amount' => (string) $s->amount])->values()->toJson() }}"
        class="inline-flex items-center gap-1.5 rounded-xl bg-[oklch(45%_0.15_151.711)] px-3.5 py-2 text-xs font-medium text-white shadow-sm transition hover:bg-[oklch(38%_0.15_151.711)]">
<svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
Release
</button>
@endif
</td>

</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-16 text-center">
<svg class="mx-auto size-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0112.728 0zM15.75 8.25a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM19.5 15a3 3 0 100-6 3 3 0 000 6z" /></svg>
<p class="mt-3 text-sm font-medium text-gray-900">No beneficiaries found</p>
<p class="mt-1 text-sm text-gray-500">{{ request()->hasAny(['search', 'barangay', 'subsidy']) ? 'Try adjusting your filters.' : 'Beneficiaries appear here once an announcement is posted for a subsidy.' }}</p>
</td>
</tr>
@endforelse

</tbody>
</table>
</div>

@if ($seniors->hasPages())
<div class="border-t border-gray-100 px-6 py-4">{{ $seniors->links() }}</div>
@endif

</div>


{{-- =====================================================
    DETAILS MODAL
====================================================== --}}
<div x-show="showDetails" x-cloak style="display: none;" x-on:keydown.escape.window="showDetails = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
<div x-show="showDetails" x-transition.opacity x-on:click="showDetails = false" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

<div x-show="showDetails" x-transition.scale.95 x-on:click.stop class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-xl">
<template x-if="details">
<div>
<div class="flex items-start justify-between gap-4 px-6 pt-6">
<div class="min-w-0">
<h3 class="truncate text-lg font-semibold text-gray-900" x-text="details.name"></h3>
<span class="mt-1 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
      x-bind:class="details.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
      x-text="details.is_active ? 'Active' : 'Inactive'"></span>
</div>
<button type="button" x-on:click="showDetails = false" class="shrink-0 rounded-full p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
</button>
</div>

<div class="space-y-5 px-6 py-5">
<div>
<p class="mb-2 text-sm font-semibold text-gray-900">Personal information</p>
<dl class="grid grid-cols-2 gap-4 rounded-xl bg-gray-50 p-4 text-sm">
<div><dt class="text-xs text-gray-500">Gender</dt><dd class="mt-0.5 font-medium text-gray-900" x-text="details.gender || '—'"></dd></div>
<div><dt class="text-xs text-gray-500">Age</dt><dd class="mt-0.5 font-medium tabular-nums text-gray-900" x-text="details.age + ' years old'"></dd></div>
<div><dt class="text-xs text-gray-500">Birth date</dt><dd class="mt-0.5 font-medium text-gray-900" x-text="details.birth_date || '—'"></dd></div>
<div><dt class="text-xs text-gray-500">Contact number</dt><dd class="mt-0.5 font-medium tabular-nums text-gray-900" x-text="details.contact || '—'"></dd></div>
</dl>
</div>

<div>
<p class="mb-2 text-sm font-semibold text-gray-900">Address</p>
<dl class="grid grid-cols-2 gap-4 rounded-xl bg-gray-50 p-4 text-sm">
<div class="col-span-2"><dt class="text-xs text-gray-500">House no. / street</dt><dd class="mt-0.5 font-medium text-gray-900" x-text="details.address || '—'"></dd></div>
<div><dt class="text-xs text-gray-500">Barangay</dt><dd class="mt-0.5 font-medium text-gray-900" x-text="details.barangay || '—'"></dd></div>
<div><dt class="text-xs text-gray-500">City / municipality</dt><dd class="mt-0.5 font-medium text-gray-900" x-text="details.municipality || '—'"></dd></div>
<div><dt class="text-xs text-gray-500">Region</dt><dd class="mt-0.5 font-medium text-gray-900" x-text="details.region || '—'"></dd></div>
</dl>
</div>

<div>
<p class="mb-2 text-sm font-semibold text-gray-900">Subsidy</p>
<div class="rounded-xl bg-gray-50 p-4 text-sm">
<template x-if="details.subsidies.length">
<ul class="space-y-1">
<template x-for="s in details.subsidies" x-bind:key="s"><li class="font-medium text-gray-900" x-text="s"></li></template>
</ul>
</template>
<p x-show="!details.subsidies.length" class="font-medium text-gray-400">No subsidy</p>
</div>
</div>
</div>

<div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
<button type="button" x-on:click="showDetails = false" class="rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100">Close</button>
</div>
</div>
</template>
</div>
</div>


{{-- =====================================================
    ADD / MANAGE SUBSIDIES MODAL
====================================================== --}}
<div x-show="showManage" x-cloak style="display: none;" x-on:keydown.escape.window="showManage = false"
     class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-6" role="dialog" aria-modal="true">
<div x-show="showManage" x-transition.opacity x-on:click="showManage = false" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

<div x-show="showManage" x-transition.scale.95 x-on:click.stop class="relative w-full max-w-2xl rounded-2xl bg-white shadow-xl">

<div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
<div>
<h2 class="text-lg font-bold text-gray-900">Subsidy Types</h2>
<p class="text-sm text-gray-500">Active subsidies appear in the Application form's Subsidy Type dropdown.</p>
</div>
<button type="button" x-on:click="showManage = false" class="rounded-full p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
</button>
</div>

<div class="max-h-[70vh] overflow-y-auto px-6 py-5">

@if ($subsidyErrors->any())
<div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4">
<p class="text-sm font-medium text-red-800">Please fix the following:</p>
<ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
@foreach ($subsidyErrors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

{{-- Add / edit form. When subsidyForm.id is set it submits as PUT to that subsidy. --}}
<form method="POST"
      x-bind:action="subsidyForm.id ? '{{ url('/subsidies') }}/' + subsidyForm.id : '{{ route('subsidies.store') }}'"
      class="rounded-xl bg-gray-50 p-4">
@csrf
<input type="hidden" name="_method" value="PUT" x-bind:disabled="!subsidyForm.id">

<p class="mb-3 text-sm font-semibold text-gray-900" x-text="subsidyForm.id ? 'Edit subsidy' : 'Add a subsidy'"></p>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
<div>
<label for="subsidy_name" class="{{ $label }}">Name</label>
<input type="text" name="name" id="subsidy_name" x-model="subsidyForm.name" required maxlength="150" class="{{ $field }} mt-1 w-full" placeholder="Social Pension">
</div>

<div>
<label for="subsidy_amount" class="{{ $label }}">Default amount</label>
<div class="relative mt-1">
<span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">&#8369;</span>
<input type="number" step="0.01" min="0" name="amount" id="subsidy_amount" x-model="subsidyForm.amount" required class="{{ $field }} w-full pl-8">
</div>
</div>

<div>
<label for="subsidy_frequency" class="{{ $label }}">Frequency</label>
<select name="frequency" id="subsidy_frequency" x-model="subsidyForm.frequency" required class="{{ $field }} mt-1 w-full">
@foreach (\App\Models\Subsidy::FREQUENCIES as $value => $freqLabel)
<option value="{{ $value }}">{{ $freqLabel }}</option>
@endforeach
</select>
</div>

<div class="flex items-end pb-2">
<label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
<input type="checkbox" name="is_active" value="1" x-model="subsidyForm.is_active"
       class="rounded border-gray-300 text-[oklch(45%_0.15_151.711)] focus:ring-[oklch(45%_0.15_151.711)]">
Active (available in the Application form)
</label>
</div>

<div class="sm:col-span-2">
<label for="subsidy_description" class="{{ $label }}">Description <span class="text-gray-400">(optional)</span></label>
<textarea name="description" id="subsidy_description" x-model="subsidyForm.description" rows="2" maxlength="2000" class="{{ $field }} mt-1 w-full resize-none"></textarea>
</div>
</div>

<div class="mt-4 flex items-center justify-end gap-3">
<button type="button" x-show="subsidyForm.id" x-on:click="resetSubsidyForm()" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100">Cancel edit</button>
<button type="submit" class="rounded-xl bg-[oklch(45%_0.15_151.711)] px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[oklch(38%_0.15_151.711)]" x-text="subsidyForm.id ? 'Update subsidy' : 'Add subsidy'"></button>
</div>
</form>

{{-- Existing subsidies --}}
<div class="mt-5 overflow-hidden rounded-xl border border-gray-200">
<table class="min-w-full divide-y divide-gray-200">
<thead class="bg-gray-50">
<tr>
<th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Name</th>
<th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Amount</th>
<th class="px-4 py-2.5"><span class="sr-only">Actions</span></th>
</tr>
</thead>
<tbody class="divide-y divide-gray-100 bg-white">
@forelse ($subsidyOptions as $option)
<tr>
<td class="px-4 py-3 text-sm">
<p class="font-semibold text-gray-900">{{ $option->name }}</p>
<p class="text-xs text-gray-500">{{ $option->frequency_label }}{{ $option->is_active ? '' : ' · Inactive' }}</p>
@if ($option->description)
<p class="text-xs text-gray-400">{{ \Illuminate\Support\Str::limit($option->description, 80) }}</p>
@endif
</td>
<td class="whitespace-nowrap px-4 py-3 text-sm tabular-nums text-gray-600">&#8369;{{ number_format((float) $option->amount, 2) }}</td>
<td class="whitespace-nowrap px-4 py-3 text-right">
<div class="flex justify-end gap-2">
<button type="button"
        x-on:click="editSubsidy($event.currentTarget)"
        data-id="{{ $option->id }}"
        data-name="{{ $option->name }}"
        data-amount="{{ $option->amount }}"
        data-description="{{ $option->description }}"
        data-frequency="{{ $option->frequency }}"
        data-active="{{ $option->is_active ? '1' : '0' }}"
        class="rounded-xl border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50">Edit</button>

<form method="POST" action="{{ route('subsidies.destroy', $option) }}"
      onsubmit="return confirm('Delete {{ addslashes($option->name) }}? This cannot be undone.');">
@csrf
@method('DELETE')
<button type="submit" class="rounded-xl px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50">Delete</button>
</form>
</div>
</td>
</tr>
@empty
<tr>
<td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">No subsidies yet. Add the first one above.</td>
</tr>
@endforelse
</tbody>
</table>
</div>

</div>

<div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
<button type="button" x-on:click="showManage = false" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Done</button>
</div>

</div>
</div>


{{-- =====================================================
    RELEASE MODAL
====================================================== --}}
<div x-show="showModal" x-cloak style="display: none;" x-on:keydown.escape.window="showModal = false"
     class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
<div x-show="showModal" x-transition.opacity x-on:click="showModal = false" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

<div x-show="showModal" x-transition.scale.95 x-on:click.stop class="relative w-full max-w-md rounded-2xl bg-white shadow-xl">

<div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
<div>
<h2 class="text-lg font-bold text-gray-900">Release subsidy</h2>
<p class="text-sm text-gray-500" x-text="target.name"></p>
</div>
<button type="button" x-on:click="showModal = false" class="rounded-full p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
</button>
</div>

<form method="POST" :action="target.url">
@csrf
<input type="hidden" name="subsidy_id" x-model="releaseForm.subsidy_id">

<div class="space-y-5 px-6 py-5">

@if ($errors->any() && ! $subsidyErrors->any())
<div class="rounded-xl border border-red-200 bg-red-50 p-4">
<p class="text-sm font-medium text-red-800">Please fix the following:</p>
<ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<div>
<label for="release-subsidy" class="{{ $label }}">Subsidy</label>
<select id="release-subsidy" x-model="releaseForm.subsidy_id" x-on:change="pickSubsidy()" class="{{ $field }} mt-1 w-full">
<template x-for="o in releaseForm.options" :key="o.id">
<option :value="o.id" x-text="o.name" :selected="String(o.id) === String(releaseForm.subsidy_id)"></option>
</template>
</select>
</div>

<div>
<label for="release-amount" class="{{ $label }}">Amount</label>
<div class="relative mt-1">
<span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">&#8369;</span>
<input type="number" step="0.01" min="0" name="amount" id="release-amount" required
       x-model="releaseForm.amount"
       class="{{ $field }} w-full pl-8">
</div>
</div>

<div>
<label for="release-remarks" class="{{ $label }}">Remarks <span class="text-gray-400">(optional)</span></label>
<textarea name="remarks" id="release-remarks" rows="2" maxlength="500" class="{{ $field }} mt-1 w-full resize-none">{{ old('remarks') }}</textarea>
</div>

</div>

<div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
<button type="button" x-on:click="showModal = false" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Cancel</button>
<button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[oklch(45%_0.15_151.711)] px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[oklch(38%_0.15_151.711)]">Confirm release</button>
</div>
</form>

</div>
</div>

</div>

</x-app-layout>