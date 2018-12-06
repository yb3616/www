<?php
/**
 * 用户登录相关操作
 *
 * @author    姚斌  <yb3616@126.com>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */
namespace Apps\logxx;

use Closure;
use YF\DB;
use YF\User;
use YF\Request;
use YF\Response;
use Apps\rbac\Common as RBAC;
use Apps\logxx\Base;

class Controller
{
  /**
   * 当前控制器错误码
   */
  static private $_errno = '00';

  /**
   * 注册
   * 错误码：00
   *
   * @return
   */
  public function register()
  {
    // 错误码
    $errno = Base::$errno . self::$_errno . '00';

    // 接受参数
    list( $data, $err ) = Request::param([
      'username|'          => '请输入用户名',
      'username|length:4,' => '用户名须大于等于 4 个字符',
      'password|'          => '请输入密码',
      'password|length:6,' => '密码须大于等于 6 个字符',
    ]);
    if( false === $err ){
      return Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>$data ]);
    }

    // 查找用户名是否存在
    // 为避免主键无端自增，尽量避免 try catch 操作
    if( DB::name( 'users' )
      ->where([ 'username'=>$data['username'] ])
      ->count() > 0 ){
      // 用户名已存在，返回错误信息
      return Response::withJson([ 'errno'=>$errno.'01', 'errmsg'=>'用户名已存在' ]);
    }

    // 当前用户名可用
    // 整理数据
    $sqldata = [
      'username'        => $data['username'],
      'password'        => User::password_hash( $data['password'] ),
      'password_cookie' => md5( time() . rand( 10000, 99999 ) ),
    ];

    // 入库
    if( DB::name( 'users' )
      ->add( $sqldata ) === 1 ){
      return Response::withJson([ 'errno'=>0, 'msg'=>'注册成功' ]);
    }else{
      return Response::withJson([ 'errno'=>$errno.'02', 'errmsg'=>'注册失败？' ]);
    }
  }

  /**
   * 登录操作
   * 错误码：01
   *
   * @return
   */
  public function login()
  {
    // 错误码
    $errno = Base::$errno . self::$_errno . '01';

    // 接受参数
    list( $data, $err ) = Request::param([
      'username|'          => '请输入用户名',
      'username|length:4,' => '用户名须大于等于 4 个字符',
      'password|'          => '请输入密码',
      'password|length:6,' => '密码须大于等于 6 个字符',
    ]);
    if( false === $err ){
      return Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>$data ]);
    }

    // 查找用户
    $result = DB::name( 'users' )
      ->field( 'id, password, password_cookie' )
      ->where([ 'username' => $data['username'] ])
      ->find();
    if( empty( $result ) ){
      return Response::withJson([ 'errno'=>$errno.'01', 'errmsg'=>'用户不存在' ]);
    }

    // 验证密码
    if( !User::password_verify( $data['password'], $result['password'] ) ){
      return Response::withJson([ 'errno'=>$errno.'02', 'errmsg'=>'密码不匹配' ]);
    }

    // 查找角色列表
    $rids = RBAC::getRids( $result['id'] );

    // 保存会话
    // 第三参数参考 \YF\User::login 说明
    User::login( $result['id'], $result['password_cookie'], ['rids' => $rids] );
    Response::withJson([ 'errno'=>0, 'msg'=>'登录成功' ]);
  }

  /**
   * 免密登录操作
   * 错误码：02
   *
   * @return
   */
  public function loginWithCookie()
  {
    // 错误码
    $errno = Base::$errno . self::$_errno . '02';

    // 获得数据
    $data = User::getCookiePasswordData();
    if( false === $data ){
      return Response::withJson([ 'errno'=>$errno.'00', 'errmsg'=>'cookies 数据异常' ]);
    }

    // 查找数据
    $result = DB::name( 'users' )
      ->field( 'id, password_cookie' )
      ->where([ 'id'=>$data['uid'] ])
      ->find();
    if( empty( $result ) ){
      return Response::withJson([ 'errno'=>$errno.'01', 'errmsg'=>'用户不存在' ]);
    }

    // 分析数据
    if( !User::checkCookiePassword( $data['password_cookie'], $result['password_cookie'] ) ){
      return Response::withJson([ 'errno'=>$errno.'02', 'errmsg'=>'密码不匹配' ]);
    }

    // 查找角色列表
    $rids = RBAC::getRids( $data['uid'] );

    // 登录
    // 保存会话
    // 第三参数参考 \YF\User::login 说明
    User::login( $result['id'], $result['password_cookie'], ['rids' => $rids] );
    Response::withJson([ 'errno'=>0, 'msg'=>'登录成功' ]);
  }

  /**
   * 注销操作
   *
   * @return
   */
  public function logout()
  {
    User::logout();
    return Response::withJson([ 'errno'=>0, 'msg'=>'注销成功' ]);
  }
}
