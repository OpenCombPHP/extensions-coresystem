<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\opencomb\coresystem\mvc\controller\Controller;
use org\opencomb\platform\ext\ExtensionManager;
use org\jecat\framework\mvc\view\Webpage;

class UserSpace extends Controller
{
    protected function defaultFrameConfig()
    {
    	return array('class'=>'org\\opencomb\\coresystem\\mvc\\controller\\UserSpaceFrame') ;
    }
}