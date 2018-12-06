<?php
/**
 * 权限管理：rbac
 * 角色相关
 *
 * 错误码：03
 *
 * @author    姚斌  <yb3616@126>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */
namespace Apps\rbac;

use Exception;
use Apps\rbac\Base;
use YF\DB;
use YF\MPTTA;
use YF\Response;
use YF\Request;
use YF\User;

class Role
{
  /**
   * 错误码
   */
  static private $_errno = '03';

  /**
   * 错误码：00
   */
  public function create()
  {
    // 错误码
    $errno = Base::$errno . self::$_errno . '00';

    // 获得参数
    list( $param, $err ) = Request::param([
      'rolename|'            => '请输入 rolename',
      'rolename|length:2,37' => 'rolename 长度须在 2~37 字符之间',
      'id|'                  => '请输入 id', // id 为当前用户的角色之一，由当前用户指定
      'id|int'               => 'id 非整数',
      'id|compare:0,'        => 'id 非正数',
    ]);
    if( false === $err ){
      return Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>$param ]);
    }

    // 检查id是否在当前用户角色之下
    // 无须考虑高并发下用户角色被修改的情况（可能性太小, 且用户角色写入session，实时性不强）
    $id  = intval( $param['id'] );
    Base::flushRoles();
    if( !in_array( $id, User::roles(2) ) ){
      return Response::withJson([ 'errno'=>$errno.'01', 'errmsg'=>'id 异常' ]);
    }

    // 检查 rolename 是否重复
    if( DB::name( 'rbac_roles' )->where([ 'rolename' => $param['rolename'] ])->count() > 0 ){
      return Response::withJson([ 'errno'=>$errno.'02', 'errmsg'=>'rolename 重复' ]);
    }

    // 入库
    if( false === MPTTA::addChild( $id, [ 'rolename' => $param['rolename'] ], 'rbac_roles' ) ){
      return Response::withJson([ 'errno'=>$errno.'03', 'errmsg'=>'入库失败' ]);
    }
    Base::flushRoles();
    return Response::withJson([ 'errno'=>0, 'msg'=>'添加成功' ]);
  }

  /**
   * 错误码：01
   */
  public function delete()
  {
    // 错误码
    $errno = Base::$errno . self::$_errno . '01';

    list( $param, $err ) = Request::param([
      'id|'           => '请输入 id',
      'id|int'        => 'id 非整数',
      'id|compare:0,' => 'id 非正数',
    ]);
    if( false === $err ){
      return Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>$param ]);
    }

    $id = intval( $param['id'] );
    if( $id === 1 ){
      return Response::withJson([ 'errno'=>$errno.'01', 'errmsg'=>'无法删除顶级角色' ]);
    }

    // 检查id是否在当前用户角色之下
    Base::flushRoles();
    if( !in_array( $id, $ids = User::roles(2) ) ){
      return Response::withJson([ 'errno'=>$errno.'02', 'errmsg'=>'id 异常' ]);
    }

    // 删除数据
    if( false === MPTTA::delete( $id, 'rbac_roles' ) ){
      return Response::withJson([ 'errno'=>$errno.'03', 'errmsg'=>'删除失败' ]);
    }
    Base::flushRoles();
    return Response::withJson([ 'errno'=>0, 'msg'=>'删除成功' ]);
  }

  /**
   * 错误码：02
   */
  public function modify()
  {
    // 错误码
    $errno = Base::$errno . self::$_errno . '02';

    // 获得参数
    list( $param, $err ) = Request::param([
      'rolename|'            => '请输入 rolename',
      'rolename|length:2,37' => 'rolename 长度须在 2~37 字符之间',
      'id|'                  => '请输入 id',
      'id|int'               => 'id 非整数',
      'id|compare:0,'        => 'id 非正数',
    ]);
    if( false === $err ){
      return Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>$param ]);
    }

    $param['id'] = intval( $param['id'] );
    // 检查id是否在当前用户角色之下
    Base::flushRoles();
    if( !in_array( $param['id'], User::roles(2) ) ){
      return Response::withJson([ 'errno'=>$errno.'01', 'errmsg'=>'id 异常' ]);
    }

    // 修改
    try{
      $num = DB::name( 'rbac_roles' )
        ->where([ 'id'=>$param[ 'id' ] ])
        ->update( $param );
    }catch( Exception $e ){
      return Response::withJson([ 'errno'=>$errno.'02', 'errmsg'=>'sql 错误' ]);
    }

    if( $num < 1 ){
      return Response::withJson([ 'errno'=>$errno.'03', 'errmsg'=>'修改失败' ]);
    }
    Response::withJson([ 'errno'=>0, 'msg'=>'修改成功' ]);
  }

  public function find()
  {
    Base::flushRoles();
    $result = DB::name( 'rbac_roles' )
      ->field( 'id,rolename' )
      ->whereIn([ 'id'=>User::roles(2) ])
      ->order( 'id' )
      ->select();
    Response::withJson([ 'errno'=>0, 'data'=>$result ]);
  }
}
