<?php
namespace org\opencomb\coresystem\mvc\controller ;

use jc\mvc\model\db\orm\Prototype;
use jc\auth\IdManager;
use oc\ext\Extension;
use jc\auth\AuthenticationException;
use jc\mvc\controller\Controller as JcController ;

class Controller extends JcController
{
    /**
     * properties:
     * 	name				string						名称
     * 	params				array,jc\util\IDataSrc 		参数
     *  model.ooxx			config
     *  view.ooxx			config
     *  controller.ooxx		config
     * 
     * @see jc\bean\IBean::build()
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
    		$aController = new PermissionDenied($this->aParams) ;
    		$this->add($aController) ;
    		
    		if( $sMessage = $e->message() )
    		{
    			$aController->viewMain->variables()->set('message',$sMessage) ;
    		}
    		
    		$aController->mainRun() ;
    	}
    }
    
    /**
     * @return jc\auth\IIdentity
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