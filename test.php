<?php

    require_once('vendor/autoload.php');
    use Facebook\WebDriver\Remote\RemoteWebDriver as RemoteWebDriver;
    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\Remote\DesiredCapabilities as DesiredCapabilities;

// This would be the url of the host running the server-standalone.jar
echo "host";
$host = 'http://localhost:4444'; // this is the default

// Set URL
$url = 'https://snippetinfo.net';

echo "launch";

// Launch Firefox
$capabilities = DesiredCapabilities::firefox();

$capabilities->setCapability(
    'moz:firefoxOptions',
   ['args' => ['-headless']]
);


$driver = RemoteWebDriver::create($host, $capabilities);


echo "get";
$driver->get($url);

echo $driver->getTitle();

$driver->quit();
?>
