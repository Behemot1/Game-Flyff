<?php

namespace Azuriom\Plugin\Flyff\Providers;

use Azuriom\Models\Ban;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Azuriom\Plugin\Flyff\Games\FlyffGame;
use Azuriom\Providers\GameServiceProvider;
use Azuriom\Plugin\Flyff\Observers\BanObserver;
use Azuriom\Plugin\Flyff\Middleware\CheckCharsShop;
use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Plugin\Flyff\View\Composers\FlyffAdminDashboardComposer;

class FlyffServiceProvider extends BasePluginServiceProvider
{
    /**
     * The plugin's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        // \Azuriom\Plugin\Flyff\Middleware\ExampleMiddleware::class,
    ];

    /**
     * The plugin's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [];

    /**
     * The plugin's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // 'example' => \Azuriom\Plugin\Flyff\Middleware\ExampleRouteMiddleware::class,
    ];

    /**
     * The policy mappings for this plugin.
     *
     * @var array
     */
    protected $policies = [
        // User::class => UserPolicy::class,
    ];

    /**
     * Register any plugin services.
     *
     * @return void
     */
    public function register()
    {
        require_once __DIR__.'/../../vendor/autoload.php';

        config([
            'database.connections.sqlsrv.host' => env('SQLSRV_HOST'),
            'database.connections.sqlsrv.port' => env('SQLSRV_PORT'),
            'database.connections.sqlsrv.username' => env('SQLSRV_USERNAME'),
            'database.connections.sqlsrv.password' => env('SQLSRV_PASSWORD'),
            'database.connections.sqlsrv.database' => 'ACCOUNT_DBF'
        ]);

        $this->registerMiddlewares();
        GameServiceProvider::registerGames(['flyff'=> FlyffGame::class]);
        //
    }

    /**
     * Bootstrap any plugin services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->registerPolicies();

        $this->loadViews();

        $this->loadTranslations();

        $this->loadMigrations();

        $this->registerRouteDescriptions();

        $this->registerAdminNavigation();

        $this->registerUserNavigation();

        View::composer('admin.dashboard', FlyffAdminDashboardComposer::class);

        Event::listen(function (Registered $event) {
            $event->user->access_token = Str::random(128);
            $event->user->save();
        });

        Ban::observe(BanObserver::class);
        
        $this->app['router']->pushMiddlewareToGroup('web', \Azuriom\Plugin\Flyff\Middleware\InGameShop::class);
        //
    }

    /**
     * Returns the routes that should be able to be added to the navbar.
     *
     * @return array
     */
    protected function routeDescriptions()
    {
        return [
            'flyff.guilds.index' => 'flyff::messages.guilds',
            'flyff.characters.index' => 'flyff::messages.characters'
        ];
    }

    /**
     * Return the admin navigations routes to register in the dashboard.
     *
     * @return array
     */
    protected function adminNavigation()
    {
        return [
            'flyff' => [
                'name' => 'Flyff',
                'type' => 'dropdown',
                'icon' => 'fas fa-gamepad',
                'route' => 'flyff.admin.*',
                'items' => [
                    'flyff.admin.index' => 'Comptes',
                    'flyff.admin.mails' => 'Mails',
                    'flyff.admin.trades.index' => 'Trades',
                    'flyff.admin.lookup' => 'Item Lookup',
                ],
            ],
        ];
    }

    /**
     * Return the user navigations routes to register in the user menu.
     *
     * @return array
     */
    protected function userNavigation()
    {
        return [
            'flyff' => [
                'route' => 'flyff.accounts.index',
                'name' => 'In-Game Accounts',
            ]
        ];
    }
}
