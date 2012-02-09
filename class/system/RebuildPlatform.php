<?php
namespace org\opencomb\coresystem\system ;

use org\opencomb\platform\system\PlatformShutdowner;

use org\jecat\framework\lang\oop\ClassLoader;

use org\jecat\framework\fs\FileSystem;

use org\opencomb\platform\Platform;

use org\opencomb\platform\system\PlatformSerializer;

use org\jecat\framework\auth\IdManager;
use org\jecat\framework\mvc\controller\Controller;
use org\opencomb\coresystem\auth\Id;

class RebuildPlatform extends Controller
{
	/**
	 * @example /权限/Bean配置许可
	 */
	public function createBeanConfig()
	{
		return array(
			'title'=>'系统重建',
			// 配置许可
			'perms' => array(
				// 权限类型的许可
				'perm.purview'=>array(
					'name' => Id::PLATFORM_ADMIN		// 要求管理员权限
				) ,
			) ,

			'view' => array(
				'template' => 'coresystem:system/RebuildPlatform.html'
			) ,
		) ;
	}

	public function process()
	{
		$this->authorizer()->check(IdManager::singleton()) ;
		
		if( $this->params->has('act') )
		{
			$this->doActions('act') ;
			return ;
		}
		
		// 输出所有的类
		$arrClasses = array() ;
		foreach(ClassLoader::singleton()->classIterator() as $sClassName)
		{
			$arrClasses[] = $sClassName ;
		}
		$this->view->variables()->set('arrClasses',json_encode($arrClasses)) ;
	}
	
	/**
	 * 关闭系统
	 * 
	 * @example /蜂巢/关闭系统
	 * @forwiki /蜂巢/关闭系统
	 */
	public function actionShutdownPlatform()
	{
		// 关闭系统，并取得“后门”密钥
		$sSecretKey = PlatformShutdowner::singleton()->shutdown() ;
		
		// 将后门密钥埋在cookie中，便于当前用户可以进入系统，完成后续操作。
		setcookie('shutdown_backdoor_secret_key',$sSecretKey,time()+24*60*60,'/') ;
		
		// ajax 返回内容
		$this->response()->putReturnVariable(1,'success') ;
		$this->response()->putReturnVariable($sSecretKey,'shutdown_backdoor_secret_key') ;
	}
	
	/**
	 * 恢复系统启动
	 */
	public function actionRestartPlatform()
	{
		PlatformShutdowner::singleton()->restore() ;
		
		$this->response()->putReturnVariable(1,'success') ;
	}
	
	public function actionClear()
	{
		// 清理系统核心类的缓存
		PlatformSerializer::singleton()->clearRestoreCache() ;
		
		// 清理数据库反射缓存
		Platform::singleton()->cache()->delete('/db') ;
		
		foreach(array(
				'/data/class',				// 清理系统中的影子类
				'/data/compiled/class',		// 清理类编译
				'/data/compiled/template'	// 清理模板编译
		) as $sFolder)
		{
			if($aFolder = FileSystem::singleton()->findFolder($sFolder))
			{
				$aFolder->delete(true) ;
			}
		}
		
		echo '{"success":"1"}' ;
		exit() ;				// 立即退出，避免后续执行造成类重新编译
	}
	
	public function actionCompileClasses()
	{
		foreach($this->params['classes'] as $sClass)
		{
			try{
				ClassLoader::singleton()->searchClass($sClass) ;
			} catch (\Exception $e) {}
		}
		
		$this->response()->putReturnVariable(1,'success') ;
		
		$arrCompileds = array_merge(
			ClassLoader::singleton()->compiledClasses()?: array()
			, @$this->params['classes']?: array()
		) ;
		$this->response()->putReturnVariable($arrCompileds,'arrCompileds') ;
	}
	
	
}

