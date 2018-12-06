<?php
/**
 * 路由
 *
 * @author    姚斌 <yb3616@126.com>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */
use YF\Router;
use YF\Response;

// 组件路由
use Apps\rbac\Router as RBAC;
use Apps\logxx\Router as Logxx;

return function ( Router $r )
{
  // 对之后的路由启用访问日志功能
  $r->add( 'YF/Middleware/AccessLog' );

  // 首页
  $r->get('/', function() {
    Response::withJson([ 'Hello', 'World' ]);
  });

  // 登录注册
  $r->group('/logxx', function( Router $g ) {
    Logxx::run( $g );
  });

  // 权限管理
  RBAC::run( $r );

  // 404
  $r->miss(function() {
    Response::withJson([ 'errmsg' => 404 ]);
  });
};
