<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        {{ config('app.name', 'Senior Citizen Subsidy Management') }}
    </title>


    {{-- =========================================================
        FONTS
    ========================================================== --}}

    <link rel="preconnect" href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
        rel="stylesheet"
    >


    {{-- =========================================================
        SCRIPTS
    ========================================================== --}}

    @viteReactRefresh
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @livewireStyles


    {{-- =========================================================
        SHELL TRANSITIONS

        Sidebar width, main margin and the toggle button all
        share one duration and one easing curve. If they differ
        even slightly, the sidebar edge and the content edge
        separate and rejoin — which reads as rubber-banding.
    ========================================================== --}}

    <style>
        [x-cloak] { display: none !important; }

        .shell-sidebar {
            transition:
                width     300ms cubic-bezier(0.4, 0, 0.2, 1),
                transform 300ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        .shell-main {
            transition: margin-left 300ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        .shell-toggle {
            transition:
                transform        300ms cubic-bezier(0.4, 0, 0.2, 1),
                background-color 150ms ease,
                color            150ms ease;
        }

        .shell-label {
            transition: opacity 200ms ease;
        }

        @media (prefers-reduced-motion: reduce) {
            .shell-sidebar,
            .shell-main,
            .shell-toggle,
            .shell-label {
                transition-duration: 0.01ms !important;
            }
        }
    </style>

</head>


<body class="font-sans antialiased bg-gray-50">

    <x-banner />


    {{-- =========================================================
        APPLICATION WRAPPER
    ========================================================== --}}

    <div
        x-data="{
            sidebarCollapsed: false,
            mobileSidebarOpen: false,

            init() {
                this.sidebarCollapsed =
                    localStorage.getItem('sidebarCollapsed') === 'true';

                this.$watch('sidebarCollapsed', value => {
                    localStorage.setItem('sidebarCollapsed', value);
                });

                this.$watch('mobileSidebarOpen', value => {
                    document.body.classList.toggle('overflow-hidden', value);
                });
            },
        }"

        x-cloak

        @keydown.escape.window="mobileSidebarOpen = false"

        @resize.window.debounce.150ms="
            if (window.innerWidth >= 1024) mobileSidebarOpen = false
        "

        class="min-h-screen"
    >


        {{-- =====================================================
            MOBILE BACKDROP
        ====================================================== --}}

        <div
            x-show="mobileSidebarOpen"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"

            @click="mobileSidebarOpen = false"

            class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"

            aria-hidden="true"
        ></div>


        {{-- =====================================================
            SIDEBAR
        ====================================================== --}}

        <x-sidebar />


        {{-- =====================================================
            MAIN CONTENT AREA
        ====================================================== --}}

        <div
            class="shell-main min-h-screen"

            :class="
                sidebarCollapsed
                    ? 'lg:ml-20'
                    : 'lg:ml-72'
            "
        >


            {{-- =================================================
                NAVIGATION
            ================================================== --}}

            @livewire('navigation-menu')


            {{-- =================================================
                PAGE CONTENT
            ================================================== --}}

            <main class="min-w-0 p-6">

                {{ $slot }}

            </main>


        </div>

    </div>


    {{-- =========================================================
        MODALS
    ========================================================== --}}

    @stack('modals')


    {{-- =========================================================
        LIVEWIRE
    ========================================================== --}}

    @livewireScripts

</body>

</html>