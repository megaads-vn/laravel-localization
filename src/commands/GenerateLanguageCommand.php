<?php 
namespace Megaads\Localization\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GenerateLanguageCommand extends AbtractCommand {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'localization:lang:generate';
    

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate language file by read html to extract text from underscore function';

    /**
     * Get the console command arguments.
     * @return array
     */
     public function getArguments() {
        return [
            ['lang', InputArgument::REQUIRED, 'Locale character. It\'s was configure in env file with named APP_LANG'],
        ];
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $lang = $this->argument('lang');
        $this->info('Generate language file by read html to extract text from underscore function');
        $this->info('Language: ' . $lang);
        $fileName = $lang . '.json';
        $outFile = $this->checkOutputFile($fileName);
        $this->response([
            'status' => 'successful',
            'message' => 'Start generate language file'
        ]);
        $scanPath = ['resources/views', 'app/Http'];
        $viewsPath = [];
        foreach ($scanPath as $path) {
            $viewsPath += $this->listFolderFiles(base_path($path));
        }
        if (!empty($viewsPath)) {
            foreach ($viewsPath as $viewPath) {
                $fileContent = file_get_contents($viewPath);
                $regexGetText = '/__\((.*?)\)/m';
                preg_match_all($regexGetText, $fileContent, $matches);
                if (!empty($matches[1])) {
                    $formatedData = [];
                    $temp = [];
                    foreach ($matches[1] as $match) {
                        $match = ltrim($match,'"');
                        $match = ltrim($match,'\'');
                        $match = rtrim($match, '"');
                        $match = rtrim($match, '\'');
                        $match = preg_replace('/\$[a-zA-Z]+$/i', '', $match);
                        if (!in_array($match, $temp) && $match != '') {
                            $temp[] = $match;
                            $formatedData[] = $match;
                        }
                    }
                    $this->writeContentToFile($outFile, $formatedData);
                }
            }
        }
        $this->info("End generate language file.\nFile was saved at " . $outFile);
    }

    /**
     * 
     */
    protected function writeContentToFile($outFile, $writeData)
    {
        $isPush = false;
        $outFileContent = file_get_contents($outFile);
        if (!empty($outFileContent)) {
            $objectContent = json_decode($outFileContent, true);
            foreach ($objectContent as $key => $value) {
                if (in_array($key, $writeData)) {
                    $rmIndex = array_search($key, $writeData);
                    unset($writeData[$rmIndex]);
                }
            }
            if (!empty($writeData)) {
                foreach ($writeData as $item) {
                    $pushItem = array($item => "");
                    $objectContent = $objectContent + $pushItem;
                }
                $isPush = true;
            }
        }
        if ($isPush) {
            $fp = fopen($outFile, 'w');
            fwrite($fp, json_encode($objectContent, JSON_UNESCAPED_UNICODE));
            fclose($fp);
        }
    }

    /**
     * Check output file is exists
     * 
     * 
     */
    protected function checkOutputFile($fileName)
    {
        $outFilePath = base_path('resources/lang/' . $fileName);
        if (!file_exists($outFilePath)) {
            $this->response([
                'status' => 'warning',
                'message' => 'File ' . $fileName . ' is not exists. Create new file'
            ]);
            $content = '{}';
            $fp = fopen($outFilePath, 'w');
            fwrite($fp, $content);
            fclose($fp);
            chmod($outFilePath, 0777);
        }
        return $outFilePath;
    }

    /**
     * List all files in a directory and subs
     * 
     */
    protected function readDirs($path)
    {
        $dirHandle = opendir($path);
        $allPath  = [];
        while ($item = readdir($dirHandle)) {
            $newPath = $path . "/" . $item;
            if (is_dir($newPath) && $item != '.' && $item != '..') {
                $dirPath = $this->readDirs($newPath);
                $allPath = $allPath + $dirPath;
            } else {
                if ($item != '.' && $item != '..') {
                    $fullPath = $newPath;
                    $allPath[]  = $fullPath;
                }
            }
        }
        return $allPath;
    }

    /**
     * 
     */
    protected function listFolderFiles($dir){
        $retval = [];
        if ( is_file($dir) ) {
            $fileContents = file_get_contents($dir);
            if (preg_match_all('/__\([\'"](.*?)[\'"]\)/', $fileContents, $matches)) {
                print_r($matches); // Get whole match values
            }
            return false;
        }

        $ffs = scandir($dir);

        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        // prevent empty ordered elements
        if (count($ffs) < 1)
            return;

        // $allowDir = ['app/Http', 'resources/views'];
        $allowExtension = ['php', 'html', 'js'];
        foreach($ffs as $ff){
            $ext = pathinfo($ff, PATHINFO_EXTENSION);
            if ( is_file($dir . '/' . $ff) && $ext != '' && in_array($ext, $allowExtension) ) {
                $filePath = $dir . "/" . $ff ;
                $retval[] = $filePath;
            }
            $folders = $dir.'/' . $ff;
            $isAllow = true; //$this->isAllowDir($ff);
            if(is_dir($folders) && $isAllow ) {
                $dirFile = $this->listFolderFiles($folders);
                if (is_array($dirFile) && count($dirFile) >= 1) {
                    foreach( $dirFile as $itemFile ) {
                        $retval[] = $itemFile;
                    }
                }
            }
        }

        return $retval;
    }

    private function isAllowDir($dir) {
        $retVal = false;
        $allowDir = ['app/Http', 'resources/views'];
        foreach ($allowDir as $item) {
            if (strpos($dir, $item) >= 0) {
                $retVal = true;
                break;
            }
        }
        return $retVal;
    }
}
