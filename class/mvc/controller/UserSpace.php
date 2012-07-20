<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\opencomb\coresystem\mvc\controller\Controller;

class UserSpace extends Controller
{
    protected function defaultFrameConfig()
    {    	
    	return array(
    			'class'=>'webframe' ,
    			'frameview:frameView' => array(
    					'template' => 'coresystem:UserSpaceFrame.html' ,
    			) ,
    	) ;
    }
}
