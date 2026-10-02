<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Tables that have a `user_id` column and must be scoped per user.
     * senior_citizens is NOT listed because it has no user_id column
     * (it is treated as a shared registry).
     *
     * If you later add user_id to senior_citizens, add it here.
     */
    private array $scopedTables = [
        'subsidy_applications',
    ];

    /**
     * Base query for a table, scoped to the logged-in user
     * only when the table is user-owned.
     */
    private function owned(string $table)
    {
        $query = DB::table($table);

        if (in_array($table, $this->scopedTables, true)
            && Schema::hasColumn($table, 'user_id')) {
            $query->where("$table.user_id", auth()->id());
        }

        return $query;
    }

    public function index()
    {
        /* ---------------- SENIOR CITIZENS ----------------
         * senior_citizens has no `status` column: every row is already approved.
         * `is_active` is turned off by "Mark Deceased".
         */
        $totalSeniorCitizens   = $this->owned('senior_citizens')->count();
        $approvedBeneficiaries = $totalSeniorCitizens;

        $activeBeneficiaries = $this->owned('senior_citizens')
            ->where(function ($query) {
                $query->where('is_active', 1)->orWhereNull('is_active');
            })
            ->count();

        /* ---------------- SUBSIDY APPLICATIONS ---------------- */
        $pendingApplications      = $this->owned('subsidy_applications')->where('status', 'pending')->count();
        $verificationApplications = $this->owned('subsidy_applications')->where('status', 'for_verification')->count();

        // ASSUMPTION: status values are 'approved' and 'rejected'.
        $approvedApplications = $this->owned('subsidy_applications')->where('status', 'approved')->count();
        $rejectedApplications = $this->owned('subsidy_applications')->where('status', 'rejected')->count();

        /* ---------------- SUBSIDY DISTRIBUTED ----------------
         * ASSUMPTION: subsidy_applications has an `amount` column and
         * a `released` status. Change both to match your table.
         */
        // TODO: temporary placeholder until the real column names are known.
        // Replace with e.g.:
        // (float) $this->owned('subsidy_applications')->where('status', '<status>')->sum('<column>');
        $totalSubsidyDistributed = 0.0;
        $thisMonthSubsidy        = 0.0; // TODO: same column as above, filtered to the current month
        $thisMonthReleases       = 0;   // TODO: count of subsidies released this month

        /* ---------------- CHART DATA ----------------
         * Shapes required by dashboard.blade.php:
         *   monthlyApplications: rows with ->month (1-12), ->total
         *   monthlySubsidies:    rows with ->month (1-12), ->total
         *   subsidyBreakdown:    rows with ->name, ->total_amount
         */

        // ASSUMPTION: subsidy_applications has the default `created_at` column.
        $monthlyApplications = $this->owned('subsidy_applications')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->get();

        // TODO: needs the real money column + date column, e.g.
        // ->selectRaw('MONTH(released_at) as month, SUM(<column>) as total')
        $monthlySubsidies = collect();

        // TODO: needs the real program/type name + amount columns, e.g.
        // ->selectRaw('<name_col> as name, SUM(<amount_col>) as total_amount')->groupBy('<name_col>')
        $subsidyBreakdown = collect();

        /* ---------------- RECENT ANNOUNCEMENTS ----------------
         * The view reads per row: ->status (draft|sending|sent|failed),
         * ->recipients_count, ->sent_count, and probably ->title / ->created_at.
         * TODO: replace with your real model, e.g.
         *   \App\Models\Announcement::latest()->limit(5)->get();
         */
        $recentAnnouncements = collect();

        /* ---------------- KEEP YOURS ----------------
         * Paste the rest of your original index() here
         * (approved/rejected counts, charts, recent records, etc.).
         * Any other table you query through $this->owned() must either
         * have a user_id column and be listed in $scopedTables above,
         * or be left out of that list to stay unscoped.
         */

        return view('dashboard', compact(
            'totalSeniorCitizens',
            'approvedBeneficiaries',
            'activeBeneficiaries',
            'pendingApplications',
            'verificationApplications',
            'approvedApplications',
            'rejectedApplications',
            'totalSubsidyDistributed',
            'thisMonthSubsidy',
            'thisMonthReleases',
            'monthlyApplications',
            'monthlySubsidies',
            'subsidyBreakdown',
            'recentAnnouncements'
            // KEEP YOURS: add your other variables here
        ));
    }
}