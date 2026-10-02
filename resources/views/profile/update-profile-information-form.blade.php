<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Profile Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update your account details, contact information and profile photo.') }}
    </x-slot>

    <x-slot name="form">

        {{-- =====================================================
             ACCOUNT SUMMARY (read-only)
        ====================================================== --}}

        <div class="col-span-6">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">

                    <div>
                        <p class="text-xs font-medium text-gray-500">{{ __('Role') }}</p>
                        <span class="mt-1 inline-flex items-center rounded-full bg-[oklch(79.2%_0.209_151.711)]/20 px-2.5 py-0.5 text-xs font-semibold text-[oklch(35%_0.12_151.711)]">
                            {{ $this->user->role_label }}
                        </span>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-500">{{ __('Status') }}</p>
                        <span @class([
                            'mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                            'bg-green-100 text-green-800' => $this->user->is_active,
                            'bg-red-100 text-red-800' => ! $this->user->is_active,
                        ])>
                            {{ $this->user->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-500">{{ __('Member since') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">
                            {{ $this->user->created_at?->format('M d, Y') ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-500">{{ __('Last login') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-900" @if($this->user->last_login_at) title="{{ $this->user->last_login_at->format('M d, Y g:i A') }}" @endif>
                            {{ $this->user->last_login_at?->diffForHumans() ?? __('Never') }}
                        </p>
                    </div>

                </div>
            </div>
        </div>


        {{-- =====================================================
             PROFILE PHOTO
        ====================================================== --}}

        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div
                x-data="{
                    photoName: null,
                    photoPreview: null,
                    dragging: false,
                    error: null,

                    readFile(file) {
                        this.error = null;

                        if (! file) return;

                        if (! ['image/jpeg','image/png','image/webp'].includes(file.type)) {
                            this.error = 'Only JPG, PNG or WEBP images are allowed.';
                            return;
                        }

                        if (file.size > 2 * 1024 * 1024) {
                            this.error = 'Image must be smaller than 2MB.';
                            return;
                        }

                        this.photoName = file.name;

                        const reader = new FileReader();
                        reader.onload = e => this.photoPreview = e.target.result;
                        reader.readAsDataURL(file);
                    },

                    drop(event) {
                        this.dragging = false;

                        const file = event.dataTransfer.files[0];
                        if (! file) return;

                        // Hand the dropped file to the real input so Livewire uploads it.
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        this.$refs.photo.files = dt.files;
                        this.$refs.photo.dispatchEvent(new Event('change', { bubbles: true }));
                    },
                }"
                class="col-span-6 sm:col-span-4"
            >

                <input type="file" id="photo" class="hidden" wire:model.live="photo" x-ref="photo" accept="image/jpeg,image/png,image/webp" x-on:change="readFile($refs.photo.files[0])" />

                <x-label for="photo" value="{{ __('Profile Photo') }}" />

                <div class="mt-2 flex items-start gap-5">

                    {{-- CURRENT / PREVIEW --}}

                    <div class="relative shrink-0">
                        <div x-show="! photoPreview">
                            <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="size-24 rounded-full object-cover ring-2 ring-gray-200">
                        </div>

                        <div x-show="photoPreview" style="display: none;">
                            <span class="block size-24 rounded-full bg-cover bg-center bg-no-repeat ring-2 ring-[oklch(45%_0.15_151.711)]" x-bind:style="'background-image: url(\'' + photoPreview + '\');'"></span>
                        </div>

                        {{-- Upload spinner --}}
                        <div wire:loading wire:target="photo" class="absolute inset-0 flex items-center justify-center rounded-full bg-black/50">
                            <svg class="size-6 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- DROP ZONE --}}

                    <div class="min-w-0 flex-1">
                        <div x-on:dragover.prevent="dragging = true" x-on:dragleave.prevent="dragging = false" x-on:drop.prevent="drop($event)" x-on:click="$refs.photo.click()" x-bind:class="dragging ? 'border-[oklch(45%_0.15_151.711)] bg-[oklch(79.2%_0.209_151.711)]/10' : 'border-gray-300 hover:border-gray-400'" class="cursor-pointer rounded-xl border-2 border-dashed p-4 text-center transition-colors">

                            <svg class="mx-auto size-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
                            </svg>

                            <p class="mt-2 text-sm text-gray-600">
                                <span class="font-semibold text-[oklch(45%_0.15_151.711)]">{{ __('Click to upload') }}</span>
                                {{ __('or drag and drop') }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500">{{ __('JPG, PNG or WEBP — max 2MB') }}</p>
                        </div>

                        <p x-show="photoName" x-text="photoName" style="display: none;" class="mt-2 truncate text-xs text-gray-500"></p>
                        <p x-show="error" x-text="error" style="display: none;" class="mt-2 text-sm text-red-600"></p>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <x-secondary-button type="button" x-on:click.prevent="$refs.photo.click()">
                                {{ __('Select A New Photo') }}
                            </x-secondary-button>

                            @if ($this->user->profile_photo_path)
                                <x-secondary-button type="button" wire:click="deleteProfilePhoto" wire:confirm="{{ __('Remove your profile photo?') }}">
                                    {{ __('Remove Photo') }}
                                </x-secondary-button>
                            @endif
                        </div>
                    </div>

                </div>

                <x-input-error for="photo" class="mt-2" />
            </div>
        @endif


        {{-- =====================================================
             PERSONAL DETAILS
        ====================================================== --}}

        <div class="col-span-6">
            <h3 class="border-b border-gray-200 pb-2 text-sm font-semibold text-gray-900">
                {{ __('Personal Details') }}
            </h3>
        </div>

        <div class="col-span-6 sm:col-span-3">
            <x-label for="name" value="{{ __('Full Name') }}" />
            <x-input id="name" type="text" class="mt-1 block w-full" wire:model="state.name" required autocomplete="name" />
            <x-input-error for="name" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-3">
            <x-label for="contact_number" value="{{ __('Contact Number') }}" />
            <x-input id="contact_number" type="tel" class="mt-1 block w-full" wire:model="state.contact_number" placeholder="09171234567" autocomplete="tel" />
            <x-input-error for="contact_number" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="{{ __('Email Address') }}" />
            <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required autocomplete="username" />
            <x-input-error for="email" class="mt-2" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                <p class="mt-2 text-sm">
                    {{ __('Your email address is unverified.') }}

                    <button type="button" class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-[oklch(45%_0.15_151.711)] focus:ring-offset-2" wire:click.prevent="sendEmailVerification">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if ($this->verificationLinkSent)
                    <p class="mt-2 text-sm font-medium text-green-600">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            @endif
        </div>


        {{-- =====================================================
             WORK DETAILS
        ====================================================== --}}

        <div class="col-span-6">
            <h3 class="border-b border-gray-200 pb-2 text-sm font-semibold text-gray-900">
                {{ __('Work Details') }}
            </h3>
        </div>

        <div class="col-span-6 sm:col-span-2">
            <x-label for="employee_id" value="{{ __('Employee ID') }}" />
            <x-input id="employee_id" type="text" class="mt-1 block w-full" wire:model="state.employee_id" placeholder="EMP-0001" />
            <x-input-error for="employee_id" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-2">
            <x-label for="position" value="{{ __('Position') }}" />
            <x-input id="position" type="text" class="mt-1 block w-full" wire:model="state.position" placeholder="{{ __('e.g. Social Welfare Officer') }}" />
            <x-input-error for="position" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-2">
            <x-label for="department" value="{{ __('Office / Department') }}" />
            <x-input id="department" type="text" class="mt-1 block w-full" wire:model="state.department" placeholder="{{ __('e.g. OSCA') }}" />
            <x-input-error for="department" class="mt-2" />
        </div>

    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>