<?php

namespace Core;

/**
 * Class Dic
 * @desc      Der Core DependencyInjectionContainer
 *
 * @author    Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since     2011-07-18
 * @copyright Nicolas Andreas
 * @package   Core
 */
class Dic
{

    protected $dicManager;

    protected $configuration;
    protected $css;
    protected $database;
    protected $directory;
    protected $js;
    protected $repository;
    protected $httpRequest;
    protected $security;
    protected $httpResponse;
    protected $session;
    protected $url;
    protected $view;

    /**
     * @desc Constructor
     *
     * @param DicManager $dicManager
     */
    public function __construct(DicManager $dicManager)
    {
        $this->dicManager = $dicManager;
    }

    /**
     * inject class or mock for testing purpose
     *
     * @param string $name
     * @param mixed $instance
     */
    public function inject($name, $instance)
    {
        $this->$name = $instance;
    }

    /**
     * @desc Gibt das ConfigurationObject zurück die zum übergebenen Namespace passt.
     *
     * @param string $dir
     *
     * @return Configuration
     *
     * @throws \Exception
     */
    public function getConfiguration($dir = 'src')
    {
        if (!$this->configuration) {
            $configurationPath = $this->getDirectory()->getRealPath(SRC_PATH.'Configuration'.DIRECTORY_SEPARATOR.'configuration.ini');
            $this->configuration = new Configuration($configurationPath);

            return $this->configuration;
        }
        $configurationPath = $this->getDirectory()->getRealPath(PRIVATE_PATH.$dir.DIRECTORY_SEPARATOR.'Configuration'.DIRECTORY_SEPARATOR.'configuration.ini');
        $this->configuration->loadConfiguration($configurationPath);

        return $this->configuration;
    }

    /**
     * @desc Gibt das HttpRequestObject zurück
     *
     * @return HttpRequest
     *
     * @throws \Exception
     */
    public function getHttpRequest()
    {
        if (!isset($this->httpRequest)) {
            $this->httpRequest = new HttpRequest($this->getVarSecurity(), $this->getConfiguration());
        }

        return $this->httpRequest;
    }

    /**
     * @desc Gibt das SecurityObject zurück
     *
     * @return Security
     */
    public function getVarSecurity()
    {
        if (!isset($this->security)) {
            $this->security = new VarSecurity();
        }

        return $this->security;
    }

    /**
     * @desc Hier wird, jedesmal wenn verlangt ein neues View ausgegeben.
     *
     * @param $type
     * @param $router
     *
     * @return HtmlView
     *
     * @throws \Exception
     */
    public function getView($type, $router)
    {
        switch ($type) {
            case 'html':
            default:
                return new HtmlView($this->getConfiguration(), $this->getJs(), $this->getCss(), $this->getHttpRequest(), $this->getUrl($router));
        }
    }

    /**
     * @desc Gibt das Directory-Object zurück
     *
     * @return Directory
     */
    public function getDirectory()
    {
        if (!isset($this->directory)) {
            $this->directory = new Directory();
        }

        return $this->directory;
    }

    /**
     * @desc Gibt das DatenbankObject zurück
     *
     * @return \PDO
     *
     * @throws \Exception
     */
    public function getDatabase()
    {
        if (!isset($this->database)) {
            $mySql = $this->getConfiguration()->getAllParameterToGivenGroup('mysql');
            $database = new Database();
            $this->database = $database->get($mySql['host'], $mySql['database'], $mySql['port'], $mySql['user'], $mySql['password'], (bool)$mySql['persistent']);
        }

        return $this->database;
    }

    /**
     * @desc Gibt das UrlObject zurück
     *
     * @param $router
     *
     * @return Url
     *
     * @throws \Exception
     */
    public function getUrl($router)
    {
        if (!isset($this->url)) {
            $this->url = new Url($this->getConfiguration(), $router);
        }

        return $this->url;
    }

    /**
     * @desc Gibt das HttpResponse-Object zurück
     *
     * @param $session
     * @return HttpResponse
     *
     * @throws \Exception
     */
    public function getHttpResponse()
    {
        if (!isset($this->httpResponse)) {
            $this->httpResponse = new HttpResponse($this->getHttpRequest(), $this->getSession());
        }

        return $this->httpResponse;
    }

    /**
     * @desc Gibt das CssObject zurück
     *
     * @return Css
     *
     * @throws \Exception
     */
    public function getCss()
    {
        if (!isset($this->css)) {
            $this->css = new Css($this->getConfiguration(), $this->getDirectory(), $this->getHttpResponse());
        }

        return $this->css;
    }

    /**
     * @desc Gibt das JS-Object zurück
     *
     * @return Javascript
     *
     * @throws \Exception
     */
    public function getJs()
    {
        if (!isset($this->js)) {
            $this->js = new Javascript($this->getConfiguration(), $this->getDirectory(), $this->getHttpResponse());
        }

        return $this->js;
    }

    /**
     * @desc Gibt das Repository für den Core zurück
     *
     * @return Repository
     *
     * @throws \Exception
     */
    public function getRepository()
    {
        if (!isset($this->repository)) {
            $this->repository = new Repository($this->getDatabase());
        }

        return $this->repository;
    }

    /**
     * @desc Gibt das SessionObject zurück
     * @return Session
     */
    public function getSession()
    {
        return Session::getInstance();
    }

}
