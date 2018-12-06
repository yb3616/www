<?php
/**
 * 判断用户是否登录中间件
 *
 * @author    姚斌  <yb3616@126.com>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */
namespace Apps\logxx;

use Closure;
use YF\User;
use YF\Response;
use Apps\logxx\Base;

class Middleware
{
  /**
   * 当前控制器错误码
   */
  static private $errno = '01';

  /**
   * 中间件
   * 过滤去除已登录用户的操作
   * 错误码：00
   *
   * @param   $app    App
   *
   * @return
   */
  public function isGuest( Closure $next )
  {
    // 错误码
    $errno = Base::$errno . self::$errno . '00';

    // 每个对外方法都需添加以下判断用来禁用当前组件
    if( Base::$isForbidden ){
      $next();
      return;
    }

    if( User::isGuest() ){
      $next();
    }else{
      Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>'用户已登录' ]);
    }
  }

  /**
   * 中间件
   * 过滤去除未登录用户的操作
   * 错误码：01
   *
   * @param   $app    App
   *
   * @return
   */
  public function isLogged( Closure $next )
  {
    // 错误码
    $errno = Base::$errno . self::$errno . '01';

    // 每个对外方法都需添加以下判断用来禁用当前组件
    if( Base::$isForbidden ){
      $next();
      return;
    }

    if( User::isGuest() ){
      Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>'用户未登录' ]);
    }else{
      $next();
    }
  }
}
