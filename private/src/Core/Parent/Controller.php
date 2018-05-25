<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 19.05.2018
 * Time: 11:30
 */

namespace Core\Parent;


abstract class Controller
{
    /** @var \Core\DicManager */
    protected $dicManager;
    /** @var  \Core\Dic */
    protected $dicCore;

    protected $dic;


    public function __construct(\Core\DicManager $dicManager, $dicName)
    {
        $this->dicManager = $dicManager;
        $this->dicCore = $this->dicManager->getDic('Core');
        $this->dic = $this->dicManager->getDic($dicName);
    }

    abstract function run();
}