<?php 
namespace Megaads\LaravelLocalization\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class AbtractCommand extends Command {
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    public function getArguments() {
        return [
            // ['name', InputArgument::IS_ARRAY, 'name'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions() {
        return [
            // ['lang', null, InputOption::VALUE_OPTIONAL, 'language'],
        ];
    }

    /**
     * @param $data
     */
    protected function response($data)
    {
        $response = json_encode($data);
        if ($data['status'] == 'successful') {
            $this->displayMessage($data['message'], 's');
        } else if ($data['status'] == 'warning') {
            $this->displayMessage($data['message'], 'w');
        } else {
            $this->displayMessage($data['message'], 'e');
        }
    }

    /**
     * @param $msg
     * @param $type
     * @return void
     */
    protected function displayMessage($msg, $type = 'i')
    {
        switch ($type) {
            case 'e': //error
                    echo "\033[1;3;31m$msg \e[0m\n";
                break;
            case 's': //success
                    echo "\033[1;3;32m$msg \e[0m\n";
                break;
            case 'w': //warning
                    echo "\033[1;3;33m$msg \e[0m\n";
                break;
            case 'i': //info
                    echo "\033[1;3;36m$msg \e[0m\n";
                break;
            default:
                # code...
                break;
        }
    }
}