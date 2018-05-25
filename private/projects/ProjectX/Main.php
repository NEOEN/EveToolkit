<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 18.07.2011
 * Time: 22:53
 */

namespace projects\ProjectX;

class Main
{

    /**
     * @var object dicManager
     */
    protected $dicManager;

    /**
     * @desc Constructor
     *
     * @param $dicManager
     */
    public function __construct(\Core\DicManager $dicManager)
    {
        $this->dicManager = $dicManager;
    }

    public function run()
    {
        //$this->dicManager->getDic(__NAMESPACE__)->getHttpResponse()->setState(503);
        $this->dicManager->getDic('Core')->getConfiguration(__NAMESPACE__);
        $dic = $this->dicManager->getDic(__NAMESPACE__);
        $dic->getDispatcher()->getController()->run();
        $dic->getSession()->__destruct();
    }
}