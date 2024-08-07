<?php 
namespace Megaads\LaravelLocalization\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LanguageApiController extends Controller
{
    /**
     * List language
     * 
     */
    public function listLanguage(Request $request) {
        $response = [
            'status' => 'fail'
        ];
        $locale = $request->get('locale', 'en');
        
        $path = base_path('resources/lang/' . $locale . '.json');
        $content = file_get_contents($path);
        $objectContent = json_decode($content, true);
        if ($objectContent) {
            $response = [
                'status' => 'successful',
                'data' => $objectContent
            ];
        } else {
            $response = [
                'status' => 'successful',
                'message' => 'File not found',
                'data' => []
            ];
        }
        $response['locale'] = $locale;
        return response()->json($response);
    }

    /**
     * Save language
     * 
     */
    public function saveLanguage(Request $request) {
        $response = [
            'status' => 'fail'
        ];
        $locale = $request->get('locale', 'en');
        $path = base_path('resources/lang/' . $locale . '.json');
        $content = file_get_contents($path);
        $objectContent = json_decode($content, true);
        if ($objectContent) {
            $langKey = $request->get('key');
            $langValue = $request->get('value');
            $objectContent[$langKey] = $langValue;
            $fp = fopen($path, 'w');
            fwrite($fp, json_encode($objectContent, JSON_UNESCAPED_UNICODE));
            fclose($fp);
            $response = [
                'status' => 'successful',
                'data' => $langValue
            ];
        }
        return response()->json($response);
    }

    /**
     * Delete item
     * 
     */
    public function deleteItem(Request $request) {
        $response = [
            'status' => 'fail'
        ];
        $locale = $request->get('locale', 'en');
        $path = base_path('resources/lang/' . $locale . '.json');
        $content = file_get_contents($path);
        $objectContent = json_decode($content, true);
        if ($objectContent) {
            $key = $request->get('key');
            if (isset($objectContent[$key])) {
                unset($objectContent[$key]);
            }
            $fp = fopen($path, 'w');
            fwrite($fp, json_encode($objectContent, JSON_UNESCAPED_UNICODE));
            fclose($fp);
            $response = [
                'status' => 'successful'
            ];
        }
        return response()->json($response);
    }
}