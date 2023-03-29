<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

use App\Features\Shared\Configuration\BitPayConfigurationFactoryInterface;
use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use App\Features\Shared\StringConverter;
use App\Infrastructure\Laravel\Handler;
use App\Infrastructure\Laravel\Repository\EloquentInvoiceRepository;
use App\Models\Invoice\InvoiceRepositoryInterface;
use Illuminate\Contracts\Foundation\Application;

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Infrastructure\Laravel\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    Handler::class
);

$app->singleton(
    InvoiceRepositoryInterface::class,
    EloquentInvoiceRepository::class
);

$app->singleton(
    StringConverter::class,
    \App\Infrastructure\StringConverter::class
);

$app->singleton(
    BitPayConfigurationInterface::class, static function (Application $application) {
        /** @var BitPayConfigurationFactoryInterface $factory */
        $factory = $application->make(BitPayConfigurationFactoryInterface::class);

        return $factory->create();
    }
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
