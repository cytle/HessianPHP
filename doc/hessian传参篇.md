### 字符串，数字，布尔值

直接传递

### 数组

自然序列数组


### 对象

传递一个php对象即可

1. 定义类需要注意一下对象属性需要是`public`

2. 传递对象还需注意一下几点

    - 对象的`__type`值对应着远程java类 （`Lib-Hessian`对`hessianPHP`的改造，拥有的特性）
    - 尽量构建专门的类，避免让传递的对象是`StdClass`实例（`(object) [ 'a' => 1]`产生的就是`StdClass`的实例）

> 在hessianPHP传参时会保存对象的类的默认属性(`get_class_vars`)，如果是StdClass，则记录对象所有的属性(`get_object_vars`)，所以当多次传递StdClass，所传的对象属性都和第一次对象一致。



### Map类型

构建一个对象

#### 示例

```php
$sortMap = (object)['add_time' => 'desc'];
```

**lib-hessian 方法**

无

### Enum

构建一个含有`name`属性的对象。（hessian的特殊写法吧。。）

在传递这个枚举对象时最好带上`__type`属性，因为如果需要传递的是一个枚举对象数组，在java端是不能解析的


#### 示例

```php
$__type = 'com.store59.dto.common.order.OrderStatusEnum';

$sortMap = (object)['name' => 'FINISH', '__type' => $__type];
```

**lib-hessian 方法**

```php
// use LibHessian\HessianHelpers;
$__type = 'com.store59.dto.common.order.OrderStatusEnum';

$enum = HessianHelpers::createEnum('FINISH', $__type);
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


