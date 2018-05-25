<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 18.07.2011
 * Time: 23:21
 */

namespace projects\ProjectX;


class Dic extends \Core\Dic
{

    protected $dicManager;

    protected $dispatcher;
    protected $router;
    protected $controller = [];
    protected $repositories = [];

    /**
     * @desc Constructor
     *
     * @param \Core\DicManager $dicManager
     */
    public function __construct($dicManager)
    {
        parent::__construct($dicManager);
    }

    /**
     * @desc Erstellt das DispatcherObject
     *
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        if (!isset($this->dispatcher)) {
            $this->dispatcher = new Dispatcher($this->dicManager);
        }

        return $this->dispatcher;
    }

    /**
     * @return Router
     *
     * @throws \Exception
     */
    public function getRouter()
    {
        if (!isset($this->router)) {
            $this->router = new Router(parent::getConfiguration(__NAMESPACE__));
        }

        return $this->router;
    }

    /**
     * @param $type
     * @param null $router
     * @return \Core\HtmlView
     * @throws \Exception
     */
    public function getView($type, $router = null)
    {
        return parent::getView($type, $this->getRouter());
    }

    /**
     * @param string $namespace
     * @return \Core\Configuration
     * @throws \Exception
     */
    public function getConfiguration($namespace = 'projects\ProjectX')
    {
        return parent::getConfiguration($namespace);
    }

    /**
     * @param null $router
     * @return \Core\Url
     * @throws \Exception
     */
    public function getUrl($router = null)
    {
        return parent::getUrl($this->getRouter());
    }

    /**
     * return a controller to given controllername
     *
     * @param $controllerName
     *
     * @return mixed
     */
    public function getController($controllerName)
    {
        if (!isset($this->controller[$controllerName])) {
            $controllerClass = __NAMESPACE__.'\\Controller\\'.$controllerName;
            $this->controller[$controllerName] = new $controllerClass($this->dicManager, $this->dicManager->getDic(__NAMESPACE__));
        }

        return $this->controller[$controllerName];
    }

    /**
     * @return \projects\ProjectX\Classes\News\RepositoryNews
     *
     * @throws \Exception
     */
    public function getRepositoryNews()
    {
        $className = __NAMESPACE__.'\\Classes\\News\\RepositoryNews';
        if (!isset($this->repositories[$className])) {
            $this->repositories[$className] = new $className($this->getDatabase());
        }

        return $this->repositories[$className];
    }

    /**
     * @return \projects\ProjectX\Classes\User\RepositoryUser
     *
     * @throws \Exception
     */
    public function getRepositoryUser()
    {
        $className = __NAMESPACE__.'\\Classes\\User\\RepositoryUser';
        if (!isset($this->repositories[$className])) {
            $this->repositories[$className] = new $className($this->getDatabase());
        }

        return $this->repositories[$className];

    }

    /**
     * @return \projects\ProjectX\Classes\Esi\EsiRepository
     * @throws \Exception
     */
    public function getEsiRepository()
    {
        $className = __NAMESPACE__.'\\Classes\\Esi\\EsiRepository';
        if (!isset($this->repositories[$className])) {
            $this->repositories[$className] = new $className($this->getDatabase());
        }

        return $this->repositories[$className];
    }

    /**
     * @return \projects\ProjectX\Classes\Character\CharacterEveRepository
     * @throws \Exception
     */
    public function getCharacterEveRepository(){
        $className = __NAMESPACE__.'\\Classes\\Character\\CharacterEveRepository';
        if(!isset($this->repositories[$className])){
            $this->repositories[$className] = new $className($this->getDatabase());
        }

        return $this->repositories[$className];

    }

}