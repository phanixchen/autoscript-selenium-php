<?php
// for ckiptagger python (tensorflow)


//namespace GeneralFunctions;
// require_once(BASE_PATH . '/vendor/autoload.php');
// use Facebook\WebDriver\Remote\RemoteWebDriver as RemoteWebDriver;
// use Facebook\WebDriver\WebDriverBy as WebDriverBy;
// use Facebook\WebDriver\Remote\DesiredCapabilities as DesiredCapabilities;


class Ckiptagger //implements Ckip
{
    private static $application_base_path = "/home/sychen/CKIP/";
    private static $posinput_path = "/home/sychen/CKIP/posinput/";
    private static $posoutput_path = "/home/sychen/CKIP/posoutput/";
    
    public function postagging($_text)  // save to posinput folder, wait for postagging process to process
    {
        // put content
        $filename = (new Datetime())->format("YmdHis") . "_" . uniqid();
        $file = fopen(self::$posinput_path . $filename . ".txt", "w");
        fputs($file, $_text . "\r\n%%EoF%%");
        fclose($file);

        $str_result = "";
        while(1)
        {
            sleep(5);

            if (file_exists(self::$posoutput_path . $filename . ".txt"))
            {
                $str_result = trim(file_get_contents(self::$posoutput_path . $filename . ".txt"));
                $arr_result = explode("\n", $str_result);

                if ($arr_result[count($arr_result) - 1] != "%%EoF%%")
                    continue;
                else
                {
                    unset($arr_result[count($arr_result) - 1]);
                    $str_result = implode("\n", $arr_result);
                    break;
                }
            }
        }

        return $str_result;
    }

    public function TheSeleniumTest($_text)
    {
       
    }
/*
    private function IsElementPresent(By by)
    {
        try
        {
            driver.FindElement(by);
            return true;
        }
        catch (Exception $ex)
        {
            return false;
        }
    }

    private function IsAlertPresent()
    {
        try
        {
            driver.SwitchTo().Alert();
            return true;
        }
        catch (Exception $ex)
        {
            return false;
        }
    }

    private function CloseAlertAndGetItsText()
    {
        try
        {
            IAlert alert = driver.SwitchTo().Alert();
            string alertText = alert.Text;
            if (acceptNextAlert)
            {
                alert.Accept();
            }
            else
            {
                alert.Dismiss();
            }
            return alertText;
        }
        finally
        {
            acceptNextAlert = true;
        }
    }
*/
}