<x-app-layout>

    {{-- =========================================================
        DASHBOARD
    ========================================================== --}}

    <div class="max-w-7xl mx-auto">

        {{-- PAGE HEADING --}}
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-950">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">Overview of the Senior Citizen Subsidy Management System.</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-lg bg-white px-3.5 py-2 text-sm font-medium text-gray-600 ring-1 ring-gray-950/5 shadow-sm">
                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                {{ now()->format('F d, Y') }}
            </div>
        </div>

        {{-- =====================================================
            STAT CARDS + ANALYTICS — rendered by React/Redux.
            All data below is computed once in DashboardController
            and handed off as plain JSON; no client-side fetching.
        ====================================================== --}}
        @php
            $dashboardPayload = [
                'stats' => [
                    'totalSeniorCitizens' => $totalSeniorCitizens,
                    'activeBeneficiaries' => $activeBeneficiaries,
                    'approvedBeneficiaries' => $approvedBeneficiaries,
                    'pendingApplications' => $pendingApplications,
                    'totalSubsidyDistributed' => (float) $totalSubsidyDistributed,
                ],
                'status' => [
                    'pending' => $pendingApplications,
                    'verification' => $verificationApplications,
                    'approved' => $approvedApplications,
                    'rejected' => $rejectedApplications,
                ],
                'thisMonth' => [
                    'subsidy' => (float) $thisMonthSubsidy,
                    'releases' => $thisMonthReleases,
                ],
                'monthlyApplications' => collect(range(1, 12))
                    ->map(fn ($m) => (int) (optional($monthlyApplications->firstWhere('month', $m))->total ?? 0))
                    ->values(),
                'monthlySubsidies' => collect(range(1, 12))
                    ->map(fn ($m) => (float) (optional($monthlySubsidies->firstWhere('month', $m))->total ?? 0))
                    ->values(),
                'subsidyBreakdown' => $subsidyBreakdown
                    ->map(fn ($row) => ['name' => $row->name, 'total_amount' => (float) $row->total_amount])
                    ->values(),
            ];
        @endphp

        <div id="dashboard-root" class="mb-8">
            {{-- Static placeholder shown until React mounts / while the bundle loads --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                @for ($i = 0; $i < 4; $i++)
                    <div class="rounded-xl bg-white p-6 ring-1 ring-gray-950/5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="h-3 w-24 rounded bg-gray-100 animate-pulse"></div>
                            <div class="h-9 w-9 rounded-lg bg-gray-100 animate-pulse"></div>
                        </div>
                        <div class="h-7 w-20 rounded bg-gray-100 animate-pulse mt-4"></div>
                        <div class="h-3 w-32 rounded bg-gray-100 animate-pulse mt-4"></div>
                    </div>
                @endfor
            </div>
        </div>

        <script>
            window.__DASHBOARD_DATA__ = @json($dashboardPayload);
        </script>

        {{-- =====================================================
            RECENT ANNOUNCEMENTS — plain server-rendered list, no
            client state needed, so this stays as Blade.
        ====================================================== --}}
        <div class="rounded-xl bg-white ring-1 ring-gray-950/5 shadow-sm overflow-hidden">

            <div class="p-6 border-b border-gray-100 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        {{-- Megaphone: matches "announcements", not the calendar used elsewhere --}}
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-950">Recent Announcements</h2>
                        <p class="text-sm text-gray-500">Subsidy distribution notices sent to senior citizens</p>
                    </div>
                </div>

                <a href="{{ route('announcements.index') }}" class="shrink-0 inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-500 whitespace-nowrap">
                    View all
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
                    </svg>
                </a>
            </div>

            <div class="divide-y divide-gray-100">

                @forelse ($recentAnnouncements as $announcement)

                    @php
                        $statusStyles = [
                            'draft'   => ['badge' => 'bg-gray-50 text-gray-600 ring-gray-500/10',    'dot' => 'bg-gray-400',    'icon' => 'bg-gray-50 text-gray-400'],
                            'sending' => ['badge' => 'bg-amber-50 text-amber-700 ring-amber-600/10',  'dot' => 'bg-amber-500',   'icon' => 'bg-amber-50 text-amber-500'],
                            'sent'    => ['badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10', 'dot' => 'bg-emerald-500', 'icon' => 'bg-emerald-50 text-emerald-500'],
                            'failed'  => ['badge' => 'bg-rose-50 text-rose-700 ring-rose-600/10',     'dot' => 'bg-rose-500',    'icon' => 'bg-rose-50 text-rose-500'],
                        ];
                        $style = $statusStyles[$announcement->status] ?? $statusStyles['draft'];
                        $notifiedPct = $announcement->recipients_count > 0
                            ? min(100, round(($announcement->sent_count / $announcement->recipients_count) * 100))
                            : 0;
                    @endphp

                    <div class="p-6 flex items-start justify-between gap-4 hover:bg-gray-50/60 transition-colors">

                        <div class="min-w-0 flex items-start gap-3">
                            {{-- Document/notice icon — distinct from the megaphone used in the section header --}}
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $style['icon'] }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-950 truncate">{{ $announcement->title }}</p>
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $announcement->message }}</p>
                                <p class="flex items-center gap-1.5 text-xs text-gray-400 mt-2">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A9.001 9.001 0 003 12a9 9 0 109-9.036V3z" />
                                    </svg>
                                    @if ($announcement->distribution_date)
                                        Distribution: {{ $announcement->distribution_date->format('M d, Y') }}
                                        @if ($announcement->distribution_location)
                                            &middot; {{ $announcement->distribution_location }}
                                        @endif
                                    @else
                                        No distribution date set
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="text-right shrink-0 w-36">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $style['badge'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $style['dot'] }}"></span>
                                {{ ucfirst($announcement->status) }}
                            </span>

                            <p class="text-xs text-gray-500 mt-2.5">
                                <span class="font-medium text-gray-700">{{ $announcement->sent_count }}</span> / {{ $announcement->recipients_count }} notified
                            </p>
                            <div class="mt-1.5 h-1.5 w-full rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $style['dot'] }}" style="width: {{ $notifiedPct }}%"></div>
                            </div>
                        </div>

                    </div>

                @empty

                    <div class="p-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-50">
                            <svg class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75L19.5 12l-2.25 2.25m-10.5 0L4.5 12l2.25-2.25M14.25 6l-4.5 12" />
                            </svg>
                        </div>
                        <p class="mt-3 text-sm font-medium text-gray-900">No announcements yet</p>
                        <p class="mt-1 text-sm text-gray-400">Distribution notices you send will show up here.</p>
                    </div>

                @endforelse

            </div>

        </div>

    </div>

</x-app-layout>