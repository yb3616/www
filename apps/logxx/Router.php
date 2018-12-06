<?php
/**
 * 组件路由
 *
 * @author    姚斌 <yb3616@126.com>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */
namespace Apps\logxx;

use YF\Router as R;
use Apps\logxx\Base;

class Router
{
  static public function run( R $r )
  {
    // 每个对外方法都需添加以下判断用来禁用当前组件
    if( Base::$isForbidden ){
      return;
    }

    // 登录注册
    $r->post( '/register',        'Apps/logxx/Controller/register',        'Apps/logxx/Middleware/isGuest' );
    $r->get ( '/login',           'Apps/logxx/Controller/login',           'Apps/logxx/Middleware/isGuest' );
    $r->get ( '/loginWithCookie', 'Apps/logxx/Controller/loginWithCookie', 'Apps/logxx/Middleware/isGuest' );
    $r->get ( '/logout',          'Apps/logxx/Controller/logout',          'Apps/logxx/Middleware/isLogged' );
  }
}
