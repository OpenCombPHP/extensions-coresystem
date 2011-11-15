<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\opencomb\coresystem\mvc\controller\Controller;

class ControlPanel extends Controller
{
    public function createFrame()
    {
    	return new ControlPanelFrame() ;
    }
}

?>