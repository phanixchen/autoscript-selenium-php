<?php
//namespace GeneralFunctions;
require_once(BASE_PATH . '/vendor/autoload.php');
use Facebook\WebDriver\Remote\RemoteWebDriver as RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy as WebDriverBy;
use Facebook\WebDriver\Remote\DesiredCapabilities as DesiredCapabilities;


class CkipSelenium
{
    private $ckip_url = "http://sunlight.iis.sinica.edu.tw//uwextract/demo.htm";
    private $driver;

    public function __construct()
    {
        $capabilities = DesiredCapabilities::firefox();

        $capabilities->setCapability(
            'moz:firefoxOptions',
           ['args' => ['-headless']]
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
        $ele = $this->driver->findElement(WebDriverBy::Name("query"));
        $ele->clear();
        //ele.Click();
        //ele.SendKeys(_text);
        $this->driver->executeScript("arguments[0].value=arguments[1]", [$ele, $_text]);
        // IJavaScriptExecutor js = driver as IJavaScriptExecutor;
        //js.ExecuteScript("document.forms[0].query.value='arguments[0]'", _text);
        // js.ExecuteScript("arguments[0].value=arguments[1]", ele, _text);
        $this->driver->findElement(WebDriverBy::Name("Submit"))->Click();

        $ele = null;
        $retry_count = 0;
        do
        {
            try
            {
                $ele = $this->driver->findElement(WebDriverBy::LinkText("包含未知詞的斷詞標記結果"));
            }
            catch (Exception $ex)
            {
                sleep(1);
            }
            $retry_count++;
        } while ($ele == null && $retry_count < 15);

        if ($retry_count>=15) 
        {
            // echo "find 包含未知詞的斷詞標記結果 error\r\n";
            return;
        }
        $ele->Click();

        $ele = null;
        $retry_count = 0;
        do
        {
            try
            {
                $ele = $this->driver->findElement(WebDriverBy::CssSelector("pre"));
            }
            catch (Exception $ex)
            {
                sleep(1);
            }
            $retry_count++;
        } while ($ele == null && $retry_count < 15);

        if ($retry_count>=15) 
        {
            // echo "find pre tag error\r\n";
            return;
        }
        // var_dump($ele);
        return $ele->getText();
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