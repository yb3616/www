## 前言

​	Your Framework是一个PHP开发框架。针对前后端分离的大趋势，本人开发了以API为主的轻巧框架YF。

​	YF集 App(主体环境)、Cache、Config、Cookie、DB、Exception(错误|异常处理)、Http(请求网络资源)、Log、Middleware(封装了一些常用的中间件)、MPTTA(预排序遍历树算法)、Request、Response、Router、Session、User、Validate等几大功能于一体，可助你快速开发产品。

​	YF核心功能统一使用静态类封装(工厂模式下函数定位不准确)，使用函数跳转功能快速而又准确地找到目标方法，提升开发速度，并大大减轻后期调试的难度。

​	YF还提供了权限控制(RBAC)功能和登录注册功能，其中权限控制功能使用预编译排序算法实现了支资源和角色的继承功能，可避免继承关系越多访问越慢的尴尬情况。

​	YF代码注释较多，请配合源码来浏览本文档。

​	To be continued..







## 目录



1. 安装
2. 项目目录
3. 开发建议
4. 路由
5. 路由中间件
6. 请求
7. 验证器
8. 响应
9. 数据库
10. Session
11. Cookie
12. 配置 Config
13. 缓存 Cache
14. 日志 Log
15. 常用中间件
16. 错误 | 异常处理
17. 用户方法
18. 无限分类（非递归）
19. 网络资源请求
20. File







## 正文



### 1. 安装



#### 1. *安装项目目录*

```bash
git clone https://github.com/yb3616/www
```

#### 2. *安装框架文件*

```bash
cd www
git clone https://github.com/yb3616/yf
```

#### 3. *生成 `vendor` 文件夹*

```bash
composer install
```

#### 4. *给 `runtime` 赋予写权限*

```bash
chmod 777 runtime
```







### 2. 目录说明



**重要！** 请将Http服务根目录指向`/public`文件夹。

​	YF使用`Composer`部署项目，具体参考`composer.json`文件描述，`/apps`为应用程序主目录，`/config`为配置文件目录，`/public`为入口文件目录，`/runtime`为运行时目录，vendor为composer自动生成的目录，`/yf`为框架目录。`/funcs.php`为公共函数文件，`/router.php`为路由文件。

```bash
.
├── apps    （应用文件夹）
│   ├── logxx    （登录注册）
|   |   ├── Base.php          （提供组件变量及公共方法）
│   │   ├── Controller.php    （供路由访问的方法集）
|   |   ├── Router.php        （路由规则）
│   │   └── Middleware.php    （本功能提供的中间件）
│   └── rbac     （鉴权，参考其下的README.md）
│       ├── Base.php
│       ├── Common.php
│       ├── Init.php
│       ├── Middleware.php
│       ├── PA.php
│       ├── Permission.php
│       ├── README.md
│       ├── Role.php
│       ├── Router.php
│       └── UA.php
├── composer.json
├── config    （配置文件夹）
│   ├── app.ini
│   ├── cache.ini
│   ├── cookie.ini
│   ├── db.ini
│   ├── http.ini
│   ├── log.ini
│   ├── mptta.ini
│   ├── request.ini
│   ├── session.ini
│   ├── user.ini
├── funcs.php  （公共函数，不推荐使用）
├── public     （入口文件夹）
│   ├── favicon.ico
│   └── index.php
├── README.md
├── router.php （路由文件）
├── runtime    （运行时目录）
├── vendor
└── yf         （框架目录）
```







### 3. 开发建议



#### 1. *错误码*

​	错误代码用于快速定位到错误（逻辑）点，并排查问题。所以错误代码必须具有唯一性，可读性等特点。推荐使用九位数字表示错误码，其中前三位代表一个组件，接着两位代表组件内的文件（控制器、中间件），接着两位代表方法，最后两位代表方法中的断点。

​	例如：`/apps/logxx`这个组件的唯一错误码为：`100xxxxxx`，`\Apps\logxx\Controller`控制器错误码为：`10000xxxx`，`\Apps\logxx\Controller::register` 方法的错误码为：`	1000000xx`，其参数错误的断点错误码为：`100000000`

#### 2. *项目结构*

​	YF与其说是一个功能大而全的框架，倒不如说是一个功能小而精的工具集合。YF完全不限制你使用何种项目结构，然无规矩不成方圆，在此我推荐一个项目结构。

​	参考`/apps/rbac/README.md`

​	参考`/apps/logxx`目录，`logxx`代表登录注册方法集，其下共有三个PHP文件。其中`Controller.php`为本功能的入口方法集，路由`/router.php`中的业务方法直接指向该文件，`Common.php`为本功能的公共方法和对其他功能提供工具方法的集合，`Middleware.php`是提供的中间件方法集合。

​	若`Controller.php`中的方法过于繁多，就考虑将其按功能相似性拆分成多个文件，具体请参考`/apps/rbac`目录，YF将`Controller.php`按数据表拆分成四个文件，分别为`PA.php`、`Permission.php`、`Role.php`和`UA.php`。另外有一个文件`Init.php`里面是数据表结构+数据的初始化方法，以助于快速搭建数据库。

#### 3. *组件化开发*

> 参考`/apps/rbac`

​	将大表拆成小表，以主键关联，不影响现有表结构。比方说如果你的站点已存在用户表，且已在运营中，此时若你想要添加一个权限管理功能（带角色以及资源的继承功能），用于给部分用户授予部分资源访问权限（比如新闻组只能访问添加新闻板块...）。此时只要启用`/apps/rbac`组件即可，该组件须初始化以生成相应表。请检查你的数据库没有这四张表“rbac_pa”、“rbac_permissions”、“rbac_roles”、“rbac_ua”，第一次启用该组件，请执行

```php
php public/index/ rbacInit
```

​	此时你的数据库将生成上述四张表（注意表前缀）。程序生成一个顶级管理员角色，并绑定到主键为1的用户上（可根据需要自行绑定`rbac_ra`）。根据`/apps/rbac/README.md`操作后即可。

​	组件化开发的核心思想是不修改已有的数据表结构，而重新设计本组件需要的表结构以及数据。只对外部（组件、路由、中间件等...）开放三个文件：Common.php（对外暴露本组件提供的可调用的功能）、Router.php（路由规则，用户全局路由函数）、Middleware.php（路由中间件，供本组件路由以及外部路由使用）。其他文件存放相应的逻辑代码用以充实这三个文件。





### 4. 路由



​	`YF\Router`支持包含`cli`、`post`、`delete`、`put`、`get`在内的五种请求类型。并提供`param`方法和路由分组`group`方法。具体使用请参考`/router.php`路由文件。

#### 1. *闭包支持*

​	路由参数（第二参数）允许闭包（而非字符串）

```php
$r->get('/', function() {
    Response::withJson([ 'foo', 'bar' ]);
}, [ 'Apps/M/t1', 'Apps/M/t2' ]);
```

#### 2. *MISS路由*

> 注：cli模式无MISS路由功能（它不需要这个功能，手动剔除了）

​	miss路由即404重定向，用于定制404页。同时支持组miss路由功能。若未指定miss路由，则默认返回404状态码。

```php
// 全局miss路由
$r->miss(function() {
    Response::withJson([ 'errmsg' => 404 ]);
});
// 路由分组
$r->group('/test', function( Router $g ) {
    // 组miss路由
    $g->miss(function() {
        Response::withJson([ '404 in /test' ]);
    });
    // 路由分组
    $g->group('/depth', function( Router $d ) {
      // 组miss路由
      $d->miss(function() {
          Response::withJson([ '404 in /test/depth' ]);
      });
      // get路由规则
      $d->get('/test', function() {
          Response::withJson([ 'foo', 'bar' ]);
      });
    });
});

```

#### 3. *路由中间件*

​	本路由支持路由中间件功能，并提供`add`方法以对之后的所有路由添加全局路由中间件（注：对`add`方法调用之前的路由规则无效）

​	关于路由中间件的优先级（优先级越高越先执行），全局路由中间件 < 组路由中间件 < 具体路由请求中间件。例如：以下路由规则中，`Apps/Test/m01` > `apps/Test/md02` > `apps/Test/md03`

```php
// 全局路由中间件
// 仅对之后的路由规则开启访问日志功能
// 对 $r->get('/', function)不开启该功能
$r->add( 'Apps/Test/m01' );
// 第三参数为组路由中间件
$r->group('/v1', function( Router $g ){
    // 第三参数为具体路由请求中间件
    $g->get('/test', 'Apps/Test/index', 'Apps/Test/md03');
}, 'Apps/Test/md02');
```

​	中间件参数支持字符串以及数组类型，当其类型为数组时，例如：

```php
$r->get('/', 'Apps/Index/index', [ 'Apps/M/t1', 'Apps/M/t2' ]);
```

​	优先级：`Apps/M/t1` < `Apps/M/t2`

#### 4. *CLI请求方式*

​	YF支持cli访问模式，例如：`$r->cli('rbacInit', 'Apps/rbac/Init/createEnv');`此条规则的使用方法为：`php public/index.php rbacInit`，若要传参，请以`key=value`的形式附加于路由之后，例如：`php public/index.php index foo=bar hello=world`

#### 5. *Fast-cgi请求方式*

​	访问形式`http://domain.com/uri?param1=value1&param2=value2`，为了减轻业务逻辑，以及api对路由美化的依赖极低，目前仅支持此类访问形式。







### 5. 路由中间件



#### 1. *语法*

​	写法请参考`/apps/logxx/Middleware.php`，中间件方法第一参数为闭包，里面包裹的是路由方法以及更高优先级的中间件。**重要！**请手动执行该参数 。`User`类以及`Response`类请参考之后的介绍。

```php
public function isGuest( Closure $next )
{
    if( User::isGuest() ){
        $next();
    }else{
        Response::withJson([ 'errno'=>1000200, 'errmsg'=>'用户已登录' ]);
    }
}
```

#### 2. *用法*

​	参考本文档的 `路由 → 路由中间件` 栏目。







### 6. 请求 Request



#### 1. *请求变量*

​	目前支持`post, get, cli, json, xml`等5种请求方式，建议统一使用`Request::param( string $key )`来接收参数，省得下次更换路由的时候再次修改接受参数的方法。

> 注：若无特殊需求，请勿调用`Request::init( array $config )`方法

#### 2. *常用请求方法*

```php
Request::getMethod();				// 获得请求类型(post | get | delete | put | cli)
Request::getURI();					// 获得请求的uri(不含域名)
Request::getIP();					// 获得用户IP
Request::getHeader( string $key );	// 获得自定义请求头信息
// ... 更多方法待添加
```

#### 3. *参数验证器*

​	参考源码`YF\Request::_getParam( $rule, string $type )`

```php
use YF\Validate;
// ...
static private function _getParam( $rule, string $type )
{
    // ...
    return Validate::verify( self::$_params[$type], $rule );
}
```

​	如上所示，`Request::param`调用了验证器方法，具体使用方法为：

```php
// apps\logxx\Controller\register()

list( $data, $err ) = Request::param([
    // 'key|rule' => '注释'
    'username|'         => '请输入用户名',			// '|' 表示必须输入该参数
    'username|length:4' => '用户名须大于等于4个字符',	 // '|length:4' 验证字符串长度
]);
```

```php
list( $data, $err ) = Request::param([
    'key', 'key|rule', 'key|rule'=>'comment',
]);

list( $data, $err ) = Request::param('key');
```

#### 4. *文件上传*

文件存储根路径请看配置文件`/config/request.ini` 

```php
Request::file( string $filename, array $rules=[] ); // 单个文件上传
Request::files([ $filename => $rules, ... ]);		// 批量上传
```

上传规则：

**1. size（支持单位：'', 'b', 'bit', 'k', 'kb', 'kbit', 'm', 'mb', 'mbit', 'g', 'gb', 'gbit'，不区分大小写）**

```php
$rules = [ 'size'=>'1K,' ];				// 文件须大于1K
$rules = [ 'size'=>',1M' ];				// 文件须小于1M
$rules = [ 'size'=>'1024,2048bit' ];	// 文件须小于2048b、大于1024b
$rules = [ 'size'=>'100b' ];			// 文件须小于100b
```

**2. type**

```php
$rules = [ 'type'=>'gif|jpg' ];			// 文件须为gif或jpg格式
```

**3. path**

设置文件存储路径（相对于存储根路径）

```php
$rules = [ 'path'=>'avatar' ];			// 将文件保存到用户头像文件夹	
```

**4. 返回值**

​	为避免文件夹上传的文件太多而不好整理，本上传方法将所有用户上传文件按`上传根路径/指定文件夹/年/月/文件名`的形式保存。并在`Request::file`、`Request::files`返回保存的绝对路径(string | array)。

> 为防止同文件名异文件无法上传的问题，文件名以`md5(随机数).filename`的形式保存

**5. 举例说明**

​	感觉前面说的太简陋听不懂的就看下面的实例

```php
<?php
namespace Apps\user;
use YF\Request;
use YF\Response;

class Upload
{
    /**
     * 上传用户头像
     */
    public function avatar()
    {
        $filename = Request::file('avatar', [
            'size' => '10K,1M',
            'type' => ['jpg', 'jpeg', 'png'],
            'path' => 'avatar',
        ]);
        Response::withJson(['filepath' => $filename]);
    }
    
    /**
     * 用户上传文件备份
     */
    public function backup()
    {
        $filenames = Request::files([
            'file' => [
            	'size' => '1M',
                'type' => 'txt|pdf',
                'path' => 'backup/file',
      		],
            'image' => [
                'size' => '1M',
                'type' => 'gif|png|jpeg|jpg',
                'path' => 'backup/image',
            ]
        ]);
        Response::withJson(['result' => $filenames]);
    }
}
```









### 7. 验证器



#### 1. *参数验证器用法*

```php
// 1.
list( $data, $err ) = Request::param([
    'key', 'key|rule', 'key|rule'=>'comment',
]);

// 2.
list( $data, $err ) = Request::param('key');

if( false===$err ){
    echo $data; return;
}
```

#### 2. *自定义验证器*

```php
namespace Apps\logxx;

class Validate
{
    public function equal( $data, $rule )
    {
        if( $data === $rule ){
            return true;
        }
        return false;
    }
}
```

#### 3. *参数验证器自定义用法*

```php
list( $data, $err) = Request::param([
    'key|Apps/logxx/Validate/equal' => 'comment'
]);
```

#### 4. *内置验证器规则*

```php
// YF\Validate::_length( string $data, string $params );
// 字符串长度
Request::param(['id|length:3'   => "参数须等于3个字符"]);
Request::param(['id|length:,3'  => "参数须小于等于3个字符"]);
Request::param(['id|length:3,'  => "参数须大于等于3个字符"]);
Request::param(['id|length:3,5' => "参数须大于等于3个字符并小于等于5个字符"]);

// YF\Validate::_compare( string $data, string $params );
// 比较大小(数字类型均可)
Request::param(['id|compare:1'   => 'id须等于1']);
Request::param(['id|compare:1,'  => 'id须大于等于1']);
Request::param(['id|compare:,1'  => 'id须小于等于1']);
Request::param(['id|compare:1,5' => 'id须大于等于1并小于等于5']);

// YF\Validate::_int( string $data, string $params );
// 检查是否为整数
Request::param(['id|int' => 'id须为整数']);

// YF\Validate::_in( string $data, string $params );
// 检查是否包含
Request::param(['id|in:1,2,3,4' => 'id须在（1，2，3，4）之间']);

// ... 其他规则待添加
```

#### 5. *独立验证*

```php
use YF\Validate;
// ...
list( $data, $err ) = Validate::verify( array $data, array $rule );
```









### 8. 响应



#### 1. *常规调用*

​	YF响应统一由静态类`Response`处理。

```php
use YF\Response;
// ...
// 1. 返回 json 数据
Response::withJson( array $data, int $code=200 );
// 2. 返回 html 数据
Response::withHtml( 'Hello, World!' );
// 不建议返回 xml 数据，未封装相应的方法
```

> 响应信息可叠加（多次调用`Response::withJson`(array_merge)、`Response::withHtml`(.=)而不会覆盖之前的数据）
> 响应信息由`YF\App::run()`中调用`Response::send()`时发送。
> 若无特殊情况，请勿执行`Response::send()`方法。

#### 2. *写入头信息*

​	YF 封装了两个静态方法`Response::withHeader`、`Response::withAddedHeader`区别是后一个不会覆盖相同键名的数据，比如：

```php
use YF\Response;
// 响应头中只有 foo hello 的信息
Response::withHeader( 'foo', 'bar' );
Response::withHeader( 'foo', 'hello' );

// 响应头中存在 foo bar 以及 foo hello 信息
Response::withAddedHeader( 'foo', 'bar' );
Response::withAddedHeader( 'foo', 'hello' );
```

#### 3. *设置响应代码*

```php
Response::withStatus( 201 );	// 返回 201 状态码
```

#### 4. *获得响应数据*

```php
Response::data();
```

```php
// /yf/Middleware.php
public function AccessLog( Closure $next )
{
    $next;
    
    Log::set([
        'ip' => Request::getIP(),
        'method' => Request::getMethod(),
        'uri'    => Request::getURI(),
        'params' => Request::param('*'),
        'return' => Response::data(),	// 响应数据
    ], str_replace( '/', '%', $uri), [ 'engine' => 'file', 'path' => '/runtime/access_log' ]);
}
```







### 9. 数据库



​	开发模式下（调试模式），默认将每条数据库操作语句（预处理前）和当前sql语句耗时写入响应头中，以方便开发者调试。

> 注：响应头中仅包含耗时+预处理前的Sql语句，如需打印绑定的数据，请使用 `debug()`方法。
>
> 注：所有操作仅在直接或者间接调用 handle() 方法的时候才会建立数据库连接（并放入对象属性中，以待下次调用）。

#### 1. *数据库配置*

​	支持http以及sock配置，支持多数据库配置，配置文件为`/config/db.ini`、`/config/db_local.ini`，主数据库`[master]`为默认数据库，必须配置。其他数据库可配置可不配置，业务逻辑代码中使用db方法指定数据库配置内容。

```ini
; 数据库配置
[master]
type    = mysql
pre     = pre_
charset = utf8mb4
dbname  = database
host    = localhost
port    = 3306
user    = user
pass    = password

;[read01]
;type    = mysql
;pre     = pre_
;charset = utf8mb4
;dbname  = database
;sock    = /run/mysqld/mysqld.sock
;user    = slave
;pass    = 123456
```

#### 2. *查询数据*

```php
use YF\DB;
DB::name('test')->select();	// 返回二维数组
DB::name('test')->find();	// 添加了Limit 1，返回一维数组

// select 方法接受一参数，表示是否返回总行数，默认 false 不返回
list($data, $count) = DB::name('test')->select(true);

// 分页查询
// 第一参数为当前页数
// 第二参数为返回行数
// 第三参数为是否返回总行数，默认：true返回；
list($data, $count) = DB::name('test')->page(1, 10);
```

#### 3. *添加数据*

```php
use YF\DB;
// 添加一条数据
// 返回成功的条数
DB::name('test')->add(['username'=>'test','password'=>'123456']);

// 批量添加
// 注：批量添加需要所有数据的字段名字和顺序都相同，add 方法不做相应检测
// 返回成功的条数
$s_count = DB::name('test')->add([['un'=>'u1','pd'=>'p1'], ['un'=>'u2','pd'=>'p2']]);
```

#### 4. *删除数据*

```php
use YF\DB;
// TODO
// 把返回值改成删除条数

// 删除一条数据
// 返回布尔值
DB::name('test')->where(['id'=>1])->delete();
DB::name('test')->delete(['id'=>1]);

// 批量删除
// 注：批量删除条件可不相同
// 返回布尔值
DB::name('test')->delete([['id'=>1], ['status'=>1]]);
DB::name('test')->whereOr([['id'=>1], ['status'=>1]])->delete();

// 禁止全删（防误操作）
DB::name('test')->delete(); 	// 返回异常
DB::name('test')->delete(true); // 全删
```

#### 5. *修改数据*

```php
use YF\DB;
// 修改数据
DB::name('test')->where(['id'=>1])->update(['status'=>1]);

// 全部修改
// 第二参数设为：true后，所有数据的 status 都设置成 1；
DB::name('test')->update(['status'=>1], true);

// 禁止全改
// 防止用户误操作
DB::name('test')->update(['status'=>1]); // 返回异常

// 递增
// update `pre_test` set `score`=`score`+1;
DB::name('test')->setInc('score');
// update `pre_test` set `score`=`score`+2;
DB::name('test')->setInc(['score'=>2]);

// 递减
// update `pre_test` set `score`=`score`-1;
DB::name('test')->setDec('score');
// update `pre_test` set `score`=`score`-2;
DB::name('test')->setDec(['score'=>2]);
```

#### 6. *指定表名*

```php
use YF\DB;
DB::name('test')->select();  // 若使用如9.1中的配置：select * from `pre_test`;
DB::table('test')->select(); // 同上配置：select * from `test`;
```

#### 7. *设置字段*

```php
use YF\DB;
// select `id`,`username`,`password` from `pre_test`;
DB::name('test')->field('id,username,password')->select();

// field 第二参数为是否转义参数，默认 true 转义。
// select max(`id`) from `pre_test`;
DB::name('test')->field('max(`id`)', false)->select();

// 多次执行 field 方法
select `id`,`status` from `pre_test`;
DB::name('test')->field('id')->field('status')->select();
```

#### 8. *设置条件*

```php
use YF\DB;
// select * from `pre_test` where `id`=1 limit 1;
DB::name('test')->where(['id'=>1])->find();

// select * from `pre_test` where `id`=1 limit 1;
DB::name('test')->where('id=1')->find();

// where 第二参数为是否转义参数，默认 true 转义
// select * from `pre_test` where `id`>1;
DB::name('test')->where('`id`>1', false)->select();

// between
// select * from `pre_test` where `id` between 1 and 2;
DB::name('test')->whereBetween(['id'=>[1,2]])->select();

// in
// select * from `pre_test` where id in (1,2,3,4);
DB::name('test')->whereIn(['id'=>[1,2,3,4]])->select();

// or
// select * from `pre_test` where `id`=1 or `status`=1;
DB::name('test')->whereOr(['id'=>1, 'status'=>1])->select();
// select * from `pre_test` where `id`=1 or (`status`=1 and `type`="a");
DB::name('test')->where([
    'or'=>[
        'id'  => 1,
        'and' => [
            'status' => 1,
            'type'   => 'a',
        ]
    ]
])->select();

// like
// select * from `pre_test` where `title` like "%foo%";
DB::name('test')->whereLike(['title'=>'%foo%'])->select();

// not
// select * from `pre_test` where `status` <> 1;
DB::name('test')->whereNot(['status'=>1])->select();

// > | <
// select * from `pre_test` where `score` > 100;
DB::name('test')->whereGt(['score'=>100])->select();
// select * from `pre_test` where `score` < 100;
DB::name('test')->whereLt(['score'=>100])->select();

// 多次执行where方法
// select * from `pre_test` where `id`=1 and `status`=1 limit 1;
DB::name('test')->where(['id'=>1])->where(['status'=>1])->find();
```

#### 9. *关联查询*

##### 1. 默认连接

```php
// select * from `pre_foo` as `f` join `pre_bar` as b on `f`.`bid`=`b`.`id`;
DB::name('foo f')->join(['bar b'=>'f.bid=b.id'])->select();

// join 第二参数为是否添加表前缀，默认 true 添加
// select * from `pre_foo` as `f` join `bar` as b on `f`.`bid`=`b`.`id`;
DB::name('foo f')->join(['bar b'=>'f.bid=b.id'], false)->select();
```

##### 2. 左联

```php
// select * from `pre_foo` as `f` left join `pre_bar` as b on `f`.`bid`=`b`.`id`;
DB::name('foo f')->leftJoin(['bar b'=>'f.bid=b.id'])->select();
```

##### 3. 右联

```php
// select * from `pre_foo` as `f` right join `pre_bar` as b on `f`.`bid`=`b`.`id`;
DB::name('foo f')->rightJoin(['bar b'=>'f.bid=b.id'])->select();
```

##### 4. 内联

```php
// select * from `pre_foo` as `f` inner join `pre_bar` as b on `f`.`bid`=`b`.`id`;
DB::name('foo f')->innerJoin(['bar b'=>'f.bid=b.id'])->select();
```

#### 7. *指定数据库配置*

​	~~YF 使用`[master]`作为默认连接配置，若有多个从数据库可选，请使用`db()`方法指定使用哪个从数据库获取数据。~~

​	静态方法`name`、`table`、`transaction`均添加第二参数，为数据库配置，默认启用`master`配置。

```php
// 配置参考9.1注释部分
DB::name('test', 'read01')->select();
```

#### 8. *获得数据库连接句柄*

```php
DB::handle(); // 默认获得master连接句柄
DB::handle('read01'); // 指定从数据库read01连接句柄
```

#### 9. *调试*

```php
// debug方法默认参数为true，开启调试模式，可通过设置 false或者不调用该方法以关闭调试模式
// 输出 select * from `pre_test`;
DB::name('test')->debug()->select();

// 输出 select * from `pre_test`;
DB::name('test')->debug(true)->select();

// 执行 select * from `pre_test`;
DB::name('test')->debug(false)->select();
```

#### 10. *分布式*

​	YF支持分布式数据库操作，默认操作`[master]`配置下的数据库，若做了主从，或者其他分布式数据库，需要指定数据库信息的话，就需要使用`db()`方法指定数据库信息。

```php
use YF\DB;
// 读取从数据库信息，减轻主数据库压力
DB::name('test')->db('read01')->select();
```

> 注：所有事务，以及写入操作，请确保操作的是master数据库。

#### 11. *获得连接句柄*

​	获得原生PDO对象，以执行某些特殊SQL语句，例如：

```php
// /apps/rbac/Init.php
public function createEnv()
{
    $db = DB::handle( 'master' );
    try{
        $tb = 'rbac_pa';
        $db->exec( 'DROP TABLE IF EXISTS `' . $tb . '`' );
        $db->exec( "CREATE TABLE `$tb` (
            `rid` int(10) unsigned NOT NULL COMMENT 'roles.id',
            `pid` int(10) unsigned NOT NULL COMMENT 'permissions.id',
            UNIQUE KEY `rid_pid` (`rid`,`pid`),
            KEY `uid` (`rid`),
            KEY `pid` (`pid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色-权限表'" );
    }catch( Exception $e ){
        return Response::withJson([ 'errno'=> 1010000, 'errmsg'=>'建表错误：' . $tb ]);
    }
}
```

#### 12. *事务操作*

​	事务操作默认开启锁机制（InnoDB默认是行级别的锁，当有明确指定的主键时候，是行级锁。否则是表级别）

> 注：事务中不支持`db()`方法，数据库连接信息通过第二参数传递，默认为`master`配置

```php
// DB::transaction( Closure $func, string $db='master' );
DB::transaction(function(){
    // 指定主键信息，行级锁
    // select `content` from `pre_test` where `id`=123 limit 1 for update;
    $result = DB::name('test')->field('content')->where(['id'=123])->find();
    
    DB::name('test01')->add(['content'=>$result['content'], 'uid'=>123]);
}, 'master01');
```







### 10. Session



​	使用PHP默认会话保持机制，优点是先天集成，无需实现。缺点是其他语言无法获得会话内容，对分布式不友好，不过 PHP SESSION 支持 REDIS 扩展（当需要做分布式或者多语言开发的时候开启即可，参考下文）。

#### 1. *检测是否有该变量*

```php
use YF\Session;
// 返回布尔值
Session::has('uid');

// 第二参数为默认域，支持组件化开发时Session互不干扰，默认域参考配置文件 /config/session.ini
Session::has('uid', '__user__');
```

#### 2. *获得变量*

```php
use YF\Session;
// 获得单个数据
$uid = Session::get('uid');

// 获得当前域所有数据
$session_data = Session::get('*');

// 指定域
$session_data = Session::get('uid', 'test');

// 获得所有域数据
$session_data = Session:get('*', '*');
```

#### 3. *写入数据*

```php
use YF\Session;
// 写入单条数据
Session::set('uid', 1);

// 指定域
Session::set('uid', 1, '__user__');
```

#### 4. *删除数据*

```php
use YF\Session;
// 删除一条数据
Session::unset('uid');

// 删除所有数据
Session::unset('*', '*');
// OR
Session::clear();

// 删除当前域下所有数据
Session::unset('*');

// 指定域
Session::unset('*', '__user__');
```

#### 5. *获得会话 ID*

```php
use YF\Session;
// 返回字符串值
$session_id = Session::id();
```

#### 6. *添加 Redis 扩展*

1. [安装扩展](https://github.com/phpredis/phpredis/blob/develop/INSTALL.markdown)
2. [PHP配置](https://github.com/phpredis/phpredis)

3. 通过缓存`Session::id()`实现多语言开发以及分布式存储。







### 11. Cookie



​	由于Cookie的性质，其默认使用`openssl`加密数据，加密参数请参考`/config/cookie.ini`。


#### 1. *检测是否有该变量*

```php
use YF\Cookie;
// 返回布尔值
Cookie::has('uid');
```

#### 2. *获得变量*

```php
use YF\Cookie;
// 获得数据
// 若无，返回 false
$uid = Cookie::get('uid');

// 修改解密参数
$uid = Cookie::get('uid', ['salt'=>'this is salt', 'cipher'=>'aes128', 'iv'='1234567890abcdef']);
```

#### 3. *写入数据*

```php
use YF\Cookie;
// 写入数据
Cookie::set('userinfo', ['id'=>1,'username'=>'test']);

// 同时指定配置信息
Cookie::set('userinfo', ['id'=>1,'username'=>'test'],  ['expire'=>3600, 'salt'=>'this is salt', 'cipher'=>'aes128', 'iv'='1234567890abcdef']);
```

#### 4. *删除数据*

```php
use YF\Cookie;
// 删除一条数据
Cookie::unset('uid');

// 删除所有数据
Cookie::clear();
```







### 12. 配置 Config



​	键默认小写，以半角逗号`.`区分。

> 注：配置文件以“_local.ini”结尾的配置文件不提交git，且优先级更高
>
> 推荐将数据库等敏感配置存入`/config/db_local.ini`以避免提交git，暴露数据库信息。

#### 1. *检测是否有该参数*

```php
use YF\Config;
// 返回布尔值
Config::has('app.debug');
```

#### 2. *获得变量*

```php
use YF\Config;
// 获得数据
// 若无，返回 false
$uid = Config::get('app.debug');
```

#### 3. *写入数据*

​	若需要在当前生命周期内使用某一变量（跨组件），可使用类属性或本方法。

​	本方法不能覆盖配置文件内的配置内容，可放心使用。

```php
use YF\Config;
// 写入配置
Config::set('app.test', ['id'=>1,'username'=>'test']);
```









### 13. 缓存 Cache



​	支持缓存驱动，必须实现`/yf/cache/driver.php`接口。目前仅有`file`驱动。文件驱动默认以键作为文件名，过期时间+值作为文件内容存储（支持过期时间）。当`expire=0`时代表忽略过期时间。

> 注：若缓存时过期时间为0，则无论下次如何修改过期时间，本次缓存的内容永不过期。

​	参数参考配置文件`/config/cache.ini` 。

#### 1. *写入缓存*

```php
use YF\Cache;
Cache::set([date('Y-m-d') => 1], 'views');

// 设置其他参数
Cache::set([date('Y-m-d') => 1], 'views', ['expire'=>3600]);
```

#### 2. *读取缓存*

```php
use YF\Cache;
Cache::get(date('Y-m-d'), 'views');

// 设置其他参数
Cache::get([date('Y-m-d') => 1], 'views', ['root'=>'/runtime/_cache']);
```

#### 3. *删除缓存*

```php
use YF\Cache;
Cache::unset(date('Y-m-d'), 'views');


// 设置其他参数
Cache::unset(date('Y-m-d'), 'views', ['root'=>'/runtime/_cache']);
```







### 14. 日志 Log



​	日志系统同样使用驱动式开发（目前仅实现File驱动），与缓存不同，日志按年月日存储，以防止文件过大、过多等情况造成系统资源的过多损耗。

​	日志仅一个常用公共方法`set`，暂时未实现文件大小阈值功能（单日存储，暂时不考虑单日访问过大的情况）。

```php
use YF\Log;
// 写入日志
Log::set('hello, world', 'test');

// 指定配置
Log::set('hello, world', 'test', ['root'=>'/runtime/access_log'])
```

例如：

```php
// /yf/Middleware.php

Use YF\Log;
// ...
Log::set( [
        'ip'     => Request::getIP(),
        'method' => Request::getMethod(),
        'uri'    => $uri,
        'params' => Request::param('*'),
        'return' => Response::data(),
    ], str_replace( '/', '%', $uri), [ 'engine' => 'file', 'root' => '/runtime/access_log' ] );
```

> 注：文件根目录为站点根目录，比如当前站点位于`/var/www/html`，请求URI为`/test`，则文件存储在 `/var/www/html/runtime/access_log/%test`目录下，其目录结构类似`%test/2018/12/12.log`。从性能方面考虑，建议仅对非常重要请求接口开启`YF\Middleware\AccessLog`中间件。







### 15. 常用中间件



#### 1. *访问日志 AccessLog*

用法：

```php
// /router.php
// ...
// 全局调用
$r->add('YF/Middleware/AccessLog');
```

写法：

```php
// /yf\Middleware.php
namespace YF;

use Closure;
use YF\Log;
use YF\Request;
use YF\Response;

class Middleware
{
    /**
     * 访问日志
     *
     * @param   $log    Log   日志类
     *
     * @return
     */
    public function AccessLog( Closure $next )
    {
        // 先执行业务代码，生成响应数据
        $next();

        // 写入访问日志
        $uri = Request::getURI();
        Log::set( [
            'ip'     => Request::getIP(),
            'method' => Request::getMethod(),
            'uri'    => $uri,
            'params' => Request::param('*'),
            'return' => Response::data(),
        ], str_replace( '/', '%', $uri), [ 'engine' => 'file', 'root' => '/runtime/access_log' ] );
    }
}

```







### 16. 错误 | 异常 处理



​	接管PHP默认的错误及异常处理。处理函数写于`/yf/Exception.php`，接管写于`/yf/App.php`。

```php
// /yf/App.php
// ...
// 调试
ini_set( 'display_errors', 0 );
ini_set( 'display_startup_errors', 0 );
if( $config['debug'] ){
    set_error_handler( ['\\YF\\Exception', 'error'], E_ALL | E_STRICT );
    set_exception_handler([ '\\YF\\Exception', 'exception' ]);
}
```



### 17. 用户方法



​	YF提供用户接口，方便快速开发登录用户访问的相关操作。YF还提供了`Logxx`接口，请参考`/apps/logxx`和`/yf/User.php`开发即可。

> 注：密码使用`BCRYPT`算法加密，请给密码字段保留60位字符即可：”char(60)“。或者自己实现密码加密算法

​	配置参数参考`/config/user.ini`。

#### 1. *isGuest()*

检查当前访问用户是否匿名

```php
use YF\User;
if( !User::isGuest() ){
    // 处理登录用户请求
}
```

#### 2. *login()*

缓存用户登录信息

```php
use YF\User;

$uid = 123;
$pwd_cookie = 'here is the password_cookie string';
$rids = [[1,2,3],[]];

User::login($uid, $pwd_cookie, ['rids' => $rids]);
```

#### 3. *set()*

设置用户会话缓存

```php
use YF\User;

User::set('foo', 'bar');
```

#### 4. *get()*

获得用户会话缓存，主要用来获得`login()`第三参数传递的内容。

```php
use YF\User;

$foo = User::get('foo');
```

#### 5. *getCookiePasswordData()*

​	获得客户端Cookie中的数据，是缓存的用户免密登录信息，用于免密验证。正确则返回数组，失败则返回False。

```php
use YF\User;

$data_cookie = User::getCookiePasswordData();
$uid = $data_cookie['uid'];
$password_cookie = $data_cookie['password_cookie'];
```

#### 6. *checkCookiePassword()*

​	验证密码是否正确，暂时仅直接比较。可加盐以使左右Cookie验证信息失效。

```php
use YF\User;
use YF\DB;

$data_cookie = User::getCookiePasswordData();
$uid = $data_cookie['uid'];
$password_cookie = $data_cookie['password_cookie'];

$data = DB::table('table')->field('password_cookie')->where(['id'=>$uid])->find();

$boo = User::checkCookiePassword($password_cookie, $data['password_cookie']);
```

#### 7. *logout()*

​	清空会话信息，注：同时清空服务器以及客户端的会话信息。

```php
use YF\User;

User::logout();
```

#### 8. *id()*

​	获得用户主键

```php
use YF\User;

$id = User::id();
```

#### 9. *roles()*

​	获得用户角色

> 注：支持角色继承，参数一为获得角色的类型（0：直接角色；1：子孙角色；2：所有角色）

```php
use YF\User;

$roles = User::roles(2);
```

#### 10. *flushRoles()*

​	当用户角色发生改变时刷新用户角色（目前仅在管理组用户增删角色时有用）

> 注：请严格参照参数说明使用，目前不对参数进行是否时二维数组的验证

```php
use YF\User;

User::flushRoles( [[1,2,3],[]] );
```

#### 11. *password_hash()*

​	加密密码，使用`BCRYPT`加密免密，返回60字符的加密字符串。

```php
use YF\User;

$password_encrypted = User::password_hash( '123456' );
```

#### 12. *password_verify()*

​	密码验证，返回布尔值

```php
use YF\User;

$pass = '123456';
$pass_db = '$2y$10$s2OEQiYN5WG4zZlQFiMJTuZHFpOQXz1tiVXzhz01zFcih6dPRq8Gq';

$boo = User::password_verify( $pass, $pass_db );
```







### 18. 无限分类 MPTTA



​	Modified Preorder Tree Traversal Algorithm，使用”预排序遍历树“算法保存分类信息（非递归）。

> 注：本类严重依赖DB类

#### 1. *setConfig()*

> 注：因本类的初始化方法需要数据库表前缀字段为第二参数，故新增本方法来修改配置信息，而非重新调用`init()`来修改配置信息；
>
> 注：强烈建议直接修改配置文件：`/config/mptta.ini`

​	设置数据库字段信息，有四个字段为必须，分别是主键（默认：id，自增），左值（默认：lft），右值（默认：rgt），层级（默认：lvl，从 1 开始计数）。

```php
use YF\MPTTA;

MPTTA::setConfig();
```

#### 2. *addChild()*

​	添加子数据

```php
use YF\MPTTA;

MPTTA::addChild( 1, ['foo'=>'bar', 'key'=>'value'], 'name');

// 第四参数为是否添加表前缀参数，默认添加
MPTTA::addChild( 1, ['foo'=>'bar', 'key'=>'value'], 'table', false);
```

#### *3. addChindFunc()*

​	添加子数据，参数为闭包函数。

​	若添加数据前需要根据数据库信息做相应判断，若需要高并发情况下高精准度添加数据，则请使用本方法。

​	具体使用方法请参考`/apps/rbac/Permission.php`，以下举例说明：

```php
use YF\MPTTA;
use YF\DB;

MPTTA::addChildFunc(function( Closure $next ) use($pid, $param){
    $data = DB::name('test')->where(['pid'=>$pid])->find();
    
    // 用 $data 做一些相应判断
    if( !empty($data) ){
        // 写入数据
        $next( $pid, $param, 'test' );
    }
});
```

#### 4. *addBrother()*

​	添加兄弟节点

```php
use YF\MPTTA;

MPTTA::addBrother( 1, ['foo'=>'bar', 'key'=>'value'], 'name');

// 第四参数为是否添加表前缀参数，默认添加
MPTTA::addBrother( 1, ['foo'=>'bar', 'key'=>'value'], 'table', false);
```

#### 5. *delete()*

​	删除节点，及其所有子节点。

```php
use YF\MPTTA;

MPTTA::delete(12, 'test');

// 第四参数为是否添加表前缀参数，默认添加
MPTTA::delete(12, 'test', false);
```

#### 6. *deleteFunc()*

​	参考`addChildFunc()`

#### 7. *findAllChildren()*

​	获得所有子孙节点信息，第一参数是父节点主键（int | array），第二字段是要查找的字段名，第三参数是表名。

```php
use YF\MPTTA;

$data1 = MPTTA::findAllChildren( 1, 'field1, field2, field3', 'test');

// 还支持查找多个父节点的子孙节点信息
$data2 = MPTTA::findAllChildren( 1, 'field1, field2, field3', 'test', false);
```

#### 8. *findChildren()*

​	获得所有直接节点信息。

```php
use YF\MPTTA;

$data1 = MPTTA::findChildren( 1, 'field1, field2, field3', 'test');

// 还支持查找多个父节点的子孙节点信息
$data2 = MPTTA::findChildren( 1, 'field1, field2, field3', 'test', false);
```

#### 9. *findChildrenNum()*

​	获得所有子孙节点个数

```php
use YF\MPTTA;

$num = MPTTA::findChildrenNUm(1, 'test');
```

#### 10. *findParents()*

​	检查所有父节点

```php
use YF\MPTTA;

$data = MPTTA::findParents( 100, 'field1, field2, field3', 'test');
```

#### 11. *findParent()*

​	查找直接父节点

```php
use YF\MPTTA;

$data = MPTTA::findParent( 100, 'field1, field2, field3', 'test');
```

#### 12. *hasChild()*

​	检查是否有该子节点

```php
use YF\MPTTA;

$boo = MPTTA::hasChild( 100, [1,2,3,4,5], 'test');
```







### 19. 网络资源请求



​	带 Cookie 访问网络资源，配置参考`/config/http.ini`

#### 1. *post*

```php
use YF\Http;

// 第二参数若存在 file 字段，则本次请求带 cookie。
// 第二参数若存在 data 字段，则此值为请求内容。
$response = Http::post( 'https://www.baidu.com/', ['file'=>'cookie01.txt', 'data'=>['name'=>'google']] );
```

#### 2. *delete*

```php
use YF\Http;

// 第二参数若存在 file 字段，则本次请求带 cookie。
// 第二参数若存在 data 字段，则此值为请求内容。
$response = Http::delete( 'https://www.baidu.com/', ['file'=>'cookie01.txt', 'data'=>['name'=>'google']] );
```

#### 3. *put*

```php
use YF\Http;

// 第二参数若存在 file 字段，则本次请求带 cookie。
// 第二参数若存在 data 字段，则此值为请求内容。
$response = Http::put( 'https://www.baidu.com/', ['file'=>'cookie01.txt', 'data'=>['name'=>'google']] );
```

#### 4. *get*

```php
use YF\Http;

// 第二参数若存在 file 字段，则本次请求带 cookie。
$response = Http::get( 'https://www.baidu.com/', 'cookie01.txt' );
```







### 20. File



​	文件操作相关

#### 1. *createDir()*

​	递归创建目录（若无），并设置目录权限为 0777

```php
use YF\File;

// $dp = '/var/www/html/runtime/foo/bar';
$dp = File::createDir('/runtime/foo/bar');
```
