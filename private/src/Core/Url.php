<?php

namespace Core;

/**
 * Class Url
 * @desc      Behandelt alle Umschreibungen der Url sowohl nach intern als auch nach extern
 * @author    Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since     2011-07-18
 * @copyright Nicolas Andreas
 * @package   Core
 */

class Url
{

    protected $configuration;
    protected $router;

    protected $imageUrls = array();

    /**
     * @desc Constructor
     *
     * @param    Configuration $configuration
     */
    public function __construct(Configuration $configuration, $router)
    {
        $this->configuration = $configuration;
        $this->router = $router;
    }

    public function setRouter($router)
    {
        $this->router = $router;
    }

    /**
     * @desc Baut aus den übergebenen Parametern eine Url zusammen
     *
     * @param null $controllerIntern
     * @param null $parameter
     * @param null $subdomain
     * @param string $protocol
     *
     * @return string
     *
     * @throws \Exception
     */
    public function get($controllerIntern = null, $parameter = null, $subdomain = null, $protocol = 'https://')
    {
        $uri = $this->configuration->getParameter('http', 'uri').'/';
        $controllerExtern = $this->router->getRouteExtern($controllerIntern);

        if ($controllerExtern == 'index') {
            $controllerExtern = '';
        }
        if ($subdomain) {
            $subdomain .= '.';
        }
        if ($parameter && \stripos($parameter, '.html') === false) {
            $parameter .= '/';
        }
        if ($controllerExtern) {
            $controllerExtern .= '/';
        }

        return $protocol.$subdomain.$uri.$controllerExtern.$parameter;
    }

    /**
     * @desc Gibt ein Image auf einer Subdomain zurück um die Browserbegrenzung von 4 Elementen gleichzeitig laden zu umgehen.
     *
     * @param $imagePath
     * @param string $protocol
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getImageUrl($imagePath, $protocol = 'http://', $withSubdomain = false)
    {
        $subdomain = '';
        if($withSubdomain) {
            if (!in_array($imagePath, $this->imageUrls)) {
                $this->imageUrls[] = $imagePath;
            }
            $iKey = array_search($imagePath, $this->imageUrls) + 1;
            $subdomain = 'img'.sprintf('%1$02d', ceil($iKey / 4)).'.';
        }

        return $protocol.$subdomain.$this->configuration->getParameter('http', 'staticuri').'/'.$this->configuration->getParameter('images', 'publicFolder').'/'.$imagePath;
    }
}
