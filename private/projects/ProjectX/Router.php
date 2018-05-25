<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 04.03.2012
 * Time: 21:53
 */

namespace projects\ProjectX;


class Router
{

    public $routenExtern = [
        'homepage' => 'Homepage',
        'index' => 'Index',
        'login' => 'Login',
        'logout' => 'Logout',
        'news' => 'News',
        'callback' => 'Callback',
        'test' => 'Test'
    ];

    public $routenIntern;
    public $route;

    protected $configuration;


    public function __construct(\Core\Configuration $configuration)
    {
        $this->routenIntern = array_flip($this->routenExtern);
        $this->configuration = $configuration;
    }

    public function getRouteIntern($extern)
    {
        if (isset($this->routenExtern[$extern])) {
            return $this->route = $this->routenExtern[$extern];
        }

        return $this->route = $this->configuration->getParameter('http', 'fallbackIntern');
    }

    public function getRouteExtern($intern)
    {
        if (isset($this->routenIntern[$intern])) {
            return $this->routenIntern[$intern];
        }

        return $this->configuration->getParameter('http', 'fallbackExtern');
    }

}