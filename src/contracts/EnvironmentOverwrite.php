<?php
namespace Megaads\Localization\Contracts;

use Dotenv;

class EnvironmentOverwrite {

    public function handle($app) {
        $envFile = $this->urlLocalizeDetection();
        $this->loadNewLocaleEnv($app, $envFile);
    }


    private function urlLocalizeDetection() {
        $envFile = '.env';
        $tmpEnvFile = '';
        $uri = array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : '';
        preg_match('/\/([A-Za-z]+)(\/*\-*)/', $uri, $env);
        if (isset($env[0]) && strpos($env[0], '-') === false) {
            if (count($env) == 3) {
                $tmpEnvFile = ".{$env[1]}.env";
            }
        }
        if ($tmpEnvFile != '' && file_exists(base_path($tmpEnvFile))) {
            $envFile = $tmpEnvFile;
        }
        return $envFile;
    }

    private function loadNewLocaleEnv($app, $envFile) {
        $app->detectEnvironment(function () use ($envFile) {
            $dotenv = new Dotenv\Dotenv(app()->environmentPath(), $envFile);
            $dotenv->overload();
            // $lang = env('APP_LOCALE_LANG', 'en_US');
            // putenv('LANG=' . $lang);
            // setlocale(LC_ALL, $lang);
            // $domain = 'messages';
            // $localedir = dirname(__FILE__) . '/../' . env('APP_LOCALES_DIR', 'resources/lang');
            // bindtextdomain($domain, $localedir);
            // textdomain($domain);
        });
    }
}