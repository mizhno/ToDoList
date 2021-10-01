<?php

declare(strict_types=1);

namespace Library;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Library\Feature\Task\Add\Api\AddTaskHandler;
use Library\Feature\Task\Delete\All\Api\DeleteTaskHandler;
use Library\Feature\Task\Delete\Finished\Api\DeleteFinishedTaskHandler;
use Library\Feature\Task\Rename\Api\RenameTaskHandler;
use Library\Feature\Task\Toggle\Api\ToggleTaskHandler;
use Library\Feature\TaskList\Add\Api\AddTaskListHandler;
use Library\Feature\User\Authenticate\Api\AutheticationMiddleware;
use Library\Feature\User\Authenticate\Api\UserAuthenticationHandler;
use Library\Feature\User\Register\Api\UserRegistrationHandler;
use Library\Infrastructure\Config\MissedValueException;
use Library\Infrastructure\Database\Connection;
use Library\Infrastructure\Database\ConnectionFactory;
use Library\Infrastructure\Http\UnpackMiddleware;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteCollectorProxy;
use function DI\autowire;
use function DI\factory;

$container = (static function(): ContainerInterface {
    return (new ContainerBuilder())
        ->addDefinitions([
            // Configuration
            'database' => [
                'dsn' => sprintf(
                    'pgsql:host=%s;port=%d;dbname=%s',
                    getenv('DATABASE_HOST') ?: throw new MissedValueException('dsn.host'),
                    getenv('DATABASE_PORT') ?: throw new MissedValueException('dsn.port'),
                    getenv('DATABASE_DB') ?: throw new MissedValueException('dsn.db'),
                ),
                'user' => getenv('DATABASE_USER') ?: throw new MissedValueException('database.user'),
                'password' => getenv('DATABASE_PASSWORD') ?: throw new MissedValueException('database.password'),
            ],
            // Factories
            Connection::class => factory(ConnectionFactory::class),
            // Handlers
            AddTaskHandler::class => autowire(),
            DeleteFinishedTaskHandler::class => autowire(),
            RenameTaskHandler::class => autowire(),
            ToggleTaskHandler::class => autowire(),
            UserRegistrationHandler::class => autowire(),
            UserAuthenticationHandler::class => autowire(),
            AddTaskListHandler::class => autowire(),
            // Middlewares
            AutheticationMiddleware::class => autowire(),
            UnpackMiddleware::class => autowire(),
        ])
        ->useAutowiring(true)
        ->useAnnotations(false)
        ->enableCompilation('/tmp/.php-di-http')
        ->build();
})();

$app = Bridge::create($container);

$app->get('/', static fn(): ResponseInterface => new Response(status: 200, body: Stream::create('Hi there!')));

$app->group('', function(RouteCollectorProxy $group): void {
    $group->post('/task', AddTaskHandler::class);
    $group->delete('/task/{guid}', DeleteTaskHandler::class);
    $group->delete('/task/finished/{guid}', DeleteFinishedTaskHandler::class);
    $group->put('/task/{guid}', RenameTaskHandler::class);
    $group->patch('/task/{guid}', ToggleTaskHandler::class);
    $group->post('/tasklist', AddTaskListHandler::class);
})->add(AutheticationMiddleware::class)
    ->add(UnpackMiddleware::class);

$app->post('/register', UserRegistrationHandler::class)->add(UnpackMiddleware::class);

$app->post('/authenticate', UserAuthenticationHandler::class)->add(UnpackMiddleware::class);

$app->run();
