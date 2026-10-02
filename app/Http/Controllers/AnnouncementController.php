<?php

namespace App\Http\Controllers;

use App\Jobs\SendAnnouncementSms;
use App\Models\Announcement;
use App\Models\SeniorCitizen;
use App\Models\Subsidy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with(['barangays', 'subsidy'])
            ->latest()
            ->paginate(10);

        $barangays = $this->availableBarangays();

        // Created subsidies for the "Subsidy Type" dropdown
        $subsidies = Subsidy::orderBy('name')->get();

        $recipientsCount = $this->recipientsQuery([])->count();

        return view('announcements.index', compact(
            'announcements',
            'barangays',
            'subsidies',
            'recipientsCount'
        ));
    }

    public function create()
    {
        $barangays = $this->availableBarangays();

        $subsidies = Subsidy::orderBy('name')->get();

        $recipientsCount = $this->recipientsQuery([])->count();

        return view('announcements.create', compact(
            'barangays',
            'subsidies',
            'recipientsCount'
        ));
    }

    public function recipientsCount(Request $request)
    {
        $codes = array_filter(
            (array) $request->input('barangay_codes', [])
        );

        return response()->json([
            'count' => $this->recipientsQuery($codes)->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subsidy_id' => ['required', 'exists:subsidies,id'],
            'message' => ['required', 'string', 'max:300'],
            'distribution_date' => ['nullable', 'date'],
            'distribution_location' => ['nullable', 'string', 'max:255'],
            'barangay_codes' => ['nullable', 'array'],
            'barangay_codes.*' => ['string'],
        ]);

        $codes = array_values(array_filter($validated['barangay_codes'] ?? []));

        $announcement = DB::transaction(function () use ($validated, $codes) {

            $announcement = Announcement::create([
                'title' => $validated['title'],
                'subsidy_id' => $validated['subsidy_id'],
                'message' => $validated['message'],
                'distribution_date' => $validated['distribution_date'] ?? null,
                'distribution_location' => $validated['distribution_location'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->syncBarangays($announcement, $codes);

            // Auto-enlist eligible senior citizens into the subsidy.
            // Only those in the selected barangays (all when none selected).
            // syncWithoutDetaching + the unique index prevent duplicates.
            Subsidy::findOrFail($validated['subsidy_id'])
                ->seniorCitizens()
                ->syncWithoutDetaching($this->eligibleQuery($codes)->pluck('id'));

            $announcement->update([
                'recipients_count' => $this->recipientsQuery($codes)->count(),
            ]);

            return $announcement;
        });

        if ($request->boolean('send_now')) {
            SendAnnouncementSms::dispatch($announcement);
        }

        return redirect()
            ->route('announcements.index')
            ->with(
                'success',
                $request->boolean('send_now')
                    ? 'Announcement created and SMS notifications are being sent.'
                    : 'Announcement saved as draft.'
            );
    }

    public function update(Request $request, Announcement $announcement)
    {
        // Sent or in-progress messages can't be edited.
        abort_unless(
            in_array($announcement->status, ['draft', 'failed']),
            403,
            'This announcement can no longer be edited.'
        );

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subsidy_id' => ['required', 'exists:subsidies,id'],
            'message' => ['required', 'string', 'max:300'],
            'distribution_date' => ['nullable', 'date'],
            'distribution_location' => ['nullable', 'string', 'max:255'],
            'barangay_codes' => ['nullable', 'array'],
            'barangay_codes.*' => ['string'],
        ]);

        $codes = array_values(array_filter($validated['barangay_codes'] ?? []));

        DB::transaction(function () use ($announcement, $validated, $codes) {

            $announcement->update([
                'title' => $validated['title'],
                'subsidy_id' => $validated['subsidy_id'],
                'message' => $validated['message'],
                'distribution_date' => $validated['distribution_date'] ?? null,
                'distribution_location' => $validated['distribution_location'] ?? null,
                'recipients_count' => $this->recipientsQuery($codes)->count(),
                'status' => 'draft', // a failed announcement returns to draft once edited
            ]);

            // Replace the selected barangays.
            $announcement->barangays()->delete();
            $this->syncBarangays($announcement, $codes);

            // Enlist seniors for the (possibly new) subsidy, same as store().
            Subsidy::findOrFail($validated['subsidy_id'])
                ->seniorCitizens()
                ->syncWithoutDetaching($this->eligibleQuery($codes)->pluck('id'));
        });

        if ($request->boolean('send_now')) {
            SendAnnouncementSms::dispatch($announcement->fresh());
        }

        return redirect()
            ->route('announcements.index')
            ->with(
                'success',
                $request->boolean('send_now')
                    ? 'Announcement updated and SMS notifications are being sent.'
                    : 'Announcement updated.'
            );
    }

    public function destroy(Announcement $announcement)
    {
        abort_if($announcement->status === 'sending', 409, 'This announcement is being sent right now.');

        DB::transaction(function () use ($announcement) {
            $announcement->barangays()->delete();
            $announcement->delete();
        });

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement deleted.');
    }

    public function send(Announcement $announcement)
    {
        abort_if($announcement->status === 'sent', 400, 'Already sent.');

        SendAnnouncementSms::dispatch($announcement);

        return back()->with('success', 'Sending SMS notifications to senior citizens.');
    }

    /**
     * Save the selected barangays for an announcement.
     * senior_citizens uses "barangay"; announcement_barangays uses "barangay_name".
     */
    protected function syncBarangays(Announcement $announcement, array $codes): void
    {
        if (empty($codes)) {
            return;
        }

        $allBarangays = $this->availableBarangays()->keyBy('barangay_code');

        foreach ($codes as $code) {
            $announcement->barangays()->create([
                'barangay_code' => $code,
                'barangay_name' => $allBarangays->get($code)?->barangay ?? $code,
            ]);
        }
    }

    /**
     * Eligible seniors (active, not deceased), limited to the given
     * barangay codes. No codes = every eligible senior citizen.
     */
    protected function eligibleQuery(array $codes)
    {
        return SeniorCitizen::eligible()
            ->when(
                count($codes) > 0,
                fn ($q) => $q->whereIn('barangay_code', $codes)
            );
    }

    /**
     * SMS recipients = eligible seniors with a contact number on file.
     */
    protected function recipientsQuery(array $codes)
    {
        return $this->eligibleQuery($codes)->whereNotNull('contact_number');
    }

    /**
     * Distinct barangays from senior_citizens.
     */
    protected function availableBarangays()
    {
        return SeniorCitizen::whereNotNull('barangay_code')
            ->select('barangay_code', 'barangay')
            ->distinct()
            ->orderBy('barangay')
            ->get();
    }
}
