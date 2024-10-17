<?php

namespace Megaads\LaravelLocalization\Middlewares;

use Closure;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class OverwriteLocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $currentLocale = env('APP_LOCALE');
        $response = $next($request);
        if (strpos($request->url(), 'select2') !== false) {
            return $response;
        } 
        $this->modifyResponse($request, $response);
        return $response;
    }

    /**
     * @param use Illuminate\Http\Request request
     * @param Symfony\Component\HttpFoundation\Response response
     * @return Symfony\Component\HttpFoundation\Response response
     */
    private function modifyResponse(Request $request, Response $response) {
        $acceptHeaders = [
            'text/html; charset=UTF-8',
            'text/html'
        ];
        $contentType = $response->headers->get('Content-Type');
        
        if (!empty($contentType) && !in_array($contentType, $acceptHeaders)) {
            return $response;
        } else if (!$request->ajax()) {
            $content = $response->getContent();
            $headPos = strripos($content, '</head>');
            $scripts = $this->getRendererJs([
                // asset('modules/localization/js/app-module.js'),
                asset('/vendor/localization/assets/js/ajax-prefilter.js')
            ]);
            // if (preg_match('/<script(.*?)src=((?!modules|system|google).)*$/m', $content, $matches)) {
            // $ajaxPrefilterIncluded = false;
            // if (preg_match('/<script(.*?)src=\"(.*)(jquery-3.6.0.min.js)(.*?)"(.*?)><\/script>/m', $content, $matches)
            // ) {
            //     if (isset($matches[0])) {
            //         $libPos = strripos($content, $matches[0]);
            //         $libPos = $libPos + strlen($matches[0]);
            //         if (false !== $libPos) {
            //           $content = substr($content, 0, $libPos) . '<script type="text/javascript" src="' .  ('/modules/localization/js/ajax-prefilter.js') . '?v=' . config('app.version') .'"></script>' . substr($content, $libPos);
            //           $ajaxPrefilterIncluded = true;
            //         }  
            //     }
            // }
            $bodyPos = strripos($content, '</body>');
            if (false !== $bodyPos) {
            //     if (!$ajaxPrefilterIncluded) {
            //         $scripts = $this->getRendererJs([
            //             asset('modules/localization/js/app-module.js'),
            //             asset('modules/localization/js/httprequest-interceptor.js'),
            //             asset('/modules/localization/js/ajax-prefilter.js')
            //         ]);
            //     }
                $content = substr($content, 0, $bodyPos) . $scripts . substr($content, $bodyPos);
            } 
            
            $defineJsLocale = $this->definedFetchInjectionScrtipt();
            // $styles = $this->getRendererCss([
            //     asset('modules/localization/css/app-module.css')
            // ]);
            if (false !== $headPos) {
                // $content = substr($content, 0, $headPos) . $defineJsLocale . $styles . substr($content, $headPos);
                $content = substr($content, 0, $headPos) . $defineJsLocale . substr($content, $headPos);
            }

            if (env('LOCALIZATION_REPLACE_LINK', false)) {
                $domain = "$_SERVER[HTTP_HOST]";
                $localePrefix = env('LOCALE_PREFIX', '');
                $actualLink = "https://$domain/";

                $content = preg_replace('/https:\/\/printerval.com\/' . $localePrefix . '\//i', $actualLink,$content);
                $content = preg_replace('/Printerval.com/', ucfirst($domain), $content);
                $content = preg_replace('/([a-z0-9_\.-]+)@printerval\.com/', '$1@'.$domain, $content);
            }
            
            // Update the new content and reset the content length
            $response->setContent($content);
            $response->headers->remove('Content-Length');
        }
        return $response;
    }

    /**
     * @param array files
     * @return string retval
     */
    private function getRendererJs($files = []) {
        // $retval = '<script type="text/javascript"> var locales = \'' . json_encode(Config::get('localization::module.locales')) . '\';</script>';
        $retval = '';
        if (count($files) > 0) {
            foreach ($files as $file) {
                $retval .= '<script type="text/javascript" src="' . $file . '?v=' . config('app.version') . '"></script>';
            }
        }
        return $retval;
    }

    /**
     * 
     * @param array files
     * @return string retval
     */
    private function getRendererCss($files = []) {
        $retval = '';
        if (count($files) > 0) {
            foreach ($files as $file) {
                if (str_contains($file, '/modules/localization/css/app-module.css')) {
                    $retval .= '<link rel="preload" href="' . $file . '?v=' . config('app.version') . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"><noscript><link rel="stylesheet" href="' . $file . '?v=' . config('app.version') . '" ></noscript>';
                } else {
                    $retval .= '<link rel="stylesheet" href="' . $file . '?v=' . config('app.version') . '" >';
                }
                // $retval .= '<link rel="preload" href="' . $file . '?v=' . config('app.version') . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"><noscript><link rel="stylesheet" href="' . $file . '?v=' . config('app.version') . '" ></noscript>';
            }
        }
        return $retval;
    }

    /**
     * 
     * @param
     * @return string retval
     */
    private function getRendererListSelectLanguage() {
        $retval = '';
        $listLocales = Config::get('localization::module.locales');
        if (count($listLocales) > 0) {
            $html = '<ul class="list-multilang">';
            foreach ($listLocales as $key => $text) {
                $html .= '<li>
                    <a href="/" data-lang_key="' . $key . '" class="js-multilang-link multilang-link">
                        <span class="multilang-innertext"><strong>' . $text . '</strong></span>
                        <img src="/images/'. $key .'.png" class="multilang-icon"
                            alt="' . $text . '">
                    </a>
                </li>';
            }
            $html .= '</ul>';
            $retval = $html;
        }
        return $retval;
    }

    private function createElement($element, $type, $filePath) {
        $dom = new DOMDocument();
        $element = $dom->createElement($element);
        $element->setAttribute( 'type', $type );
        if ($type == 'js') {
            $element->setAttribute( 'src', $filePath);
        } else if ($type == 'css') {
            $element->setAttribute( 'href', $filePath);
        }
        return $element;
    }

    /**
     * 
     */
    private function definedFetchInjectionScrtipt() {
        $logoutRoute = '/logout';
        if (Route::has('logout')) {
            $logoutRoute = URL::route('logout');
        }
        $defineJsLocale = '
            <script type="text/javascript">
                /** GENERATED BY LOCALIZATION MODULE */
                var localePrefix = "' . env('APP_LANG', '') . '";
                var localeIgnoreUri = \'' . json_encode(config('localization.ignore_uri', [])) . '\';
                var defaultLocale = "' . config('localization.default_locale') . '";
                if (localePrefix) {
                    const originalFetch = window.fetch;
                    window.fetch = function() {
                        try {
                            var requestUrl = arguments[0];
                            var originUrl = new URL(window.location.href);
                            var isContainHostName = requestUrl && requestUrl.includes(originUrl.hostname);
                            //k phai domain khac
                            var isContainHttp = requestUrl && requestUrl.includes("http");
                            var isIgnoreLocalization = requestUrl && requestUrl.includes("ignore_localization=1");
                            if (!isIgnoreLocalization) {
                                if (isContainHostName) {
                                    requestUrl = requestUrl.replace(originUrl.hostname, originUrl.hostname + "/" + localePrefix);
                                } else if (!isContainHttp) {
                                    if (requestUrl.charAt(0) != "/") {
                                        requestUrl = "/" + requestUrl;
                                    }
                                    requestUrl = "/" + localePrefix + requestUrl;
                                }
                            }
                            arguments[0] = requestUrl;
                        } catch (err) {
                        }
                       return originalFetch.apply(null, arguments);
                    }
                }
                
            </script>';

            return $defineJsLocale;
    }
}
