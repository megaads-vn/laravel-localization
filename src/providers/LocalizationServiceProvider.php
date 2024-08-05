<?php

namespace MegaAds\Localization\Providers;

use Illuminate\Support\ServiceProvider;
use Megaads\Localization\Commands\GenerateLanguageCommand;
use Megaads\Localization\Commands\PublishConfigCommand;
use Megaads\Localization\Middlewares\LocalizationAuthMiddleware;
use Illuminate\Routing\Router;

class LocalizationServiceProvider extends ServiceProvider {

    protected $basePath = __DIR__ . '/../../';
    protected $commandClasses = [
        GenerateLanguageCommand::class,
        PublishConfigCommand::class
    ];

    public function __construct($app)
    {
        parent::__construct($app);
        $this->basePath = base_path('vendor/megaads/laravel-localization/src/');
    }

    public function boot(Router $router)
    {   
        $framework = $this->checkFrameWork();
        if (!empty($framework) && $framework['key'] == 'laravel/framework' && $framework['version'] >= 54 ) {
            $router = $this->app['router'];
            $router->aliasMiddleware('auth.localizaion-lang', LocalizationAuthMiddleware::class);
        } else {
            $router->middleware('auth.localizaion-lang', 'Megaads\Localization\Middlewares\LocalizationAuthMiddleware');
        }

        if (!empty($framework) && $framework['key'] == 'laravel/framework' && $framework['version'] >= 52 ) {
            include $this->basePath . 'routes/web.php';
        } else {  
            if ( method_exists($this, 'routesAreCached') ) {
                if (!$this->app->routesAreCached()) {
                    include $this->basePath . 'routes/web.php';
                }
            }
        }


        $this->loadViewsFrom($this->basePath . '/resources/views', 'localization');
        $this->publishResources();
    }

    public function register()
    {
        $this->commands($this->commandClasses);
        $this->publishConfig();
    }

    /**
 * @return void
 */
    private function publishConfig()
    {
        if (function_exists('config_path')) {
            $path = $this->basePath . 'config/localization.php';
            $this->publishes([$path => config_path('localization.php')], 'config');
        }
    }

    private function publishResources() {
        $this->publishes([
            $this->basePath . 'resources/assets/' => public_path('vendor/localization/assets'),
        ], 'assets');
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function checkFrameWork() {
        $findFrameWork = ['laravel/framework','laravel/lumen-framework'];
        $basePath = base_path();
        $composerFile = $basePath . '/composer.json';
        $frameworkResult = [];
        if (file_exists($composerFile)) {
            $composerObj = json_decode(file_get_contents($composerFile), true);
            if ( !empty($composerObj['require']) ) {
                foreach ($composerObj['require'] as $key => $value) {
                    if ( in_array($key, $findFrameWork) ) {
                        $version = $value;
                        $version = str_replace('*', '',$version);
                        $version = preg_replace('/\./', '', $version);
                        $frameworkResult = ['key' => $key, 'version' => (int) $version];
                        break;
                    }
                }
            }
        }
        return $frameworkResult;
    }
}