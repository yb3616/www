#### 1. 目录结构

```bash
..
└── rbac （组件）
    ├── Base.php        （存放一些配置变量以及对内的公共方法集）
    ├── Common.php	    （对外提供的方法集）
    ├── Init.php        （初始化对应的数据库表结构以及数据）
    ├── Middleware.php  （路由中间件）
    ├── PA.php          （PA表的操作）
    ├── Permission.php  （Permission表的操作）
    ├── README.md       （组件说明）
    ├── Role.php        （Role表的操作）
    ├── Router.php      （路由规则）
    └── UA.php          （UA表的操作）
```




#### 2. 权限管理组件的使用方法

1. 用户登录时需要缓存用户角色组信息

```php
use Apps\rbac\Common as RBAC;
// ...
User::set('rids', RBAC::getRids( $user_id ));
```

2. 将组件路由写入路由文件

```php
// /router.php
// ...
use Apps\rbac\Router as RBAC;
// ...
return function( Router $r )
{
    // ...
    RBAC::run( $r );
    // ...
}
```

3. 初始化数据库表结构和数据

```bash
php public/index.php rbacInit
```



#### 3. 权限管理组件的禁用方法

1. 将基类`/apps/rbac/Base.php`中的`isForbidden`属性改成`true`即可。

```php
// /apps/rbac/Base.php
namespace Apps\rbac;

class Base
{
    static protected $isForbidden = true;
}
```

