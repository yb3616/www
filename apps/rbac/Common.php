<?php
/**
 * 公共方法
 * 存放公共方法以及供外部调用的方法
 *
 * @author    姚斌  <yb3616@126.com>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */
namespace Apps\rbac;

use YF\DB;
use YF\MPTTA;
use YF\User;
use Apps\rbac\Base;

class Common
{
  /**
   * 根据用户 ID 查找角色
   *
   * @param   $id   int   用户主键
   *
   * @return  array 二维数组，第一个数组为直接角色，第二个数组为下级所有角色
   */
  static public function getRids( int $id ) : array
  {
    // 每个对外方法都需添加以下判断用来禁用当前组件
    if( Base::$isForbidden ){
      return [];
    }

    // 查找直接角色
    $rids_r = DB::name( 'rbac_ua' )
      ->field( 'rid' )
      ->where([ 'uid'=>$id ])
      ->select();

    // 整理找到的数据
    $rids = [];
    foreach( $rids_r as $v ){
      $rids[] = intval( $v['rid'] );
    }

    // 查找所有下级角色
    $arids_r = MPTTA::findAllChildren( $rids, 'id', 'rbac_roles' );

    // 整理找到的数据
    $arids = [];
    foreach( $arids_r as $v ){
      $arids[] = intval( $v['id'] );
    }

    // 返回数组
    return [$rids, $arids];
  }
}
