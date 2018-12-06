<?php
/**
 * 权限管理：rbac
 * 资源相关
 *
 * 错误码：02
 *
 * @author    姚斌  <yb3616@126>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */
namespace Apps\rbac;

use Closure;
use Exception;
use YF\DB;
use YF\MPTTA;
use YF\Response;
use YF\Request;
use YF\User;

class Permission
{
  /**
   * 错误码
   */
  static private $_errno = '02';

  /**
   * 错误码：00
   */
  public function create()
  {
    // 错误码
    $errno = Base::$errno . self::$_errno . '00';

    // 获得参数
    list( $param, $err ) = Request::param([
      'permissionname|'            => '请输入 permissionname',
      'permissionname|length:2,37' => 'permissionname 长度须在 2~37 字符之间',
      'actionname|length:0,50'     => 'actionname 长度须在 0~50 字符之间',
      'actionname|default:',
      'methodname|length:0,50'     => 'methodname 长度须在 0~50 字符之间',
      'methodname|default:',
      'pid|'                       => '请输入 pid',
      'pid|int'                    => 'pid 非整数',
      'pid|compare:0,'             => 'pid 非正数',
    ]);
    if( false === $err ){
      return Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>$param ]);
    }

    // 检查pid的父权限
    $pid   = intval( $param['pid'] );
    unset( $param['pid'] );

    if( 0 === $pid ){
      // 1. 添加顶级权限
      // 第一参数为父 id，若顶级则为 0
      MPTTA::addBrother( 0, $param, 'rbac_permissions' );
      return Response::withJson([ 'errno'=>0, 'msg'=>'添加成功' ]);
    }

    // 2. 添加次级权限
    $r = MPTTA::addChindFunc( function( Closure $next ) use( $pid, $param ) : bool {
      $result = DB::name( 'rbac_permissions' )
        ->field( 'actionname,methodname' )
        ->find([ 'id'=>$pid ]);
      if( empty( $result ) ){
        Response::withJson([ 'errno'=>$errno.'01', 'errmsg'=>'pid 异常' ]);
        return false;
      }

      // 检查权限: method
      $checkMethod = function() use( $param, $result ) : bool {
        if( '' !== $param['methodname'] ){
          $methodname = array_map( function( $v ){
            return strtolower( trim( $v ) );
          }, explode( ',', $param['methodname'] ) );
          if( array_diff( $methodname, explode( ',', $result['methodname'] ) ) ){
            return false;
          }
        }
        return true;
      };

      if( !in_array( 1, User::roles(0) ) ){
        if( false===$this->_checkRole( $pid ) ){
          Response::withJson([ 'errno'=>$errno.'02', 'errmsg'=>'当前用户无此权限' ]);
        }elseif( false===$checkMethod() ){
          Response::withJson([ 'errno'=>$errno.'03', 'errmsg'=>'不允许的操作：method' ]);
        }else{
          return true;
        }
        return false;
      }

      // 处理 actionname(合并)
      if( !empty( $param['actionname'] ) ){
        $param['actionname'] = implode( '/', array_merge( explode( '/', $result['actionname'] ), explode( '/', $param['actionname'] ) ) );
      }else{
        $param['actionname'] = $result['actionname'];
      }
      if( true === $next( $pid, $param, 'rbac_permissions' ) ){
        return true;
      }
      Response::withJson([ 'errno'=>$errno.'04', 'errmsg'=>'pid 无效' ]);
      return false;
    } );
    if( true === $r ){
      Response::withJson([ 'errno'=>0, 'msg'=>'添加成功' ]);
    }
  }

  /**
   * 检查当前角色是否有对该资源的父资源有修改权限
   *
   * @param   $pid  int     资源id
   *
   * @return  bool
   */
  private function _checkRole( int $pid ) : bool
  {
    return DB::name('rbac_pa')->whereIn(['rid'=>User::roles(2)])->where(['pid'=>$pid])->count()>0;
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

    $r = MPTTA::deleteFunc( function( Closure $next ) use( $id ) : bool {
      // 当前用户角色是否有对该资源的权限
      if( User::id() !== 1 && $this->_checkRole( $id ) === false ){
        Response::withJson([ 'errno'=>$errno.'01', 'msg'=>'当前用户无此权限' ]);
        return false;
      }

      $result = MPTTA::findAllChildren( $id, 'id', 'rbac_permissions' );

      $cids = [];
      foreach( $result as $v ){
        $cids[] = [ 'pid' => intval( $v['id'] ) ];
      }

      // 删除
      if( !empty( $cids ) ){
        DB::name( 'rbac_pa' )->delete( $cids );
      }
      return $next( $id, 'rbac_permissions' );
    } );

    if( true===$r ){
      Response::withJson([ 'errno'=>0, 'msg'=>'删除成功' ]);
    }
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
      'permissionname|'            => '请输入 permissionname',
      'permissionname|length:2,37' => 'permissionname 长度须在 2~37 字符之间',
      'id|'                        => '请输入 id',
      'id|int'                     => 'id 非整数',
      'id|compare:0,'              => 'id 非正数',
    ]);
    if( false === $err ){
      return Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>$param ]);
    }

    // 检查当前用户角色是否能访问该资源
    $rids = User::roles(2);
    $id = intval( $param['id'] );
    if( !in_array( 1, User::roles(0) ) ){
      if( false===$this->_checkRole( $id ) ){
        return Response::withJson([ 'errno'=>$errno.'01', 'errmsg'=>'无权限' ]);
      }
    }

    // 入库
    try{
      $num = DB::name( 'rbac_permissions' )
        ->where([ 'id'=>$param['id'] ])
        ->update( $param );
    }catch( Exception $e ){
      return Response::withJson([ 'errno'=>$errno.'02', 'errmsg'=>'sql 错误' ]);
    }

    if( $num < 1 ){
      return Response::withJson([ 'errno'=>$errno.'03', 'errmsg'=>'修改失败？' ]);
    }
    Response::withJson([ 'errno'=>0, 'msg'=>'修改成功' ]);
  }

  /**
   * 查询当前用户所有资源信息
   */
  public function find()
  {
    $rids = User::roles(2);
    if( !in_array( 1, $rids ) ){
      DB::whereIn([ 'b.rid' => $rids ]);
    }
    $result = DB::name( 'rbac_permissions a' )
      ->field( 'a.id,a.permissionname,a.actionname,a.methodname' )
      ->leftJoin([ 'rbac_pa b' => 'a.id=b.pid' ])
      ->select();
    Response::withJson([ 'errno'=>0, 'data'=>$result ]);
  }
}
