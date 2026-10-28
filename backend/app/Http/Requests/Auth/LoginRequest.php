<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 7200; // 2 hours

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Auth User model
     */
    public function authenticate(): User
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            'email'    => trim((string) $this->input('email')),
            'password' => (string) $this->input('password'),
        ];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), self::LOCKOUT_SECONDS);

            // check bị soft-delete
            $user = User::withTrashed()->where('email', $credentials['email'])->first();

            if ($user && $user->trashed()) {
                throw ValidationException::withMessages([
                    'email' => [trans('auth.blocked') ?: 'Tài khoản đã bị vô hiệu hóa.'],
                ]);
            }

            throw ValidationException::withMessages([
                'email' => [trans('auth.failed', [
                    'remaining' => RateLimiter::remaining($this->throttleKey(), self::MAX_ATTEMPTS),
                ])],
            ]);
        }

        // Login success
        RateLimiter::clear($this->throttleKey());

        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => [trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])],
        ]);
    }

    public function throttleKey(): string
    {
        return 'login|' . Str::transliterate(Str::lower(trim((string) $this->input('email'))));
    }
}
