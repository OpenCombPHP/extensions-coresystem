<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\opencomb\coresystem\mvc\controller\Controller;

class UserSpace extends Controller
{
    protected function defaultFrameConfig()
    {
    	return array('class'=>'org\\opencomb\\coresystem\\mvc\\controller\\UserSpaceFrame') ;
    }
}
?>