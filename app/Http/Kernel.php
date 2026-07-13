<?php

namespace FI\Http;

use FI\Http\Middleware\AfterMiddleware;
use FI\Http\Middleware\AuthenticateAdmin;
use FI\Http\Middleware\AuthenticateClientCenter;
use FI\Http\Middleware\BeforeMiddleware;
use FI\Http\Middleware\EncryptCookies;
use FI\Http\Middleware\RedirectIfAuthenticated;
use FI\Http\Middleware\SetConfigMiddleware;
use FI\Http\Middleware\TrimStrings;
use FI\Http\Middleware\TrustProxies;
use FI\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        CheckForMaintenanceMode::class,
        ValidatePostSize::class,
        TrimStrings::class,
        TrustProxies::class,
        AfterMiddleware::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            BeforeMiddleware::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'              => Authenticate::class,
        'auth.admin'        => AuthenticateAdmin::class,
        'auth.clientCenter' => AuthenticateClientCenter::class,
        'auth.basic'        => AuthenticateWithBasicAuth::class,
        'bindings'          => SubstituteBindings::class,
        'can'               => Authorize::class,
        'guest'             => RedirectIfAuthenticated::class,
        'throttle'          => ThrottleRequests::class,
    ];
}
