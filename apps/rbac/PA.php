<?php
/**
 * 权限管理：rbac
 * PA 相关
 *
 * 错误码：01
 *
 * @author    姚斌  <yb3616@126>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */
namespace Apps\rbac;

use Exception;
use YF\DB;
use YF\MPTTA;
use YF\Response;
use YF\Request;
use YF\User;

class PA
{
  /**
   * 错误码
   */
  static private $_errno = '01';

  /**
   * 错误码：00
   */
  public function create()
  {
    // 错误码
    $errno = Base::$errno . self::$_errno . '00';

    // 获得参数
    list( $param, $err ) = Request::param([
      'pid|'           => '请输入 pid',
      'pid|int'        => 'pid 非整数',
      'pid|compare:0,' => 'pid 非正数',
      'rid|'           => '请输入 rid',
      'rid|int'        => 'rid 非整数',
      'rid|compare:0,' => 'rid 非正数',
    ]);
    if( false === $err ){
      return Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>$param ]);
    }

    // 检查是否存在该 pid
    $param['pid'] = intval( $param['pid'] );
    if( DB::name( 'rbac_permissions' )->where([ 'id'=>$param['pid'] ])->count() < 1 ){
      return Response::withJson([ 'errno'=>$errno.'01', 'errmsg'=>'不存在该权限 ID' ]);
    }

    // 检查时候存在该 rid
    $param['rid'] = intval( $param['rid'] );
    if( DB::name( 'rbac_roles' )->where([ 'id'=>$param['rid'] ])->count() < 1 ){
      return Response::withJson([ 'errno'=>$errno.'02', 'errmsg'=>'不存在该角色 ID' ]);
    }

    // 检查父角色是否具有该资源访问权限
    // 注：任何权限添加行为，首先要添加顶级角色相应的权限，再往下逐级添加
    // 注：允许顶级角色添加任意权限
    $rids = User::roles(2);
    if( !in_array( 1, $rids ) && !in_array( $param['rid'], $rids ) ){
      // 查找父权限id
      return Response::withJson([ 'errno'=>$errno.'03', 'errmsg'=>'当前角色权限不能超过其父角色权限范围' ]);
    }

    // 若待添加资源有下级资源，则默认将其下级资源添加到该角色
    // 检查本资源的所有下级子资源
    $ids = MPTTA::findAllChildren( $param['pid'], 'id', 'rbac_permissions' );

    $data[] = $param;
    foreach( $ids as $id ){
      $data[] = [
        'rid' => $param['rid'],
        'pid' => intval( $id['id'] ),
      ];
    }

    // 入库
    try{
      $num = DB::name( 'rbac_pa' )->add( $data );
    }catch( Exception $e ){
      return Response::withJson([ 'errno'=>$errno.'04', 'errmsg'=>'已存在该映射关系' ]);
    }

    Response::withHeader('num', $num);
    if( $num < 1 ){
      return Response::withJson([ 'errno'=>$errno.'05', 'errmsg'=>'入库失败？' ]);
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
      'pid|'           => '请输入 pid',
      'pid|int'        => 'pid 非整数',
      'pid|compare:0,' => 'pid 非正数',
      'rid|'           => '请输入 rid',
      'rid|int'        => 'rid 非整数',
      'rid|compare:0,' => 'rid 非正数',
    ]);
    if( false === $err ){
      return Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>$param ]);
    }

    $param['pid'] = intval( $param['pid'] );
    $param['rid'] = intval( $param['rid'] );

    // 检查父角色是否具有该资源访问权限
    // 注：任何权限添加行为，首先要添加顶级角色相应的权限，再往下逐级添加
    // 注：允许顶级角色添加任意权限
    $rids = User::roles(2);
    if( !in_array( 1, $rids ) && !in_array( $param['rid'], $rids ) ){
      // 查找父权限id
      return Response::withJson([ 'errno'=>$errno.'01', 'errmsg'=>'当前角色权限不能超过其父角色权限范围' ]);
    }

    // 若待添加资源有下级资源，则默认将其下级资源添加到该角色
    // 检查本资源的所有下级子资源
    $ids = MPTTA::findAllChildren( $param['pid'], 'id', 'rbac_permissions' );

    $data[] = $param;
    foreach( $ids as $id ){
      $data[] = [
        'rid' => $param['rid'],
        'pid' => intval( $id['id'] ),
      ];
    }

    // 删除数据
    try{
      $num = DB::name( 'rbac_pa' )->delete( $data );
    }catch( Exception $e ){
      return Response::withJson([ 'errno'=>$errno.'02', 'errmsg'=>'未知数据库错误' ]);
    }

    if( $num < 1 ){
      return Response::withJson([ 'errno'=>$errno.'03', 'errmsg'=>'删除失败？' ]);
    }
    Response::withJson([ 'errno'=>0, 'msg'=>'删除成功' ]);
  }

  public function find()
  {
    $rids = User::roles(2);
    $result = DB::name( 'rbac_pa' )
      ->field( 'pid' )
      ->whereIn([ 'rid'=>$rids ])
      ->select();

    return Response::withJson([ 'errno'=>0, 'data'=>$result ]);
  }
}
