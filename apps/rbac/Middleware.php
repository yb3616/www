<?php
/**
 * 权限管理中间件
 * 错误码：00
 *
 * @author    姚斌  <yb3616@126.com>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */
namespace Apps\rbac;

use Closure;
use YF\DB;
use YF\Request;
use YF\Response;
use YF\User;
use Apps\rbac\Base;

class Middleware
{
  /**
   * 错误码
   */
  static private $_errno = '00';

  /**
   * 错误码：10600xx
   */
  public function check( Closure $next )
  {
    // 错误码
    $errno = Base::$errno . self::$_errno . '00';

    // 每个对外方法都需添加以下判断用来禁用当前组件
    if( Base::$isForbidden ){
      $next();
      return;
    }

    // 检查权限表，若有则鉴权
    $result = DB::name( 'rbac_permissions' )
      ->field( 'id' )
      ->where([
        'actionname' => Request::getURI(),
        'methodname' => Request::getMethod(),
      ])
      ->find();

    // 鉴权（过滤掉顶级角色）
    if( !empty( $result ) && !in_array( 1, User::roles(0) ) ){
      if( User::isGuest() ){
        return Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>'未登录' ]);
      }
      $rid_r = DB::name( 'rbac_pa' )
        ->field( 'rid' )
        ->where([ 'pid'=>$result['id'] ])
        ->select();
      $rids = [];
      foreach( $rid_r as $rows ){
        $rids[] = intval( $rows['rid'] );
      }
      if( empty( array_intersect( User::roles(2), $rids ) ) ){
        // 返回无权限警告
        return Response::withJson([ 'errno'=>$errno.'01', 'errmsg'=>'无权限' ]);
      }
    }

    $next();
  }
}
