<?php

namespace App\Http\Controllers;

use App\Models\SeniorCitizen;
use App\Models\Subsidy;
use App\Models\SubsidyRelease;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubsidyController extends Controller
{
    public function index(Request $request): View
    {
        $subsidyOptions = Subsidy::orderBy('id')->get();

        // Null = "All Subsidies". Only set when the user actually picked one.
        $selectedSubsidy = $request->filled('subsidy')
            ? $subsidyOptions->firstWhere('id', (int) $request->subsidy)
            : null;

        // A senior shows up here only when they were enlisted in a subsidy
        // (which happens when an announcement for that subsidy is saved)
        // and that subsidy has not been released to them yet.
        // See SeniorCitizen::pendingSubsidies().
        $seniors = SeniorCitizen::query()
            ->whereHas('pendingSubsidies', function ($q) use ($selectedSubsidy) {
                if ($selectedSubsidy) {
                    $q->where('subsidies.id', $selectedSubsidy->id);
                }
            })
            ->with('pendingSubsidies')
            ->when($request->filled('barangay'), fn ($q) => $q->where('barangay', $request->barangay))
            ->when($request->filled('search'), fn ($q) => $q->where('list_name', 'like', '%' . $request->search . '%'))
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $barangays = SeniorCitizen::query()
            ->where('barangay', '!=', '')
            ->distinct()
            ->orderBy('barangay')
            ->pluck('barangay');

        return view('subsidies.index', compact(
            'seniors', 'subsidyOptions', 'selectedSubsidy', 'barangays'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedSubsidy($request);

        Subsidy::create($data);

        return back()->with('success', "Subsidy \"{$data['name']}\" added.");
    }

    public function update(Request $request, Subsidy $subsidy): RedirectResponse
    {
        $subsidy->update($this->validatedSubsidy($request, $subsidy));

        return back()->with('success', 'Subsidy updated.');
    }

    public function destroy(Subsidy $subsidy): RedirectResponse
    {
        if ($subsidy->applications()->exists()) {
            return back()->with('error', "\"{$subsidy->name}\" is used by existing applications. Deactivate it instead of deleting.");
        }

        if ($subsidy->releases()->exists()) {
            return back()->with('error', "\"{$subsidy->name}\" has release records. Deactivate it instead of deleting.");
        }

        try {
            $subsidy->delete();
        } catch (QueryException $e) {
            return back()->with('error', "\"{$subsidy->name}\" is still referenced elsewhere and can't be deleted.");
        }

        return back()->with('success', 'Subsidy deleted.');
    }

    public function release(Request $request, SeniorCitizen $seniorCitizen): RedirectResponse
    {
        $data = $request->validate([
            'subsidy_id' => ['required', 'exists:subsidies,id'],
            'amount'     => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'remarks'    => ['nullable', 'string', 'max:500'],
        ]);

        if (! $seniorCitizen->is_active) {
            return back()->with('error', 'Only active senior citizens can receive a subsidy.');
        }

        $subsidy = Subsidy::findOrFail($data['subsidy_id']);

        if (! $subsidy->is_active) {
            return back()->with('error', "\"{$subsidy->name}\" is inactive and can't be released.");
        }

        // The senior must be enlisted in this subsidy (via an announcement)
        // and must not have been released it already.
        $isPending = $seniorCitizen->pendingSubsidies()
            ->where('subsidies.id', $subsidy->id)
            ->exists();

        if (! $isPending) {
            return back()->with('error', "This senior citizen is not waiting for \"{$subsidy->name}\". An announcement for it must be posted first.");
        }

        // Transaction + row lock so a double-click can't create two release records.
        $result = DB::transaction(function () use ($seniorCitizen, $subsidy, $data) {
            $existing = SubsidyRelease::where('senior_citizen_id', $seniorCitizen->id)
                ->where('subsidy_id', $subsidy->id)
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->status === 'released') {
                return 'duplicate';
            }

            $attributes = [
                'amount'       => $data['amount'],
                'release_date' => now()->toDateString(),
                'status'       => 'released',
                'released_by'  => Auth::user()->name,
                'remarks'      => $data['remarks'] ?? '',
            ];

            if ($existing) {
                $existing->update($attributes);
            } else {
                SubsidyRelease::create($attributes + [
                    'senior_citizen_id' => $seniorCitizen->id,
                    'subsidy_id'        => $subsidy->id,
                    'reference_number'  => $this->newReferenceNumber(),
                ]);
            }

            return 'ok';
        });

        if ($result === 'duplicate') {
            return back()->with('error', 'This subsidy has already been released to this senior citizen.');
        }

        return back()->with('success', ($seniorCitizen->list_name ?? 'The senior citizen') . ' was released and moved to Subsidy Releases.');
    }

    private function validatedSubsidy(Request $request, ?Subsidy $subsidy = null): array
    {
        $data = $request->validateWithBag('subsidy', [
            'name'        => ['required', 'string', 'max:150', Rule::unique('subsidies', 'name')->ignore($subsidy?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount'      => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'frequency'   => ['required', Rule::in(array_keys(Subsidy::FREQUENCIES))],
        ]);

        $data['description'] = $data['description'] ?? null;
        $data['is_active']   = $request->boolean('is_active');

        return $data;
    }

    private function newReferenceNumber(): string
    {
        do {
            $reference = 'SR-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (SubsidyRelease::where('reference_number', $reference)->exists());

        return $reference;
    }
}