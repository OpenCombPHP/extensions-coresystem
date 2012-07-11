<?php
namespace org\opencomb\coresystem\setup;

use org\jecat\framework\db\DB ;
use org\jecat\framework\message\Message;
use org\jecat\framework\message\MessageQueue;
use org\opencomb\platform\ext\Extension;
use org\opencomb\platform\ext\ExtensionMetainfo ;
use org\opencomb\platform\ext\IExtensionDataInstaller ;
use org\jecat\framework\fs\Folder;

// 这个 DataInstaller 程序是由扩展 development-toolkit 的 create data installer 模块自动生成
// 扩展 development-toolkit 版本：0.2.0.0
// create data installer 模块版本：1.0.7.0

class DataInstaller implements IExtensionDataInstaller
{
	public function install(MessageQueue $aMessageQueue,ExtensionMetainfo $aMetainfo)
	{
		$aExtension = new Extension($aMetainfo);
		
		// 1 . create data table
		$aDB = DB::singleton();
		
		$aDB->execute( "CREATE TABLE IF NOT EXISTS `".$aDB->transTableName("coresystem_group")."` (
  `gid` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  PRIMARY KEY (`gid`),
  KEY `rgt` (`rgt`),
  KEY `lft-rgt` (`lft`,`rgt`)
) ENGINE=MyISAM AUTO_INCREMENT=97 DEFAULT CHARSET=utf8" );
		$aMessageQueue->create(Message::success,'新建数据表： `%s` 成功',$aDB->transTableName('coresystem_group') );
		
		
		$aDB->execute( "CREATE TABLE IF NOT EXISTS `".$aDB->transTableName("coresystem_group_user_link")."` (
  `uid` int(10) NOT NULL,
  `gid` int(10) NOT NULL,
  UNIQUE KEY `uid-gid` (`uid`,`gid`),
  UNIQUE KEY `gid-uid` (`gid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8" );
		$aMessageQueue->create(Message::success,'新建数据表： `%s` 成功',$aDB->transTableName('coresystem_group_user_link') );
		
		
		$aDB->execute( "CREATE TABLE IF NOT EXISTS `".$aDB->transTableName("coresystem_purview")."` (
  `type` enum('user','group') NOT NULL,
  `id` int(10) NOT NULL,
  `extension` varchar(30) NOT NULL,
  `name` varchar(30) NOT NULL,
  `target` varchar(30) DEFAULT NULL,
  `inheritance` enum('1','0') NOT NULL DEFAULT '0' COMMENT '下级用户组继承此权限',
  `bubble` enum('1','0') NOT NULL DEFAULT '1' COMMENT '将权限”冒泡“给上级用户组',
  UNIQUE KEY `purview` (`type`,`extension`,`name`,`target`,`id`),
  KEY `id` (`type`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8" );
		$aMessageQueue->create(Message::success,'新建数据表： `%s` 成功',$aDB->transTableName('coresystem_purview') );
		
		
		$aDB->execute( "CREATE TABLE IF NOT EXISTS `".$aDB->transTableName("coresystem_user")."` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `password` varchar(32) CHARACTER SET latin1 NOT NULL,
  `lastLoginTime` int(10) NOT NULL,
  `lastLoginIp` varchar(15) CHARACTER SET latin1 NOT NULL,
  `registerTime` int(10) NOT NULL,
  `registerIp` varchar(15) CHARACTER SET latin1 NOT NULL,
  `activeTime` int(10) NOT NULL,
  `activeIp` varchar(15) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=1422 DEFAULT CHARSET=utf8" );
		$aMessageQueue->create(Message::success,'新建数据表： `%s` 成功',$aDB->transTableName('coresystem_user') );
		
		
		$aDB->execute( "CREATE TABLE IF NOT EXISTS `".$aDB->transTableName("coresystem_userinfo")."` (
  `uid` int(10) NOT NULL,
  `nickname` varchar(60) NOT NULL,
  `realname` varchar(60) NOT NULL,
  `gender` int(1) NOT NULL,
  `birthday` int(10) NOT NULL,
  `sex` varchar(50) NOT NULL,
  `avatar` varchar(120) NOT NULL,
  `email` varchar(40) NOT NULL,
  `tel` varchar(40) NOT NULL,
  `hometown_coutry` varchar(60) DEFAULT NULL,
  `hometown_province` varchar(60) DEFAULT NULL,
  `hometown_city` varchar(60) DEFAULT NULL,
  `locale_coutry` varchar(60) DEFAULT NULL,
  `locale_province` varchar(60) DEFAULT NULL,
  `locale_city` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `hometown_coutry` (`hometown_coutry`,`hometown_province`,`hometown_city`),
  KEY `locale_coutry` (`locale_coutry`,`locale_province`,`locale_city`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8" );
		$aMessageQueue->create(Message::success,'新建数据表： `%s` 成功',$aDB->transTableName('coresystem_userinfo') );
		
		
		
		// 2. insert table data
		$nDataRows = 0 ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group") . '` (`gid`,`name`,`lft`,`rgt`) VALUES ("87","非常牛逼的黑客级管理员","3","4") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group") . '` (`gid`,`name`,`lft`,`rgt`) VALUES ("86","子系统B管理员","6","13") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group") . '` (`gid`,`name`,`lft`,`rgt`) VALUES ("85","子系统A管理员","2","5") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group") . '` (`gid`,`name`,`lft`,`rgt`) VALUES ("92","天才","15","20") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group") . '` (`gid`,`name`,`lft`,`rgt`) VALUES ("84","系统管理员","1","14") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group") . '` (`gid`,`name`,`lft`,`rgt`) VALUES ("93","假装是天才的人","16","17") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group") . '` (`gid`,`name`,`lft`,`rgt`) VALUES ("94","还真的是天才","18","19") ') ;
		$aMessageQueue->create(Message::success,'向数据表%s插入了%d行记录。',array($aDB->transTableName("coresystem_group"),$nDataRows));
			
		$nDataRows = 0 ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group_user_link") . '` (`uid`,`gid`) VALUES ("1","84") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group_user_link") . '` (`uid`,`gid`) VALUES ("1","93") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group_user_link") . '` (`uid`,`gid`) VALUES ("1","94") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group_user_link") . '` (`uid`,`gid`) VALUES ("5","84") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group_user_link") . '` (`uid`,`gid`) VALUES ("6","84") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group_user_link") . '` (`uid`,`gid`) VALUES ("8","84") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group_user_link") . '` (`uid`,`gid`) VALUES ("9","84") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group_user_link") . '` (`uid`,`gid`) VALUES ("10","84") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group_user_link") . '` (`uid`,`gid`) VALUES ("11","84") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group_user_link") . '` (`uid`,`gid`) VALUES ("34297","84") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group_user_link") . '` (`uid`,`gid`) VALUES ("270403","84") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_group_user_link") . '` (`uid`,`gid`) VALUES ("2002818","84") ') ;
		$aMessageQueue->create(Message::success,'向数据表%s插入了%d行记录。',array($aDB->transTableName("coresystem_group_user_link"),$nDataRows));
			
		$nDataRows = 0 ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("group","84","coresystem","PLATFORM_ADMIN",NULL,"1","0") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("group","87","coresystem","test-purview2",NULL,"0","1") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("group","81","coresystem","test-purview3",NULL,"0","0") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("group","85","coresystem","test-purview1",NULL,"0","0") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("user","9","coresystem","test-purview3",NULL,"0","1") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("user","9","coresystem","test-purview1","20","0","1") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("user","9","coresystem","test-purview2",NULL,"0","1") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("user","9","coresystem","test-purview1",NULL,"0","1") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("user","9","coresystem","PLATFORM_ADMIN",NULL,"0","1") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("user","1","coresystem","PLATFORM_ADMIN",NULL,"0","1") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("group","94","coresystem","PLATFORM_ADMIN",NULL,"0","0") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("group","94","coresystem","test-purview1",NULL,"0","0") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("user","1","coresystem","test-purview2",NULL,"0","1") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("user","1","coresystem","test-purview1","20","0","1") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_purview") . '` (`type`,`id`,`extension`,`name`,`target`,`inheritance`,`bubble`) VALUES ("user","5","coresystem","test-purview1","20","0","1") ') ;
		$aMessageQueue->create(Message::success,'向数据表%s插入了%d行记录。',array($aDB->transTableName("coresystem_purview"),$nDataRows));
			
		$nDataRows = 0 ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_user") . '` (`uid`,`username`,`password`,`lastLoginTime`,`lastLoginIp`,`registerTime`,`registerIp`,`activeTime`,`activeIp`) VALUES ("1","alee","e5daf7d13b0388d9b892d9edcc93375c","1338182190","192.168.1.222","1321327227","192.168.1.211","0","") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_user") . '` (`uid`,`username`,`password`,`lastLoginTime`,`lastLoginIp`,`registerTime`,`registerIp`,`activeTime`,`activeIp`) VALUES ("5","anubiskong","726c7a6583228b1435751bd0efd38dbc","1337912694","192.168.1.28","1322445303","127.0.0.1","0","") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_user") . '` (`uid`,`username`,`password`,`lastLoginTime`,`lastLoginIp`,`registerTime`,`registerIp`,`activeTime`,`activeIp`) VALUES ("6","huiling","ed7dafb153f7bc022790a0fdfd3d402b","1334196428","192.168.1.91","1322454217","127.0.0.1","0","") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_user") . '` (`uid`,`username`,`password`,`lastLoginTime`,`lastLoginIp`,`registerTime`,`registerIp`,`activeTime`,`activeIp`) VALUES ("11","alee3","fdbccdd88718fcd1d520a8f691b2a80a","0","","1327827903","127.0.0.1","0","") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_user") . '` (`uid`,`username`,`password`,`lastLoginTime`,`lastLoginIp`,`registerTime`,`registerIp`,`activeTime`,`activeIp`) VALUES ("7","alee2","ec6c4ec54aaa7b4fffa6449515a15370","1327827917","127.0.0.1","1323930917","127.0.0.1","0","") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_user") . '` (`uid`,`username`,`password`,`lastLoginTime`,`lastLoginIp`,`registerTime`,`registerIp`,`activeTime`,`activeIp`) VALUES ("8","elephant","9f11cce1b21e550bb79ad325b0b2cd8a","1340163008","192.168.1.62","1324021451","192.168.1.62","0","") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_user") . '` (`uid`,`username`,`password`,`lastLoginTime`,`lastLoginIp`,`registerTime`,`registerIp`,`activeTime`,`activeIp`) VALUES ("9","aarongao","264f903e092921d6df1407dbc5c2ec40","1334543917","192.168.1.222","1326940498","192.168.1.222","0","") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_user") . '` (`uid`,`username`,`password`,`lastLoginTime`,`lastLoginIp`,`registerTime`,`registerIp`,`activeTime`,`activeIp`) VALUES ("10","qusong","749bae71521223b89f939800c65d5657","1341820671","127.0.0.1","1327817643","192.168.1.185","0","") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_user") . '` (`uid`,`username`,`password`,`lastLoginTime`,`lastLoginIp`,`registerTime`,`registerIp`,`activeTime`,`activeIp`) VALUES ("1418","youke","4ef990cdd0749525fdc2184adf095385","0","","1337744027","127.0.0.1","0","") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_user") . '` (`uid`,`username`,`password`,`lastLoginTime`,`lastLoginIp`,`registerTime`,`registerIp`,`activeTime`,`activeIp`) VALUES ("1419","fdsafdavvvv","e37260731dacce9427c41dde6aeb2308","0","","1337828698","192.168.1.28","0","") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_user") . '` (`uid`,`username`,`password`,`lastLoginTime`,`lastLoginIp`,`registerTime`,`registerIp`,`activeTime`,`activeIp`) VALUES ("1420","fdsafdavvvvxx","7fad9096568f009f177aa948c608cd4f","0","","1337829060","192.168.1.28","0","") ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_user") . '` (`uid`,`username`,`password`,`lastLoginTime`,`lastLoginIp`,`registerTime`,`registerIp`,`activeTime`,`activeIp`) VALUES ("1421","gao830228@t.qq.com","1297e7f0eabdc5088fd151e68056571d","1340785474","192.168.1.222","1340785474","192.168.1.222","1340785474","192.168.1.222") ') ;
		$aMessageQueue->create(Message::success,'向数据表%s插入了%d行记录。',array($aDB->transTableName("coresystem_user"),$nDataRows));
			
		$nDataRows = 0 ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_userinfo") . '` (`uid`,`nickname`,`realname`,`gender`,`birthday`,`sex`,`avatar`,`email`,`tel`,`hometown_coutry`,`hometown_province`,`hometown_city`,`locale_coutry`,`locale_province`,`locale_city`) VALUES ("1","alee","","0","0","","12/1/6/hash29450d8230401fbd37f6f76eb94ec440.a.jpg","","",NULL,NULL,NULL,NULL,NULL,NULL) ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_userinfo") . '` (`uid`,`nickname`,`realname`,`gender`,`birthday`,`sex`,`avatar`,`email`,`tel`,`hometown_coutry`,`hometown_province`,`hometown_city`,`locale_coutry`,`locale_province`,`locale_city`) VALUES ("9","aarongao","","0","0","","","","",NULL,NULL,NULL,NULL,NULL,NULL) ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_userinfo") . '` (`uid`,`nickname`,`realname`,`gender`,`birthday`,`sex`,`avatar`,`email`,`tel`,`hometown_coutry`,`hometown_province`,`hometown_city`,`locale_coutry`,`locale_province`,`locale_city`) VALUES ("5","anubiskong","","0","0","男","http://12/2/8/hash0d0c9149ee8d177f73f622ad307a238e.prepare.png","anubiskong@gmail.com","135165149878",NULL,NULL,NULL,NULL,NULL,NULL) ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_userinfo") . '` (`uid`,`nickname`,`realname`,`gender`,`birthday`,`sex`,`avatar`,`email`,`tel`,`hometown_coutry`,`hometown_province`,`hometown_city`,`locale_coutry`,`locale_province`,`locale_city`) VALUES ("6","huiling","","0","0","","","","",NULL,NULL,NULL,NULL,NULL,NULL) ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_userinfo") . '` (`uid`,`nickname`,`realname`,`gender`,`birthday`,`sex`,`avatar`,`email`,`tel`,`hometown_coutry`,`hometown_province`,`hometown_city`,`locale_coutry`,`locale_province`,`locale_city`) VALUES ("7","alee2","","0","0","","","","",NULL,NULL,NULL,NULL,NULL,NULL) ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_userinfo") . '` (`uid`,`nickname`,`realname`,`gender`,`birthday`,`sex`,`avatar`,`email`,`tel`,`hometown_coutry`,`hometown_province`,`hometown_city`,`locale_coutry`,`locale_province`,`locale_city`) VALUES ("8","elephant","","0","0","","","","",NULL,NULL,NULL,NULL,NULL,NULL) ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_userinfo") . '` (`uid`,`nickname`,`realname`,`gender`,`birthday`,`sex`,`avatar`,`email`,`tel`,`hometown_coutry`,`hometown_province`,`hometown_city`,`locale_coutry`,`locale_province`,`locale_city`) VALUES ("10","qusong","","0","0","","","","",NULL,NULL,NULL,NULL,NULL,NULL) ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_userinfo") . '` (`uid`,`nickname`,`realname`,`gender`,`birthday`,`sex`,`avatar`,`email`,`tel`,`hometown_coutry`,`hometown_province`,`hometown_city`,`locale_coutry`,`locale_province`,`locale_city`) VALUES ("11","alee3","","0","0","","","","",NULL,NULL,NULL,NULL,NULL,NULL) ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_userinfo") . '` (`uid`,`nickname`,`realname`,`gender`,`birthday`,`sex`,`avatar`,`email`,`tel`,`hometown_coutry`,`hometown_province`,`hometown_city`,`locale_coutry`,`locale_province`,`locale_city`) VALUES ("0","崑崙-終極使命","","0","0","","http://tp2.sinaimg.cn/2705613893/50/5629196119/1","","",NULL,NULL,NULL,NULL,NULL,NULL) ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_userinfo") . '` (`uid`,`nickname`,`realname`,`gender`,`birthday`,`sex`,`avatar`,`email`,`tel`,`hometown_coutry`,`hometown_province`,`hometown_city`,`locale_coutry`,`locale_province`,`locale_city`) VALUES ("1420","fdsafdavvvvxx","","0","0","","","","",NULL,NULL,NULL,NULL,NULL,NULL) ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_userinfo") . '` (`uid`,`nickname`,`realname`,`gender`,`birthday`,`sex`,`avatar`,`email`,`tel`,`hometown_coutry`,`hometown_province`,`hometown_city`,`locale_coutry`,`locale_province`,`locale_city`) VALUES ("1421","","","0","0","","","","",NULL,NULL,NULL,NULL,NULL,NULL) ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_userinfo") . '` (`uid`,`nickname`,`realname`,`gender`,`birthday`,`sex`,`avatar`,`email`,`tel`,`hometown_coutry`,`hometown_province`,`hometown_city`,`locale_coutry`,`locale_province`,`locale_city`) VALUES ("1419","fdsafdavvvv","","0","0","","","","",NULL,NULL,NULL,NULL,NULL,NULL) ') ;
		$nDataRows+= $aDB->execute( 'REPLACE INTO `' . $aDB->transTableName("coresystem_userinfo") . '` (`uid`,`nickname`,`realname`,`gender`,`birthday`,`sex`,`avatar`,`email`,`tel`,`hometown_coutry`,`hometown_province`,`hometown_city`,`locale_coutry`,`locale_province`,`locale_city`) VALUES ("1418","youke","","0","0","","","","",NULL,NULL,NULL,NULL,NULL,NULL) ') ;
		$aMessageQueue->create(Message::success,'向数据表%s插入了%d行记录。',array($aDB->transTableName("coresystem_userinfo"),$nDataRows));
			
		
		
		// 3. settings
		
		$aSetting = $aExtension->setting() ;
			
				
		$aSetting->setItem('/webpage/','title-template','%s');
				
		$aSetting->setItem('/webpage/','description-template','%s');
				
		$aSetting->setItem('/webpage/','keywords-template','%s');
				
		$aSetting->setItem('/webpage/','controlpanel-title-template','控制面板 - %s');
				
		$aSetting->setItem('/webpage/','controlpanel-description-template','%s');
				
		$aSetting->setItem('/webpage/','controlpanel-keywords-template','%s');
				
		$aSetting->setItem('/webpage/','userpanel-title-template','用户面板 - %s');
				
		$aSetting->setItem('/webpage/','userpanel-description-template','%s');
				
		$aSetting->setItem('/webpage/','userpanel-keywords-template','%s');
				
		$aMessageQueue->create(Message::success,'保存配置：%s',"/webpage/");
			
				
		$aSetting->setItem('/systemupgrade/','xmlUrl','http://release.opencomb.com/releases.xml');
				
		$aMessageQueue->create(Message::success,'保存配置：%s',"/systemupgrade/");
			
		
		
		// 4. files
		
		$sFromPath = $aExtension->metainfo()->installPath().'/data/public';
		$sDestPath = $aExtension ->filesFolder()->path();
		Folder::RecursiveCopy( $sFromPath , $sDestPath );
		$aMessageQueue->create(Message::success,'复制文件夹： `%s` to `%s`',array($sFromPath,$sDestPath));
		
	}
}
