<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('announcements.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Announcement</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3">
                <p class="text-sm font-medium text-red-800">Please fix the following before continuing:</p>
                <ul class="mt-1 text-xs text-red-700 list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('announcements.store') }}" method="POST" class="bg-white shadow-sm ring-1 ring-gray-100 rounded-2xl overflow-hidden">
            @csrf

            <div class="px-6 sm:px-8 py-6 space-y-6">

                {{-- Title --}}
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}"
                           placeholder="e.g. Free Flu Vaccination Drive"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm
                                  focus:border-[oklch(45%_0.15_151.711)] focus:ring-2 focus:ring-[oklch(45%_0.15_151.711/0.25)]
                                  @error('title') border-red-400 @enderror">
                    @error('title') <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                {{-- Message --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="message" class="block text-sm font-medium text-gray-700">
                            Message
                            <span class="text-gray-400 font-normal">(sent via SMS)</span>
                        </label>
                        <span id="charCount" class="text-xs text-gray-400 tabular-nums">0 / 300</span>
                    </div>
                    <textarea id="message" name="message" rows="4" maxlength="300"
                              placeholder="Keep it short and clear — this will be delivered as a text message."
                              oninput="document.getElementById('charCount').textContent = this.value.length + ' / 300'"
                              class="w-full rounded-lg border-gray-300 shadow-sm text-sm resize-none
                                     focus:border-[oklch(45%_0.15_151.711)] focus:ring-2 focus:ring-[oklch(45%_0.15_151.711/0.25)]
                                     @error('message') border-red-400 @enderror">{{ old('message') }}</textarea>
                    @error('message') <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                {{-- Date / Venue --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="distribution_date" class="block text-sm font-medium text-gray-700 mb-1.5">Distribution Date</label>
                        <input type="date" id="distribution_date" name="distribution_date" value="{{ old('distribution_date') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm
                                      focus:border-[oklch(45%_0.15_151.711)] focus:ring-2 focus:ring-[oklch(45%_0.15_151.711/0.25)]
                                      @error('distribution_date') border-red-400 @enderror">
                        @error('distribution_date') <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="distribution_location" class="block text-sm font-medium text-gray-700 mb-1.5">Venue</label>
                        <input type="text" id="distribution_location" name="distribution_location" value="{{ old('distribution_location') }}"
                               placeholder="e.g. Barangay Hall"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm
                                      focus:border-[oklch(45%_0.15_151.711)] focus:ring-2 focus:ring-[oklch(45%_0.15_151.711/0.25)]
                                      @error('distribution_location') border-red-400 @enderror">
                        @error('distribution_location') <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Recipient notice --}}
                <div class="flex items-start gap-2.5 rounded-lg bg-[oklch(45%_0.15_151.711/0.06)] px-4 py-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-0.5 text-[oklch(45%_0.15_151.711)] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        This will be sent to <span class="font-semibold text-gray-800">{{ $recipientsCount }}</span>
                        senior citizen(s) with a mobile number on file.
                    </p>
                </div>
            </div>

            {{-- Footer actions --}}
            <div class="flex items-center justify-end gap-3 bg-gray-50 px-6 sm:px-8 py-4 border-t border-gray-100">
                <button type="submit" name="send_now" value="0"
                        class="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 bg-white
                               hover:bg-gray-100 transition">
                    Save as Draft
                </button>
                <button type="submit" name="send_now" value="1"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white
                               bg-[oklch(45%_0.15_151.711)] hover:opacity-90 transition shadow-sm"
                        onclick="return confirm('Send SMS to all {{ $recipientsCount }} senior citizen(s) now?')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Save &amp; Send Now
                </button>
            </div>
        </form>
    </div>
</x-app-layout>