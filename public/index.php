<?php
/**
 * 项目入口
 *
 * @author    姚斌 <yb3616@126.com>
 * @License   https://github.com/yb3616/www/blob/master/LICENSE
 * @Copyright (c) 2018 姚斌
 */
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// 运行程序
YF\App::run( '/router.php' );
