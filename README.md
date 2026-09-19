# Pin Access

Pin Access 是 Pin 提供的访问控制扩展包。

它基于权限代码实现接口访问控制，将路由、权限和菜单进行关联，提供统一的访问校验能力。

## 主要功能

- 基于权限代码的接口访问控制
- 路由权限自动映射
- 菜单权限管理
- 用户权限检查

## 使用约定

用户实现 `Pin\Access\Contracts\AccessUser`，通过 `accessibleMenus()` 提供菜单集合，
通过 `hasAllAccess()` 标识是否拥有全部权限。

- `codes()` 返回排序后的权限码列表，包含可用菜单、按钮和补齐的祖先菜单权限码。
- `menus()` 返回以菜单 ID 为键的序列化数组，包含祖先菜单，不包含按钮；它不是嵌套树或模型集合。
- 禁用节点及其后代会被过滤，过滤后的空父节点由 Pin 的树过滤器处理。
- 全权限用户的 `codes()` 返回空数组；授权应使用 Gate，不能仅通过数组是否为空判断权限。

```php
use Illuminate\Support\Facades\Gate;
use Pin\Access\Access;

Gate::forUser($user)->allows(Access::ABILITY, 'users');
```

路由使用 `Pin\Access\InteractsWithRoute` 时，默认以路由名称作为权限码。
`register()` 的 `accessCode` 参数接受字符串、`Routable`、`null` 或 `false`：
`null` 使用当前路由名，`false` 跳过权限中间件。
枚举上的 `#[Access(...)]` 属性优先于注册参数，包括属性值为 `null` 或 `false` 的情况。
手动挂载中间件的未命名路由需要显式传入权限码，否则拒绝访问。
`pin.access.except` 支持 URI 和路由名称规则。

## 缓存与扩展

`pin.access.access_provider` 可替换权限提供器；提供器通过 Laravel 容器创建，
构造函数的 `$user` 参数接收当前用户，其余依赖由容器解析。
`pin.access.menu_model` 可替换菜单模型。默认菜单模型沿用 Pin 的全量模型缓存，
每次补齐祖先时只解析缺失节点，共享祖先只解析一次。

权限数据使用进程缓存和应用缓存，`pin.access.cache_ttl` 设置两层缓存的有效期（秒），
默认为 `86400`，设为 `0` 可跳过两层权限缓存。此配置不影响菜单模型自身的缓存。
无认证标识的用户不缓存权限数据。修改用户的角色、菜单授权或全权限状态后，
应通过当前提供器清理该用户的缓存：

```php
use Pin\Access\Access;

$access = new Access($user);
$access->provider->flushAccess($user);
```

默认缓存键为 `auth-access:{用户ID}`，ID 取自 `getAuthIdentifier()`；相同 ID 共用权限缓存。
自定义菜单查询可覆盖 `findMenu()` 或 `menuModelClass()`。

长期运行的工作进程应在部署时重启以加载新代码。

## 开发验证

```bash
./vendor/bin/pest
./vendor/bin/phpstan analyse --no-progress
./vendor/bin/pint --test
```

## 文档

[https://ipiner.cn/packages/access](https://ipiner.cn/packages/access)
