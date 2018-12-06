<?php
/**
 * 配置文件
 *
 * @author    姚斌  <yb3616@126.com>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */
namespace Apps\rbac;

use YF\User;
use Apps\rbac\Common;

class Base
{
  /**
   * 是否禁用本组件
   */
  static public $isForbidden = false;

  /**
   * 错误码
   */
  static public $errno = '101';

  /**
   * 刷新 session 中的角色列表
   */
  final static public function flushRoles()
  {
    // 每个对外方法都需添加以下判断用来禁用当前组件
    if( self::$isForbidden ){
      return [];
    }

    User::flushRoles( Common::getRids( User::id() ) );
  }
}
