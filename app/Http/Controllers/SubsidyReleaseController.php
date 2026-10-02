<?php

namespace App\Http\Controllers;

use App\Models\SeniorCitizen;
use App\Models\Subsidy;
use App\Models\SubsidyRelease;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubsidyReleaseController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search'    => ['nullable', 'string', 'max:100'],
            'status'    => ['nullable', Rule::in(['pending', 'released', 'cancelled'])],
            'subsidy'   => ['nullable', 'integer', 'exists:subsidies,id'],
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = SubsidyRelease::query()
            ->with(['seniorCitizen', 'subsidy'])
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('seniorCitizen', function ($q) use ($search) {
                            // Adjust these columns to match your senior_citizens table
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['subsidy'] ?? null, fn ($q, $id) => $q->where('subsidy_id', $id))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('release_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('release_date', '<=', $date));

        // Total of released amounts within the current filters (ignores pagination)
        $totalReleased = (clone $query)->where('status', 'released')->sum('amount');

        $releases = $query
            ->latest('release_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('subsidy-releases.index', [
            'releases'      => $releases,
            'subsidies'     => Subsidy::orderBy('name')->get(['id', 'name']),
            'totalReleased' => $totalReleased,
            'filters'       => $filters,
        ]);
    }

    public function create(): View
    {
        return view('subsidy-releases.create', [
            'seniorCitizens' => SeniorCitizen::orderBy('last_name')->get(),
            'subsidies'      => Subsidy::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'senior_citizen_id' => [
                'required',
                'exists:senior_citizens,id',
                // Migration enforces one release per senior citizen per subsidy
                Rule::unique('subsidy_releases')->where(
                    fn ($q) => $q->where('subsidy_id', $request->input('subsidy_id'))
                ),
            ],
            'subsidy_id'   => ['required', 'exists:subsidies,id'],
            'amount'       => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'release_date' => ['required', 'date'],
            'status'       => ['required', Rule::in(['pending', 'released', 'cancelled'])],
            'remarks'      => ['nullable', 'string', 'max:500'],
        ], [
            'senior_citizen_id.unique' => 'This senior citizen already has a release for the selected subsidy.',
        ]);

        $data['reference_number'] = $this->generateReferenceNumber();
        $data['released_by']      = $data['status'] === 'released' ? $request->user()->name : '';
        $data['remarks']          = $data['remarks'] ?? '';

        SubsidyRelease::create($data);

        return redirect()
            ->route('subsidy-releases.index')
            ->with('success', 'Subsidy release recorded.');
    }

    public function edit(SubsidyRelease $subsidyRelease): View
    {
        return view('subsidy-releases.edit', [
            'release'        => $subsidyRelease,
            'seniorCitizens' => SeniorCitizen::orderBy('last_name')->get(),
            'subsidies'      => Subsidy::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SubsidyRelease $subsidyRelease): RedirectResponse
    {
        $data = $request->validate([
            'senior_citizen_id' => [
                'required',
                'exists:senior_citizens,id',
                Rule::unique('subsidy_releases')
                    ->where(fn ($q) => $q->where('subsidy_id', $request->input('subsidy_id')))
                    ->ignore($subsidyRelease->id),
            ],
            'subsidy_id'   => ['required', 'exists:subsidies,id'],
            'amount'       => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'release_date' => ['required', 'date'],
            'status'       => ['required', Rule::in(['pending', 'released', 'cancelled'])],
            'remarks'      => ['nullable', 'string', 'max:500'],
        ], [
            'senior_citizen_id.unique' => 'This senior citizen already has a release for the selected subsidy.',
        ]);

        $data['remarks'] = $data['remarks'] ?? '';

        // Record who released it, the first time it moves to "released"
        if ($data['status'] === 'released' && $subsidyRelease->status !== 'released') {
            $data['released_by'] = $request->user()->name;
        }

        $subsidyRelease->update($data);

        return redirect()
            ->route('subsidy-releases.index')
            ->with('success', 'Subsidy release updated.');
    }

    public function destroy(SubsidyRelease $subsidyRelease): RedirectResponse
    {
        if ($subsidyRelease->status === 'released') {
            return back()->with('error', 'Released subsidies cannot be deleted. Cancel the release instead.');
        }

        $subsidyRelease->delete();

        return redirect()
            ->route('subsidy-releases.index')
            ->with('success', 'Subsidy release deleted.');
    }

    private function generateReferenceNumber(): string
    {
        do {
            $reference = 'SR-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (SubsidyRelease::where('reference_number', $reference)->exists());

        return $reference;
    }
}