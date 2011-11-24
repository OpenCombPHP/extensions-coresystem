<?php
namespace org\opencomb\coresystem\mvc\controller ;

use org\jecat\framework\mvc\model\db\orm\Prototype;
use org\jecat\framework\auth\IdManager;
use org\opencomb\ext\Extension;
use org\jecat\framework\auth\AuthenticationException;
use org\jecat\framework\mvc\controller\Controller as JcController ;

class Controller extends JcController
{
    /**
     * properties:
     * 	name				string						名称
     * 	params				array,org\jecat\framework\util\IDataSrc 		参数
     *  model.ooxx			config
     *  view.ooxx			config
     *  controller.ooxx		config
     * 
     * @see org\jecat\framework\bean\IBean::build()
     */
    public function build(array & $arrConfig,$sNamespace='*')
    {
    	if($sNamespace=='*')
    	{
    		$sNamespace = $this->application()->extensions()->extensionNameByClass( get_class($this) )?: '*' ;
    	}
    	
    	return parent::build($arrConfig,$sNamespace) ;
    }
    
    /**
     * 
     * @see IController::mainRun()
     */
    public function mainRun ()
    {
	    try{
	    	parent::mainRun() ;
    	}
    	catch (AuthenticationException $e)
    	{
    		$aController = new PermissionDenied($this->params) ;
    		$this->add($aController) ;
    		
    		if( $sMessage = $e->message() )
    		{
    			$aController->viewMain->variables()->set('message',$sMessage) ;
    		}
    		
    		$aController->mainRun() ;
    	}
    }
    
    /**
     * @return org\jecat\framework\auth\IIdentity
     */
    protected function requireLogined($sMessage=null,array $arrArgvs=array()) 
    {
    	if( !$aId=IdManager::fromSession()->currentId() )
    	{
    		$this->permissionDenied($sMessage,$arrArgvs) ;
    	}
    	
    	return $aId ;
    }
    
	protected function permissionDenied($sMessage=null,array $arrArgvs=array())
	{
		throw new AuthenticationException($this,$sMessage,$arrArgvs) ;
	}

    public function createFrame()
    {
    	return new FrontFrame() ;
    }
}

?>