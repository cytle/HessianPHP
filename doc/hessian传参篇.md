### 数组，字符串，数字，布尔值

直接传递

###

### Map类型

构建一个对象

#### 示例

```php
$sortMap = (object)['add_time' => 'desc'];
```

**lib-hessian 方法**

无

### Enum

构建一个含有`name`属性的对象


#### 示例

```php
$sortMap = (object)['name' => 'FINISH'];
```

**lib-hessian 方法**

```php
// use LibHessian\HessianHelpers;

$enum = HessianHelpers::createEnum('FINISH');
```

### Date

构建一个`DateTime`对象
#### 示例

```php
// use DateTime;

$dateTime = new DateTime('2016-08-31');
```

**lib-hessian 方法**

```php
// use LibHessian\HessianHelpers;

$dateTime = HessianHelpers::createDateTime('2016-08-31');
```

