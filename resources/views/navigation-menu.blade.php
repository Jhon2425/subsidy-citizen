<nav class="sc-navbar sticky top-0 z-40">

    <div
        class="h-full
               px-6
               flex
               items-center
               justify-between"
    >

        {{-- MOBILE MENU --}}

        <button
            @click="mobileSidebarOpen = !mobileSidebarOpen"
            class="lg:hidden
                   p-2
                   rounded-lg
                   text-white
                   hover:bg-white/10"
        >

            <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                />

            </svg>

        </button>


        {{-- PAGE TITLE --}}

        <div class="hidden lg:block">

            @if (isset($header))

                <h2
                    class="text-lg
                           font-semibold
                           text-white"
                >
                    {{ $header }}
                </h2>

            @endif

        </div>


        {{-- PROFILE --}}

        <div class="ml-auto">

            <x-dropdown align="right" width="48">

                <x-slot name="trigger">

                    <button
                        type="button"
                        class="flex
                               items-center
                               gap-3
                               px-2
                               py-2
                               rounded-xl
                               hover:bg-white/10
                               focus:outline-none"
                    >

                        {{-- AVATAR --}}

                        <div
                            class="h-11
                                   w-11
                                   rounded-full
                                   bg-white
                                   flex
                                   items-center
                                   justify-center
                                   font-bold
                                   text-[oklch(45%_0.15_151.711)]"
                        >
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>


                        {{-- NAME --}}

                        <div
                            class="hidden sm:block text-left"
                        >

                            <p
                                class="text-sm
                                       font-semibold
                                       text-white"
                            >
                                {{ Auth::user()->name }}
                            </p>

                            <p
                                class="text-xs
                                       text-white/70"
                            >
                                Administrator
                            </p>

                        </div>


                        {{-- ARROW --}}

                        <svg
                            class="w-4 h-4 text-white/70"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />

                        </svg>

                    </button>

                </x-slot>


                {{-- DROPDOWN CONTENT --}}

                <x-slot name="content">

                    <div class="px-4 py-3 border-b border-gray-100">

                        <p class="text-sm font-semibold text-gray-900">
                            {{ Auth::user()->name }}
                        </p>

                        <p class="text-xs text-gray-500 mt-1">
                            {{ Auth::user()->email }}
                        </p>

                    </div>


                    <x-dropdown-link
                        href="{{ route('profile.show') }}"
                    >
                        Profile
                    </x-dropdown-link>


                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >

                        @csrf

                        <x-dropdown-link
                            href="{{ route('logout') }}"
                            onclick="
                                event.preventDefault();
                                this.closest('form').submit();
                            "
                            class="text-red-600 hover:bg-red-50"
                        >
                            Log Out
                        </x-dropdown-link>

                    </form>

                </x-slot>

            </x-dropdown>

        </div>

    </div>

</nav>