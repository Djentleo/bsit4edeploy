<?php


namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Http\Requests\LoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // Fix: allow login with username or email (override default validation)
        app()->resolving(LoginRequest::class, function ($request, $app) {
            // If 'login' is present, set both 'email' and 'username' to its value for validation
            if ($request->has('login')) {
                $login = $request->input('login');
                $request->merge([
                    'email' => $login,
                    'username' => $login,
                ]);
            }
        });

        // Allow login with username or email
        Fortify::authenticateUsing(function (Request $request) {
            $login = $request->input('login');
            $user = \App\Models\User::where('username', $login)
                ->orWhere('email', $login)
                ->first();
            if ($user && \Illuminate\Support\Facades\Hash::check($request->input('password'), $user->password)) {
                // Block login if user is not active
                if ($user->status !== 'active') {
                    return null;
                }
                return $user;
            }
            return null;
        });

        // Update rate limiter to use 'login' field
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input('login')) . '|' . $request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
