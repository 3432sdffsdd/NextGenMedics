<?php
namespace App\Core;

use App\Middleware\AuthMiddleware;
use App\Middleware\CorsMiddleware;
use App\Middleware\RateLimitMiddleware;

class Application
{
    private Container $container;
    private Router $router;

    public function __construct()
    {
        $this->loadEnv();
        $this->container = new Container();
        $this->router = new Router();
        $this->registerServices();
    }

    public function run(): void
    {
        $request = new Request();
        $router = $this->router;
        require __DIR__ . '/../../routes/api.php';
        $this->router->dispatch($request, $this->container);
    }

    private function loadEnv(): void
    {
        $envFile = __DIR__ . '/../../.env';
        if (!file_exists($envFile)) {
            return;
        }
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            // Always prefer .env file (shared hosts may expose empty getenv() values).
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }

    private function registerServices(): void
    {
        $this->container->singleton(Jwt::class, fn() => new Jwt());
        $this->container->singleton(Database::class, fn() => Database::getConnection());

        $repos = [
            'App\Repositories\UserRepository',
            'App\Repositories\AuthRepository',
            'App\Repositories\CourseRepository',
            'App\Repositories\ContentRepository',
            'App\Repositories\AssignmentRepository',
            'App\Repositories\QuizRepository',
            'App\Repositories\AttendanceRepository',
            'App\Repositories\AnnouncementRepository',
            'App\Repositories\DiscussionRepository',
            'App\Repositories\NotificationRepository',
            'App\Repositories\DashboardRepository',
            'App\Repositories\PublicContentRepository',
            'App\Repositories\ActivityLogRepository',
            'App\Repositories\LiveSessionRepository',
            'App\Repositories\ClassScheduleRepository',
            'App\Repositories\ScheduleRepository',
            'App\Repositories\BatchRepository',
            'App\Repositories\AiJobRepository',
            'App\Repositories\AiContentRepository',
            'App\Repositories\FlashcardRepository',
            'App\Repositories\McqRepository',
            'App\Repositories\ChallengeRepository',
            'App\Repositories\StreakRepository',
            'App\Repositories\AttemptRepository',
            'App\Repositories\AnalyticsRepository',
            'App\Repositories\BookmarkRepository',
            'App\Repositories\MistakeRepository',
            'App\Repositories\DailyChallengeSetRepository',
            'App\Repositories\StudyPlanRepository',
            'App\Repositories\RevisionSessionRepository',
        ];

        foreach ($repos as $repo) {
            $this->container->singleton($repo, fn($c) => new $repo());
        }

        $this->container->singleton('App\Services\AuthService', function ($c) {
            return new \App\Services\AuthService(
                $c->get('App\Repositories\UserRepository'),
                $c->get('App\Repositories\AuthRepository'),
                $c->get(Jwt::class),
                $c->get('App\Repositories\ActivityLogRepository')
            );
        });

        $this->container->singleton('App\Services\UserService', function ($c) {
            return new \App\Services\UserService(
                $c->get('App\Repositories\UserRepository'),
                $c->get('App\Repositories\CourseRepository'),
                $c->get('App\Services\NotificationService'),
                $c->get('App\Repositories\ActivityLogRepository')
            );
        });

        $this->container->singleton('App\Services\CourseService', function ($c) {
            return new \App\Services\CourseService(
                $c->get('App\Repositories\CourseRepository'),
                $c->get('App\Repositories\ActivityLogRepository')
            );
        });

        $this->container->singleton('App\Services\QuizEvaluationService', function ($c) {
            return new \App\Services\QuizEvaluationService(
                $c->get('App\Repositories\QuizRepository')
            );
        });

        $this->container->singleton('App\Services\MailService', fn() => new \App\Services\MailService());

        $this->container->singleton('App\Services\NotificationService', function ($c) {
            return new \App\Services\NotificationService(
                $c->get('App\Repositories\NotificationRepository'),
                $c->get('App\Services\MailService'),
                $c->get('App\Repositories\UserRepository')
            );
        });

        $this->container->singleton('App\Services\WhatsAppService', fn() => new \App\Services\WhatsAppService());

        // ── AI Learning Assistant ──────────────────────────────
        $this->container->singleton('App\AI\AiProviderInterface', fn() => \App\AI\AiClient::fromAppConfig());

        $this->container->singleton('App\Services\TextExtractionService', fn() => new \App\Services\TextExtractionService());

        $this->container->singleton('App\Services\AiContentService', function ($c) {
            $config = require __DIR__ . '/../../config/config.php';
            return new \App\Services\AiContentService(
                $c->get('App\AI\AiProviderInterface'),
                (int) ($config['ai']['max_input_chars'] ?? 8000)
            );
        });

        $this->container->singleton('App\Services\StudyService', function ($c) {
            return new \App\Services\StudyService(
                $c->get('App\Repositories\StreakRepository'),
                $c->get('App\Services\NotificationService')
            );
        });

        $this->container->singleton('App\Services\PremiumStudyService', function ($c) {
            return new \App\Services\PremiumStudyService(
                $c->get('App\Repositories\CourseRepository'),
                $c->get('App\Repositories\McqRepository'),
                $c->get('App\Repositories\DailyChallengeSetRepository'),
                $c->get('App\Repositories\MistakeRepository'),
                $c->get('App\Repositories\StudyPlanRepository'),
                $c->get('App\Repositories\RevisionSessionRepository'),
                $c->get('App\Repositories\AnalyticsRepository'),
                $c->get('App\Repositories\AttemptRepository'),
                $c->get('App\Repositories\FlashcardRepository')
            );
        });

        $this->container->singleton('App\Services\AiGenerationService', function ($c) {
            return new \App\Services\AiGenerationService(
                $c->get('App\Services\AiContentService'),
                $c->get('App\Services\TextExtractionService'),
                $c->get('App\Repositories\AiJobRepository'),
                $c->get('App\Repositories\AiContentRepository'),
                $c->get('App\Repositories\FlashcardRepository'),
                $c->get('App\Repositories\McqRepository'),
                $c->get('App\Repositories\ContentRepository'),
                $c->get('App\Services\NotificationService')
            );
        });

        $this->container->singleton('App\Services\ScheduleService', function ($c) {
            return new \App\Services\ScheduleService(
                $c->get('App\Repositories\ScheduleRepository'),
                $c->get('App\Repositories\BatchRepository'),
                $c->get('App\Services\NotificationService')
            );
        });

        $this->container->singleton('App\Services\ClassReminderService', function ($c) {
            return new \App\Services\ClassReminderService(
                $c->get('App\Repositories\ClassScheduleRepository'),
                $c->get('App\Services\NotificationService'),
                $c->get('App\Services\WhatsAppService')
            );
        });

        $controllers = glob(__DIR__ . '/../Controllers/*.php');
        foreach ($controllers as $file) {
            $class = 'App\\Controllers\\' . basename($file, '.php');
            $this->container->singleton($class, function ($c) use ($class) {
                $ref = new \ReflectionClass($class);
                $params = [];
                foreach ($ref->getConstructor()?->getParameters() ?? [] as $param) {
                    $type = $param->getType()?->getName();
                    $params[] = $c->get($type);
                }
                return new $class(...$params);
            });
        }

        $this->container->singleton(CorsMiddleware::class, fn() => new CorsMiddleware());
        $this->container->singleton(RateLimitMiddleware::class, fn() => new RateLimitMiddleware());
        $this->container->singleton(AuthMiddleware::class, function ($c) {
            return new AuthMiddleware(
                $c->get(Jwt::class),
                $c->get('App\Repositories\UserRepository')
            );
        });
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
}
