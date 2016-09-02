<?php

namespace Test\Characterisation;

use Cjm\Behat\LocalWebserverExtension\Webserver\BuiltInWebserverController;
use Cjm\Behat\LocalWebserverExtension\Webserver\MinkConfiguration;

trait StartAndStopWebServer
{
    private $webServerController;
    private $isStarted = false;

    /**
     * @before
     */
    final protected function startLocalWebServer()
    {
        $this->webServerController()->startServer();
        $this->isStarted = true;
    }

    /**
     * @after
     */
    final protected function stopLocalWebServer()
    {
        if ($this->isStarted) {
            $this->webServerController()->stopServer();
        }
    }

    protected function webServerController()
    {
        if ($this->webServerController === null) {
            $this->webServerController = new BuiltInWebserverController($this->webServerConfiguration());
        }

        return $this->webServerController;
    }

    protected function webServerConfiguration()
    {
        return new MinkConfiguration(null, null, $this->docRoot(), $this->baseUrl());
    }

    abstract protected function docRoot();

    abstract protected function baseUrl();
}
