<?php
/**
 * 权限管理：rbac
 * UA相关
 *
 * 错误码：04
 *
 * @author    姚斌  <yb3616@126>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */

namespace Apps\rbac;

use Exception;
use YF\App;
use YF\DB;
use YF\Response;
use YF\Request;
use YF\User;

class UA
{
  /**
   * 错误码
   */
  static private $_errno = '04';

  /**
   * 错误码：00
   */
  public function create()
  {
    // 错误码
    $errno = Base::$errno . self::$_errno . '00';

    // 获得参数
    list( $param, $err ) = Request::param([
      'uid|'           => '请输入 uid',
      'uid|int'        => 'uid 非整数',
      'uid|compare:0,' => 'uid 非正数',
      'rid|'           => '请输入 rid',
      'rid|int'        => 'rid 非整数',
      'rid|compare:0,' => 'rid 非正数',
    ]);
    if( false === $err ){
      return Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>$param ]);
    }

    // 检查是否存在该 uid
    if( DB::name( 'users' )->where([ 'id'=>$param['uid'] ])->count() < 1 ){
      return Response::withJson([ 'errno'=>$errno.'01', 'errmsg'=>'不存在该用户 ID' ]);
    }

    // 检查时候存在该 rid
    if( DB::name( 'rbac_roles' )->where([ 'id'=>$param['rid'] ])->count() < 1 ){
      return Response::withJson([ 'errno'=>$errno.'02', 'errmsg'=>'不存在该角色 ID' ]);
    }

    // 入库
    try{
      $num = DB::name( 'rbac_ua' )->add( $param );
    }catch( Exception $e ){
      return Response::withJson([ 'errno'=>$errno.'03', 'errmsg'=>'已存在该映射关系' ]);
    }

    if( $num < 1 ){
      return Response::withJson([ 'errno'=>$errno.'04', 'errmsg'=>'入库失败？' ]);
    }
    Response::withJson([ 'errno'=>0, 'msg'=>'添加成功' ]);
  }

  /**
   * 错误码：01
   */
  public function delete()
  {
    // 错误码
    $errno = Base::$errno . self::$_errno . '01';

    list( $param, $err ) = Request::param([
      'uid|'           => '请输入 uid',
      'uid|int'        => 'uid 非整数',
      'uid|compare:0,' => 'uid 非正数',
      'rid|'           => '请输入 rid',
      'rid|int'        => 'rid 非整数',
      'rid|compare:0,' => 'rid 非正数',
    ]);
    if( false === $err ){
      return Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>$param ]);
    }

    // 删除数据
    try{
      $num = DB::name( 'rbac_ua' )->delete( $param );
    }catch( Exception $e ){
      return Response::withJson([ 'errno'=>$errno.'01', 'errmsg'=>'未知数据库错误' ]);
    }

    if( $num < 1 ){
      return Response::withJson([ 'errno'=>$errno.'02', 'errmsg'=>'删除失败？' ]);
    }
    Response::withJson([ 'errno'=>0, 'msg'=>'删除成功' ]);
  }

  public function find()
  {
    // 实时获得数据库中的数据
    $result = DB::name( 'rbac_ua' )
      ->field( 'rid' )
      ->where([ 'uid'=>User::id() ])
      ->select();
    Response::withJson([ 'errno'=>0, 'data'=>$result ]);
  }
}
