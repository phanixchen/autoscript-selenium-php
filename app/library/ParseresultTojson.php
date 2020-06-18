<?php
// for ckiptagger python (tensorflow)


//namespace GeneralFunctions;
// require_once(BASE_PATH . '/vendor/autoload.php');
// use Facebook\WebDriver\Remote\RemoteWebDriver as RemoteWebDriver;
// use Facebook\WebDriver\WebDriverBy as WebDriverBy;
// use Facebook\WebDriver\Remote\DesiredCapabilities as DesiredCapabilities;


class ParseresultTojson //implements Ckip
{
    private static $application_base_path = "/home/sychen/CKIP/";
    private static $input_path = "/home/sychen/CKIP/ptojinput/";
    private static $output_path = "/home/sychen/CKIP/ptojoutput/";
    
    public function ptoj($_text)  // save to posinput folder, wait for postagging process to process
    {
        // put content
        $filename = (new Datetime())->format("YmdHis") . "_" . uniqid();
        $file = fopen(self::$input_path . $filename . ".txt", "w");
        fputs($file, $_text);
        fclose($file);

        exec(self::$application_base_path . "tree.py -i " . self::$input_path . $filename . " -o " . self::$output_path . $filename);
        sleep(1);


        return trim(file_get_contents(self::$output_path . $filename . ".txt"));
    }


}