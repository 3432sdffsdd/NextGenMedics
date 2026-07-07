<?php
namespace App\Services;

use App\Helpers\PasswordHelper;
use App\Helpers\SlugHelper;
use App\Repositories\ActivityLogRepository;
use App\Repositories\CourseRepository;
use App\Repositories\UserRepository;

class UserService
{
    public function __construct(
        private UserRepository $users,
        private CourseRepository $courses,
        private NotificationService $notifier,
        private ActivityLogRepository $activityLog
    ) {}

    public function createUser(array $data, string $roleSlug, int $createdBy, string $ip): array
    {
        $roleId = $this->users->getRoleId($roleSlug);
        if (!$roleId) {
            throw new \InvalidArgumentException('Invalid role');
        }

        if ($this->users->findByEmail($data['email'])) {
            throw new \InvalidArgumentException('Email already exists');
        }

        if ($this->users->findByUsername($data['username'])) {
            throw new \InvalidArgumentException('Username already exists');
        }

        $password = $data['password'] ?? PasswordHelper::generate();
        $userId = $this->users->create([
            'role_id'    => $roleId,
            'username'   => $data['username'],
            'email'      => $data['email'],
            'password'   => PasswordHelper::hash($password),
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'phone'      => $data['phone'] ?? null,
            'status'     => $data['status'] ?? 'active',
        ]);

        $this->activityLog->log($createdBy, 'create_user', 'user', $userId, "Created {$roleSlug}: {$data['email']}", $ip);
        $welcomeMsg = empty($data['password'])
            ? "Your account has been created. Your temporary password is: {$password}"
            : 'Your account has been created.';
        $this->notifier->notify($userId, 'welcome', 'Welcome to NextGen Medics', $welcomeMsg);

        $user = $this->users->findById($userId);
        unset($user['password']);
        $user['generated_password'] = empty($data['password']) ? $password : null;

        return $user;
    }

    public function resetUserPassword(int $userId, int $adminId, string $ip): string
    {
        $password = PasswordHelper::generate();
        $this->users->update($userId, ['password' => PasswordHelper::hash($password)]);
        $this->activityLog->log($adminId, 'reset_password', 'user', $userId, 'Password reset by admin', $ip);
        return $password;
    }
}
