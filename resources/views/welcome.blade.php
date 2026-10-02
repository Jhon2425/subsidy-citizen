<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Send senior citizens their subsidy approvals, release schedules, and reminders, and see who has been notified.">

        <title>{{ config('app.name', 'SENIOR SUBSIDY') }}</title>

        @php
            // Finds the logo wherever it was put: public/images/ (recommended) or public/build/.
            $logo = collect(['images/LGU-LOGO.svg', 'build/LGU-LOGO.svg'])
                ->first(fn ($path) => file_exists(public_path($path))) ?? 'images/LGU-LOGO.svg';
        @endphp

        @if (file_exists(public_path($logo)))
            <link rel="icon" type="image/svg+xml" href="{{ asset($logo) }}">
        @endif

        @fonts

        {{-- Jetstream's default typeface, so this page matches the login and dashboard screens. --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif;
            }

            /* One page-load moment: the sample notifications arrive on the phone, one after another. */
            @keyframes notice-in {
                from { opacity: 0; transform: translateY(-14px) scale(.97); }
                to   { opacity: 1; transform: translateY(0) scale(1); }
            }
            .notice { animation: notice-in .45s cubic-bezier(.2, .8, .2, 1) both; }

            @media (prefers-reduced-motion: reduce) {
                .notice { animation: none; }
            }
        </style>
    </head>

    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100
                 selection:bg-[#3d7a26] selection:text-white">

        <a href="#main"
           class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50
                  focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold
                  focus:text-gray-800 focus:shadow-lg">
            Skip to main content
        </a>

        @php
            /*
             * LGU identity. To change it without editing this file, set these in config/app.php
             * (backed by .env), for example:
             *   'lgu_name'   => env('LGU_NAME', 'Municipality of Candelaria, Quezon'),
             *   'lgu_office' => env('LGU_OFFICE', 'Office of Senior Citizens Affairs'),
             * The seal is resolved into $logo above (public/images/LGU-LOGO.svg, or public/build/ as a fallback).
             */
            $lgu    = config('app.lgu_name', 'Municipality of Candelaria, Quezon');
            $office = config('app.lgu_office', 'Office of Senior Citizens Affairs');

            // Shared Jetstream button styles (same classes as <x-button> and <x-secondary-button>).
            $btnPrimary = 'inline-flex items-center justify-center px-6 py-3 bg-[#2f6b1f] dark:bg-[#8fc078] border border-transparent rounded-md font-semibold text-sm text-white dark:text-[#12300a] uppercase tracking-widest hover:bg-[#245217] dark:hover:bg-[#a6d190] focus:bg-[#245217] dark:focus:bg-[#a6d190] active:bg-[#1f4a12] dark:active:bg-[#7fb066] focus:outline-none focus:ring-2 focus:ring-[#3d7a26] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150';
            $btnSecondary = 'inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[#3d7a26] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150';

            // Sample notifications shown on the phone. Not real messages. Newest first.
            $notices = [
                ['title' => 'Reminder',             'time' => 'now',    'delay' => '.5s',
                 'body'  => 'Your release is tomorrow, Sep 30, at 9:00 AM. Please bring your OSCA ID.'],
                ['title' => 'Release schedule',     'time' => 'Sep 19', 'delay' => '1.1s',
                 'body'  => '₱1,000.00 social pension will be released at Poblacion Barangay Hall on Sep 30, 9:00 AM.'],
                ['title' => 'Application approved', 'time' => 'Sep 9',  'delay' => '1.7s',
                 'body'  => 'Maria, your social pension application has been approved.'],
            ];

            // The life of one notice, shown in the hero. "icon" holds the SVG paths.
            $flow = [
                ['label' => 'Approved',  'icon' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>'],
                ['label' => 'Scheduled', 'icon' => '<rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
                ['label' => 'Reminded',  'icon' => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>'],
                ['label' => 'Delivered', 'icon' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>'],
            ];

            // How a notice gets sent. Edit to match your LGU's process.
            $steps = [
                ['title' => 'Add contact details', 'text' => 'Barangay staff register the senior citizen along with a mobile number.'],
                ['title' => 'Set the release',     'text' => 'The office schedules the date, venue, and amount for a program.'],
                ['title' => 'Notices go out',      'text' => 'Beneficiaries receive their schedule and reminders. Staff see who has been notified.'],
            ];

            // What the LGU can send. "icon" holds the SVG paths.
            $features = [
                ['title' => 'Application updates',
                 'text'  => 'Tell beneficiaries when an application is received, verified, or approved.',
                 'icon'  => '<rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>'],
                ['title' => 'Release schedules',
                 'text'  => 'Share the date, venue, and amount before each release.',
                 'icon'  => '<rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
                ['title' => 'Reminders',
                 'text'  => 'Send a reminder ahead of each release so no one misses it.',
                 'icon'  => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>'],
                ['title' => 'Delivery tracking',
                 'text'  => 'See who has been notified and follow up on messages that did not go through.',
                 'icon'  => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>'],
            ];
        @endphp


        {{-- =========================================================
            GOVERNMENT STRIP
        ========================================================== --}}
        <div aria-hidden="true" class="flex h-1">
            <div class="flex-1 bg-[#0038a8]"></div>
            <div class="flex-1 bg-[#ce1126]"></div>
        </div>
        <div class="bg-gray-900 text-xs text-gray-300 dark:bg-black">
            <div class="max-w-6xl mx-auto flex items-center justify-between gap-4 px-4 py-1.5 sm:px-6 lg:px-8">
                <p class="shrink-0">Republic of the Philippines</p>
                <p class="truncate text-right">{{ $lgu }}</p>
            </div>
        </div>


        {{-- =========================================================
            TOP NAV
        ========================================================== --}}
        <header class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center h-16">

                    <a href="{{ url('/') }}"
                       class="flex min-w-0 items-center gap-3 rounded-md focus:outline-none focus-visible:ring-2
                              focus-visible:ring-[#3d7a26] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800">
                        <img src="{{ asset($logo) }}" alt="" class="block h-11 w-11 shrink-0 object-contain" />
                        <span class="min-w-0 leading-tight">
                            <span class="block text-sm font-semibold text-gray-800 dark:text-gray-200 sm:text-base">
                                Subsidy Notification System
                            </span>
                            <span class="block truncate text-xs text-gray-500 dark:text-gray-400">{{ $office }}</span>
                        </span>
                    </a>

                </div>
            </div>
        </header>


        <main id="main">

            {{-- =====================================================
                HERO
            ====================================================== --}}
            <section class="relative overflow-hidden bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                {{-- Faint dot pattern that fades out toward the bottom. --}}
                <div class="pointer-events-none absolute inset-0 opacity-60 dark:opacity-20" aria-hidden="true"
                     style="background-image: radial-gradient(circle, rgb(47 107 31 / .2) 1px, transparent 1.5px); background-size: 22px 22px;
                            -webkit-mask-image: linear-gradient(to bottom, #000, transparent 85%); mask-image: linear-gradient(to bottom, #000, transparent 85%);"></div>


                {{-- Soft red glow behind the phone, so the right side never looks empty. --}}
                <div class="pointer-events-none absolute -right-24 top-1/2 h-[28rem] w-[28rem] -translate-y-1/2 rounded-full
                            bg-[#dcebd3] blur-3xl dark:bg-[#2f6b1f]/30" aria-hidden="true"></div>

                <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 lg:py-16
                            grid gap-10 lg:grid-cols-12 lg:gap-8 items-center">

                    <div class="lg:col-span-7">
                        <p class="inline-flex items-center gap-2 rounded-full bg-[#eef5ea] px-3 py-1 text-xs font-semibold
                                  uppercase tracking-wider text-[#2f6b1f] dark:bg-[#2f6b1f]/30 dark:text-[#cfe3c3]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#924337] dark:bg-[#d98b7d]" aria-hidden="true"></span>
                            Senior Citizens Subsidy
                        </p>

                        <h1 class="mt-4 text-4xl sm:text-5xl font-extrabold tracking-tight leading-[1.08] max-w-2xl
                                   text-gray-900 dark:text-white">
                            Subsidy notices that reach every senior citizen on time.
                        </h1>

                        <p class="mt-4 text-lg leading-relaxed max-w-xl text-gray-600 dark:text-gray-400">
                            Send approvals, release schedules, and reminders straight to
                            beneficiaries, and see who has been notified.
                        </p>

                        <div class="mt-7 flex flex-wrap items-center gap-3">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="{{ $btnPrimary }}">Go to dashboard</a>
                            @else
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" class="{{ $btnPrimary }}">Log in</a>
                                @endif

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="{{ $btnSecondary }}">Create an account</a>
                                @endif
                            @endauth
                        </div>

                        <div class="mt-8 rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-900/40">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Every notice, tracked from approval to delivery
                            </p>

                            <ol class="mt-5 grid grid-cols-4">
                                @foreach ($flow as $item)
                                    <li class="relative text-center">
                                        @unless ($loop->last)
                                            <span class="absolute left-1/2 top-5 h-px w-full bg-[#c9dfbd] dark:bg-[#2f6b1f]" aria-hidden="true"></span>
                                        @endunless
                                        <span class="relative mx-auto flex h-10 w-10 items-center justify-center rounded-full
                                                     bg-[#2f6b1f] text-white ring-4 ring-gray-50 dark:ring-gray-800">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75"
                                                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                {!! $item['icon'] !!}
                                            </svg>
                                        </span>
                                        <span class="mt-2 block text-xs font-semibold text-gray-700 dark:text-gray-300 sm:text-sm">{{ $item['label'] }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>

                        <p class="mt-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="3" y="11" width="18" height="10" rx="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            For authorized LGU personnel only.
                        </p>
                    </div>


                    {{-- Phone lock screen with sample notifications --}}
                    <figure class="mx-auto w-full max-w-[17.5rem] lg:col-span-5">

                        <div class="relative overflow-hidden rounded-[2.5rem] border-[10px] border-gray-900 dark:border-gray-600
                                    bg-[#1f4a12] px-4 pb-6 pt-10 shadow-xl">

                            <div class="absolute left-1/2 top-2 h-5 w-24 -translate-x-1/2 rounded-full bg-gray-900 dark:bg-gray-600"
                                 aria-hidden="true"></div>

                            <div class="text-center text-white" aria-hidden="true">
                                <p id="phone-date" class="text-sm font-medium text-[#dcebd3]">{{ now('Asia/Manila')->format('l, F j') }}</p>
                                <p id="phone-time" class="text-6xl font-semibold tracking-tight tabular-nums">{{ now('Asia/Manila')->format('g:i') }}</p>
                            </div>

                            <ul class="mt-6 space-y-2.5" aria-label="Sample notifications">
                                @foreach ($notices as $notice)
                                    <li class="notice rounded-2xl bg-white/95 p-3 shadow dark:bg-gray-800/95"
                                        style="animation-delay: {{ $notice['delay'] }}">
                                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                            <img src="{{ asset($logo) }}" alt="" class="block h-5 w-5 shrink-0 object-contain" />
                                            <span class="font-medium">Subsidy Notification</span>
                                            <span class="ml-auto">{{ $notice['time'] }}</span>
                                        </div>
                                        <p class="mt-1.5 text-sm font-semibold text-gray-900 dark:text-white">{{ $notice['title'] }}</p>
                                        <p class="mt-0.5 text-sm leading-snug text-gray-600 dark:text-gray-300">{{ $notice['body'] }}</p>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="mx-auto mt-6 h-1 w-24 rounded-full bg-white/40" aria-hidden="true"></div>
                        </div>

                        <figcaption class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">
                            Sample notifications, not real data.
                        </figcaption>

                    </figure>

                </div>
            </section>


            {{-- =====================================================
                HOW A NOTICE GETS SENT
            ====================================================== --}}
            <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 sm:pt-14">

                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    How a notice gets sent
                </h2>
                <span class="mt-2 block h-1 w-10 rounded-full bg-[#bb8f63]" aria-hidden="true"></span>

                <ol class="mt-6 grid gap-4 md:grid-cols-3">
                    @foreach ($steps as $step)
                        <li class="flex gap-4 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#2f6b1f] text-sm font-bold text-white"
                                  aria-hidden="true">{{ $loop->iteration }}</span>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $step['title'] }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $step['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>

            </section>


            {{-- =====================================================
                WHAT THE LGU CAN SEND
            ====================================================== --}}
            <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-14">

                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    What the LGU can send
                </h2>
                <span class="mt-2 block h-1 w-10 rounded-full bg-[#bb8f63]" aria-hidden="true"></span>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($features as $feature)
                        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#eef5ea] dark:bg-[#2f6b1f]/30">
                                <svg class="h-5 w-5 text-[#2f6b1f] dark:text-[#b5d5a4]" fill="none" stroke="currentColor" stroke-width="1.75"
                                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    {!! $feature['icon'] !!}
                                </svg>
                            </span>
                            <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">{{ $feature['title'] }}</h3>
                            <p class="mt-1 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $feature['text'] }}</p>
                        </div>
                    @endforeach
                </div>

            </section>

        </main>


        {{-- =========================================================
            FOOTER
        ========================================================== --}}
        <footer class="border-t border-gray-200 dark:border-gray-700">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-3 text-sm text-gray-500 dark:text-gray-400">

                <div class="flex flex-col items-center justify-between gap-2 sm:flex-row">
                    <p class="text-center sm:text-left">{{ $office }}, {{ $lgu }}</p>
                    <p>&copy; {{ now()->year }} {{ config('app.name', 'Laravel') }} &middot; v{{ app()->version() }}</p>
                </div>

                <p class="max-w-3xl text-center text-xs sm:text-left">
                    Personal information in this system is protected under the Data Privacy Act of 2012
                    (Republic Act No. 10173). Access is limited to authorized LGU personnel.
                </p>

            </div>
        </footer>

        {{-- Keeps the phone's date and time in sync with the real clock (Philippine time). --}}
        <script>
            (function () {
                var dateEl = document.getElementById('phone-date');
                var timeEl = document.getElementById('phone-time');
                if (!dateEl || !timeEl) return;

                var tz = 'Asia/Manila';
                var dateFmt = new Intl.DateTimeFormat('en-US', { weekday: 'long', month: 'long', day: 'numeric', timeZone: tz });
                var timeFmt = new Intl.DateTimeFormat('en-US', { hour: 'numeric', minute: '2-digit', hour12: true, timeZone: tz });

                function tick() {
                    var now = new Date();
                    dateEl.textContent = dateFmt.format(now);
                    // Lock screens show "8:00", not "8:00 AM".
                    timeEl.textContent = timeFmt.format(now).replace(/\s?[AP]M$/i, '');
                    // Re-run right at the start of the next minute.
                    setTimeout(tick, (60 - now.getSeconds()) * 1000 - now.getMilliseconds() + 50);
                }

                tick();
            })();
        </script>

    </body>
</html>