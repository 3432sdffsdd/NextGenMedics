<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class AuthController extends BaseController
{
    public function __construct(private AuthService $auth) {}

    public function login(Request $request): void
    {
        try {
            $data = $this->validate($request, [
                'email'    => 'required|email',
                'password' => 'required|min:6',
            ]);
            if (!$data) return;

            $result = $this->auth->login($data['email'], $data['password'], $request->ip());
            if (!$result) {
                Response::error('Invalid credentials', 401);
                return;
            }
            if (isset($result['error'])) {
                Response::error($result['error'], 403);
                return;
            }

            Response::json($result);
        } catch (\Throwable $e) {
            Response::error('Login error: ' . $e->getMessage(), 500);
        }
    }

    public function me(Request $request): void
    {
        $user = $request->user();
        unset($user['password']);
        Response::success([
            'id'         => (int) $user['id'],
            'username'   => $user['username'],
            'email'      => $user['email'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'full_name'  => $user['first_name'] . ' ' . $user['last_name'],
            'role'       => $user['role'],
            'avatar'     => $user['avatar'],
            'phone'      => $user['phone'] ?? null,
        ]);
    }

    public function refresh(Request $request): void
    {
        $data = $this->validate($request, ['refresh_token' => 'required']);
        if (!$data) return;

        $tokens = $this->auth->refresh($data['refresh_token']);
        if (!$tokens) {
            Response::error('Invalid refresh token', 401);
            return;
        }

        Response::success([
            'token'         => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'expires_in'    => 3600,
        ]);
    }

    public function logout(Request $request): void
    {
        $data = $request->body();
        $this->auth->logout($request->userId(), $data['refresh_token'] ?? null, $request->ip());
        Response::success(null, 'Logged out successfully');
    }

    public function changePassword(Request $request): void
    {
        $data = $this->validate($request, [
            'current_password' => 'required|min:6',
            'new_password'     => 'required|min:8',
        ]);
        if (!$data) return;

        $result = $this->auth->changePassword(
            $request->userId(),
            $data['current_password'],
            $data['new_password']
        );

        if ($result !== true) {
            Response::error($result, 422);
            return;
        }

        Response::success(null, 'Password changed successfully');
    }

    public function forgotPassword(Request $request): void
    {
        $data = $this->validate($request, ['email' => 'required|email']);
        if (!$data) return;

        $this->auth->forgotPassword($data['email']);
        Response::success(null, 'If the email exists, a reset link has been sent');
    }

    public function resetPassword(Request $request): void
    {
        $data = $this->validate($request, [
            'token'        => 'required',
            'new_password' => 'required|min:8',
        ]);
        if (!$data) return;

        if (!$this->auth->resetPassword($data['token'], $data['new_password'])) {
            Response::error('Invalid or expired reset token', 422);
            return;
        }

        Response::success(null, 'Password reset successfully');
    }
}
