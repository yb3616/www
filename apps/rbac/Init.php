<?php
/**
 * 权限管理：rbac
 * 初始化数据库环境
 *
 * 错误码：101xxxx
 *
 * @Author    姚斌  <yb3616@126>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */
namespace Apps\rbac;

use Exception;
use YF\DB;
use YF\Response;
use Apps\rbac\Base;

class Init
{
  /**
   * 错误码
   */
  static private $_errno = '05';

  /**
   * 错误码：00
   */
  public function createEnv()
  {
    $errno = Base::$errno . self::$_errno . '00';
    $db = DB::handle( 'master' );
    try{
      $tb = 'rbac_pa';
      $db->exec( 'DROP TABLE IF EXISTS `' . $tb . '`' );
      $db->exec( "CREATE TABLE `$tb` (
        `rid` int(10) unsigned NOT NULL COMMENT 'roles.id',
        `pid` int(10) unsigned NOT NULL COMMENT 'permissions.id',
        UNIQUE KEY `rid_pid` (`rid`,`pid`),
        KEY `uid` (`rid`),
        KEY `pid` (`pid`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色-权限表'" );
    }catch( Exception $e ){
      return Response::withJson([ 'errno'=> $errno.'00', 'errmsg'=>'建表错误：' . $tb ]);
    }

    try{
      $tb = 'rbac_permissions';
      $db->exec( 'DROP TABLE IF EXISTS `' . $tb . '`' );
      $db->exec( "CREATE TABLE `$tb` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
        `permissionname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '权限名',
        `actionname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '类方法名',
        `methodname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求方法名',
        `lft` int(10) unsigned NOT NULL COMMENT '预排序遍历树：左值',
        `rgt` int(10) unsigned NOT NULL COMMENT '预排序遍历树：右值',
        `lvl` int(10) unsigned NOT NULL COMMENT '预排序遍历树：深度',
        PRIMARY KEY (`id`),
        UNIQUE KEY `am` (`methodname`,`actionname`),
        UNIQUE KEY `permissionname` (`permissionname`,`lvl`),
        KEY `actionname` (`actionname`),
        KEY `methodname` (`methodname`),
        KEY `lft` (`lft`),
        KEY `rgt` (`rgt`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限表'" );
      DB::name( $tb )->add([
        [ 'permissionname'=>'权限管理',   'actionname'=>'/rbac',            'methodname'=>'post,delete,put,get,cli', 'lft'=>1,  'rgt'=>30, 'lvl'=>1 ],
        [ 'permissionname'=>'增加PA',     'actionname'=>'/rbac/pa',         'methodname'=>'post',                    'lft'=>2,  'rgt'=>3,  'lvl'=>2 ],
        [ 'permissionname'=>'删除PA',     'actionname'=>'/rbac/pa',         'methodname'=>'delete',                  'lft'=>4,  'rgt'=>5,  'lvl'=>2 ],
        [ 'permissionname'=>'查询PA',     'actionname'=>'/rbac/pa',         'methodname'=>'get',                     'lft'=>6,  'rgt'=>7,  'lvl'=>2 ],
        [ 'permissionname'=>'增加权限',   'actionname'=>'/rbac/permission', 'methodname'=>'post',                    'lft'=>8,  'rgt'=>9,  'lvl'=>2 ],
        [ 'permissionname'=>'删除权限',   'actionname'=>'/rbac/permission', 'methodname'=>'delete',                  'lft'=>10, 'rgt'=>11, 'lvl'=>2 ],
        [ 'permissionname'=>'修改权限',   'actionname'=>'/rbac/permission', 'methodname'=>'put',                     'lft'=>12, 'rgt'=>13, 'lvl'=>2 ],
        [ 'permissionname'=>'查询权限',   'actionname'=>'/rbac/permission', 'methodname'=>'get',                     'lft'=>14, 'rgt'=>15, 'lvl'=>2 ],
        [ 'permissionname'=>'增加角色',   'actionname'=>'/rbac/role',       'methodname'=>'post',                    'lft'=>16, 'rgt'=>17, 'lvl'=>2 ],
        [ 'permissionname'=>'删除角色',   'actionname'=>'/rbac/role',       'methodname'=>'delete',                  'lft'=>18, 'rgt'=>19, 'lvl'=>2 ],
        [ 'permissionname'=>'修改角色',   'actionname'=>'/rbac/role',       'methodname'=>'put',                     'lft'=>20, 'rgt'=>21, 'lvl'=>2 ],
        [ 'permissionname'=>'查询角色',   'actionname'=>'/rbac/role',       'methodname'=>'get',                     'lft'=>22, 'rgt'=>23, 'lvl'=>2 ],
        [ 'permissionname'=>'增加UA',     'actionname'=>'/rbac/ua',         'methodname'=>'post',                    'lft'=>24, 'rgt'=>25, 'lvl'=>2 ],
        [ 'permissionname'=>'删除UA',     'actionname'=>'/rbac/ua',         'methodname'=>'delete',                  'lft'=>26, 'rgt'=>27, 'lvl'=>2 ],
        [ 'permissionname'=>'查询UA',     'actionname'=>'/rbac/ua',         'methodname'=>'get',                     'lft'=>28, 'rgt'=>29, 'lvl'=>2 ],
      ]);
    }catch( Exception $e ){
      return Response::withJson([ 'errno'=> $errno.'01', 'errmsg'=>'建表错误：' . $tb ]);
    }

    try{
      $tb = 'rbac_roles';
      $db->exec( 'DROP TABLE IF EXISTS `' . $tb . '`' );
      $db->exec( "CREATE TABLE `$tb` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
        `rolename` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色名',
        `lft` int(10) unsigned NOT NULL COMMENT '预排序遍历树：左值',
        `rgt` int(10) unsigned NOT NULL COMMENT '预排序遍历树：右值',
        `lvl` int(10) unsigned NOT NULL COMMENT '预排序遍历树：深度',
        PRIMARY KEY (`id`),
        UNIQUE KEY `rolename` (`rolename`,`lvl`),
        KEY `lft` (`lft`),
        KEY `rgt` (`rgt`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色表'" );
      DB::name( $tb )->add([ 'rolename'=>'顶级管理员', 'lft'=>1, 'rgt'=>2, 'lvl'=>1 ]);
    }catch( Exception $e ){
      return Response::withJson([ 'errno'=> $errno.'02', 'errmsg'=>'建表错误：' . $tb ]);
    }

    try{
      $tb = 'rbac_ua';
      $db->exec( 'DROP TABLE IF EXISTS `' . $tb . '`' );
      $db->exec( "CREATE TABLE `$tb` (
        `uid` int(10) unsigned NOT NULL COMMENT 'users.id',
        `rid` int(10) unsigned NOT NULL COMMENT 'roles.id',
        UNIQUE KEY `uid_rid` (`uid`,`rid`),
        KEY `uid` (`uid`),
        KEY `rid` (`rid`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户-角色表'" );
      // 第一个用户默认获得顶级权限角色
      DB::name( $tb )->add([ 'uid'=>1, 'rid'=>1 ]);
    }catch( Exception $e ){
      return Response::withJson([ 'errno'=> $errno.'03', 'errmsg'=>'建表错误：' . $tb ]);
    }
    Response::withHtml( 'Done' );
  }
}
