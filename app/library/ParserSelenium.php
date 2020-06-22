<?php
//namespace GeneralFunctions;
require_once(BASE_PATH . '/vendor/autoload.php');
use Facebook\WebDriver\Remote\RemoteWebDriver as RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy as WebDriverBy;
use Facebook\WebDriver\Remote\DesiredCapabilities as DesiredCapabilities;


class ParserSelenium
{
    private $ckip_url = "http://parser.iis.sinica.edu.tw/";
    private $driver;

    public function __construct()
    {
        $capabilities = DesiredCapabilities::firefox();

        $capabilities->setCapability(
            'moz:firefoxOptions',
           []// ['args' => ['-headless']]
        );
        $host = 'http://localhost:4444'; // this is the default


        $this->driver = RemoteWebDriver::create($host, $capabilities);
    }

    public function TeardownTest()
    {
        try
        {
            $this->driver->quit();
        }
        catch (Exception $ex)
        {
            // Ignore errors if unable to close the browser
        }
        //Assert.AreEqual("", verificationErrors.ToString());
    }

    public function TheSeleniumTest($_text)
    {
        $this->driver->navigate()->to($this->ckip_url);
        // $ele = $this->driver->findElement(WebDriverBy::Name("id"));
        $ele = $this->driver->findElement(WebDriverBy::Name("myTag"));
        $ele->clear();
        //ele.Click();
        $ele->SendKeys($_text);
        // $this->driver->executeScript("arguments[0].value=arguments[1]", [$ele, $_text]);
        // IJavaScriptExecutor js = driver as IJavaScriptExecutor;
        //js.ExecuteScript("document.forms[0].query.value='arguments[0]'", _text);
        // js.ExecuteScript("arguments[0].value=arguments[1]", ele, _text);
        $this->driver->findElement(WebDriverBy::Name("bTag"))->Click();

        $subbtn = $this->driver->findElement(WebDriverBy::xpath('/html/body/form[2]/table/tbody/tr/td/input[1]'))->submit();

        $ele = null;
        $retry_count = 0;
        do
        {
            try
            {
                $ele = $this->driver->findElement(WebDriverBy::LinkText("上一頁"));
            }
            catch (Exception $ex)
            {
                sleep(1);
            }
            $retry_count++;
        } while ($ele == null && $retry_count < 15);

        do
        {
            try
            {
                $ele = $this->driver->findElements(WebDriverBy::xpath("//td/nobr"));
            }
            catch (Exception $ex)
            {
                sleep(1);
            }
            $retry_count++;
        } while ($ele == null && $retry_count < 15);

        $arrret = [];
        foreach ($ele as $e) {
            array_push($arrret, $e->getText());
        }
        
        return $arrret;

    }

}