<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\UserRepository;
use App\Services\UserService;

class UserController extends BaseController
{
    public function __construct(
        private UserRepository $users,
        private UserService $userService
    ) {}

    public function index(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;

        $role = $request->param('role');
        $result = $this->users->listByRole($role, $request->page(), $request->perPage(), $request->query('search'));
        Response::paginated($result['items'], $result['total'], $request->page(), $request->perPage());
    }

    public function store(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;

        $data = $this->validate($request, [
            'username'   => 'required|min:3',
            'email'      => 'required|email',
            'first_name' => 'required|min:2',
            'last_name'  => 'required|min:2',
        ]);
        if (!$data) return;

        $role = $request->param('role');
        try {
            $user = $this->userService->createUser(
                array_merge($request->body(), $data),
                $role,
                $request->userId(),
                $request->ip()
            );
            Response::success($user, 'User created', 201);
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    public function show(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;

        $user = $this->users->findById((int) $request->param('id'));
        if (!$user) {
            Response::error('Not found', 404);
            return;
        }
        unset($user['password']);
        Response::success($user);
    }

    public function update(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;

        $id = (int) $request->param('id');
        $data = $request->body();
        unset($data['password'], $data['role_id']);
        $this->users->update($id, $data);
        Response::success($this->users->findById($id), 'User updated');
    }

    public function suspend(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;
        $this->users->update((int) $request->param('id'), ['status' => 'suspended']);
        Response::success(null, 'User suspended');
    }

    public function activate(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;
        $this->users->update((int) $request->param('id'), ['status' => 'active']);
        Response::success(null, 'User activated');
    }

    public function resetPassword(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;

        $password = $this->userService->resetUserPassword(
            (int) $request->param('id'),
            $request->userId(),
            $request->ip()
        );
        Response::success(['password' => $password], 'Password reset');
    }

    public function destroy(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;
        $this->users->softDelete((int) $request->param('id'));
        Response::success(null, 'User deleted');
    }

    public function updateProfile(Request $request): void
    {
        $data = $request->body();
        unset($data['password'], $data['role_id'], $data['status']);
        $this->users->update($request->userId(), $data);
        $user = $this->users->findById($request->userId());
        unset($user['password']);
        Response::success($user, 'Profile updated');
    }

    public function roles(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;
        Response::success($this->users->getAllRoles());
    }
}
