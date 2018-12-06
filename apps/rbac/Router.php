<?php
/**
 * 组件路由
 *
 * @author    姚斌 <yb3616@126.com>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */
namespace Apps\rbac;

use YF\Router as R;
use Apps\rbac\Base;

class Router
{
  static public function run( R $r )
  {
    // 每个对外方法都需添加以下判断用来禁用当前组件
    if( Base::$isForbidden ){
      return;
    }

    // 权限控制
    $r->cli( 'rbacInit', 'Apps/rbac/Init/createEnv' );
    $r->group('/rbac', function( R $g ) {
      $g->post  ( '/ua',         'Apps/rbac/UA/create' );
      $g->post  ( '/role',       'Apps/rbac/Role/create' );
      $g->post  ( '/pa',         'Apps/rbac/PA/create' );
      $g->post  ( '/permission', 'Apps/rbac/Permission/create' );
      $g->delete( '/ua',         'Apps/rbac/UA/delete' );
      $g->delete( '/role',       'Apps/rbac/Role/delete' );
      $g->delete( '/pa',         'Apps/rbac/PA/delete' );
      $g->delete( '/permission', 'Apps/rbac/Permission/delete' );
      $g->put   ( '/role',       'Apps/rbac/Role/modify' );
      $g->put   ( '/permission', 'Apps/rbac/Permission/modify' );
      $g->get   ( '/ua',         'Apps/rbac/UA/find' );
      $g->get   ( '/role',       'Apps/rbac/Role/find' );
      $g->get   ( '/pa',         'Apps/rbac/PA/find' );
      $g->get   ( '/permission', 'Apps/rbac/Permission/find' );
    }, 'Apps/rbac/Middleware/check');
  }
}
