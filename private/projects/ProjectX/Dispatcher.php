<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 18.07.2011
 * Time: 23:11
 */

namespace projects\ProjectX;


class Dispatcher
{

    protected $httpRequest;
    protected $router;
    protected $dicManager;

    /**
     * Dispatcher constructor.
     * @param \Core\DicManager $dicManager
     */
    public function __construct(\Core\DicManager $dicManager)
    {
        /** @var  \Core\HttpRequest httpRequest */
        $this->httpRequest = $dicManager->getDic(__NAMESPACE__)->getHttpRequest();
        /** @var Router router */
        $this->router = $dicManager->getDic(__NAMESPACE__)->getRouter();
        $this->dicManager = $dicManager;
    }

    /**
     * @desc Gibt das erstellte ControllerObject zurück
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getController()
    {
        $controllerName = $this->getControllerName($this->httpRequest->getUriParameters());

        return new $controllerName($this->dicManager, __NAMESPACE__);
    }

    /**
     * @desc Erstellt den ControllerNamen und gibt ihn zurück
     *
     * @return string
     * @throws \Exception
     */
    protected function getControllerName($uriParameters)
    {
        if (!is_array($uriParameters)) {
            return __NAMESPACE__.'\\Controller\\'.$this->router->getRouteIntern(null).'\\'.$this->router->getRouteIntern(null);
        }

        $controllerName = __NAMESPACE__.'\\Controller\\'.$this->router->getRouteIntern($uriParameters[0]);
        if (!class_exists($controllerName, true)) {
            throw new \Exception('Die Klasse '.$controllerName.' existiert nicht!');
        }

        return $controllerName;
    }

}