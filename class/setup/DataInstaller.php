<?php
namespace org\opencomb\coresystem\setup;

use org\jecat\framework\db\DB ;
use org\jecat\framework\message\Message;
use org\jecat\framework\message\MessageQueue;
use org\opencomb\platform\ext\Extension;
use org\opencomb\platform\ext\ExtensionMetainfo ;
use org\opencomb\platform\ext\IExtensionDataInstaller ;

class DataInstaller implements IExtensionDataInstaller
{
	public function install(MessageQueue $aMessageQueue,ExtensionMetainfo $aMetainfo)
	{
		$aExtension = new Extension($aMetainfo);
		
		// 1 . create data table
		
		DB::singleton()->execute( "CREATE TABLE IF NOT EXISTS `coresystem_group` (
  `gid` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  PRIMARY KEY (`gid`),
  KEY `rgt` (`rgt`),
  KEY `lft-rgt` (`lft`,`rgt`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8" );
		$aMessageQueue->create(Message::success,'新建数据表： %s',"coresystem_group");
		
		
		DB::singleton()->execute( "CREATE TABLE IF NOT EXISTS `coresystem_group_user_link` (
  `uid` int(10) NOT NULL,
  `gid` int(10) NOT NULL,
  UNIQUE KEY `uid-gid` (`uid`,`gid`),
  UNIQUE KEY `gid-uid` (`gid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8" );
		$aMessageQueue->create(Message::success,'新建数据表： %s',"coresystem_group_user_link");
		
		
		DB::singleton()->execute( "CREATE TABLE IF NOT EXISTS `coresystem_purview` (
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
		$aMessageQueue->create(Message::success,'新建数据表： %s',"coresystem_purview");
		
		
		DB::singleton()->execute( "CREATE TABLE IF NOT EXISTS `coresystem_user` (
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
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8" );
		$aMessageQueue->create(Message::success,'新建数据表： %s',"coresystem_user");
		
		
		DB::singleton()->execute( "CREATE TABLE IF NOT EXISTS `coresystem_userinfo` (
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
		$aMessageQueue->create(Message::success,'新建数据表： %s',"coresystem_userinfo");
		
		
		
		// 2. insert table data
		
		
		
		
		
		
		// 3. settings
		
		
		// 4. files
		
	}
}
