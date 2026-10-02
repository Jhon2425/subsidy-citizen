<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],

            'employee_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users')->ignore($user->id),
            ],

            'position' => ['nullable', 'string', 'max:100'],

            'department' => ['nullable', 'string', 'max:100'],

            // PH mobile: 09171234567 or +639171234567
            'contact_number' => [
                'nullable',
                'string',
                'regex:/^(09|\+639)\d{9}$/',
            ],

            'photo' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'employee_id.unique' => 'That employee ID is already assigned to another account.',
            'contact_number.regex' => 'Enter a valid PH mobile number (e.g. 09171234567).',
            'photo.max' => 'The photo must not be larger than 2MB.',
            'photo.mimes' => 'The photo must be a JPG, PNG or WEBP image.',
        ], [
            'employee_id' => 'employee ID',
            'contact_number' => 'contact number',
            'department' => 'office / department',
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill(array_merge([
                'name' => $input['name'],
                'email' => $input['email'],
            ], $this->profileFields($input)))->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill(array_merge([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ], $this->profileFields($input)))->save();

        $user->sendEmailVerificationNotification();
    }

    /**
     * Normalise the optional profile fields.
     *
     * Blank strings are stored as null so the unique rule on
     * employee_id doesn't collide across empty values.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, string|null>
     */
    protected function profileFields(array $input): array
    {
        return [
            'employee_id' => $this->nullIfBlank($input['employee_id'] ?? null),
            'position' => $this->nullIfBlank($input['position'] ?? null),
            'department' => $this->nullIfBlank($input['department'] ?? null),
            'contact_number' => $this->normalisePhone($input['contact_number'] ?? null),
        ];
    }

    /**
     * Trim a value and return null when nothing is left.
     */
    protected function nullIfBlank(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Store PH mobile numbers in a single 09XXXXXXXXX format.
     */
    protected function normalisePhone(?string $value): ?string
    {
        $value = $this->nullIfBlank($value);

        if ($value === null) {
            return null;
        }

        // Strip spaces and dashes users often paste in.
        $value = preg_replace('/[\s\-()]/', '', $value);

        // +639171234567 -> 09171234567
        if (str_starts_with($value, '+63')) {
            $value = '0' . substr($value, 3);
        }

        return $value;
    }
}