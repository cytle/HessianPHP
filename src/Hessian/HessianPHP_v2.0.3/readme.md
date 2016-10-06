
HessianPHP 2

Hessian protocol library for PHP 5+
Licensed under the MIT license

Project home: http://code.google.com/p/hessianphp/

Manuel Gómez - 2010
vegeta.ec(a)gmail.com


## 源码修改记录


### isInternalUTF8 直接返回true
2016年05月25日，详见`erp/base-support` 69bb75f360e5fdba47c0c6dc440fa6a35858e732 提交
```php
<?php
    public static function isInternalUTF8(){


        // FXIME 现在使用的都是utf8，这里直接返回true
        return true;
        $encoding = ini_get('mbstring.internal_encoding');
        if(!$encoding)
            return false;
        return $encoding == 'UTF-8';
    }
```


### fix:共享对象造成hessianRef问题（多个相同对象，除第一个外结果都为hessianRef对象）
2016年06月24日
```php
<?php
    function fillMap($index){
        if(!isset($this->refmap->classlist[$index]))
            throw new HessianParsingException("Class def index $index not found");
        $classdef = $this->refmap->classlist[$index];

        $localType = $this->typemap->getLocalType($classdef->type);
        $obj = $this->objectFactory->getObject($localType);

        $this->refmap->incReference();
        $this->refmap->objectlist[] = $obj;

        foreach($classdef->props as $prop){
            $item = $this->parse();
            if(HessianRef::isRef($item)) {
                /**
                 * fix 这里不需要，也不能加引用
                 */
                // $item = &$this->refmap->objectlist[$item->index];
                $item = $this->refmap->objectlist[$item->index];
            }
            $obj->$prop = $item;
        }

        return $obj;
    }
```

```php
<?php
// Hessian2Parser.php @typedMap @untypedMap
    // if(HessianRef::isRef($key)) $key = &$this->objectlist->reflist[$key->index];
    // if(HessianRef::isRef($value)) $value = &$this->objectlist->reflist[$value->index];

    if(HessianRef::isRef($key)) $key = $this->refmap->objectlist[$value->index] ;
    if(HessianRef::isRef($value)) $value = $this->refmap->objectlist[$value->index] ;
```


### fix:返回浮点数（double64）错误
2016年08月18日

e.g. service返回0.7，但unpack后值为 1.9035985662652E+185

原因：在paser中（Hessian2parser:203,Hessian1parser:130）unpack之前有一个是否将字节翻转的
判断，原来的判断为`HessianUtils::$littleEndian`，实际上这个静态变量默认值为null，并且在运行中
没有赋值，实际上只有在`HessianUtils::isLittleEndian()`这个方法中有赋值动作，并且这个方法的含义以
及行为就是判断是否isLittleEndian，极大可能原本就应该调用此方法做为判断依据而不是直接判断属性
`$littleEndian`，这应该又是hessianPHP一个幼稚的bug。*同理在HessianUtils有两个一样的判断，这次
未修改，因为没法验证*。

修复

```php
<?php
// Hessian2parser
    function double64($code, $num){
        $bytes = $this->read(8);
        /**
         * FIX 2016年08月18日10:01:37 修复返回浮点数出错
         * @author 炒饭
         */
        // - if(HessianUtils::$littleEndian)
        // -    $bytes = strrev($bytes);
        if(HessianUtils::isLittleEndian())
            $bytes = strrev($bytes);

        //$double = unpack("dflt", strrev($bytes));
        $double = unpack("dflt", $bytes);

        return $double['flt'];
    }
```

```php
<?php
// Hessian1parser
    function parseDouble($code, $num){
        $bytes = $this->read(8);
        /**
         * FIX 2016年08月18日10:01:37
         * @author 炒饭
         */
        // - if(HessianUtils::$littleEndian)
        // -    $bytes = strrev($bytes);
        if(HessianUtils::isLittleEndian())
            $bytes = strrev($bytes);

        //$double = unpack("dflt", strrev($bytes));
        $double = unpack("dflt", $bytes);
        return $double['flt'];
    }
```
### fix:长整形
2016年08月24日

```php
<?php
// Hessian2Writer 修改前

    function writeInt($value){
        if($this->between($value, -16, 47)){
            return pack('c', $value + 0x90);
        } else
        if($this->between($value, -2048, 2047)){
            $b0 = 0xc8 + ($value >> 8);
            $stream = pack('c', $b0);
            $stream .= pack('c', $value);
            return $stream;
        } else
        if($this->between($value, -262144, 262143)){
            $b0 = 0xd4 + ($value >> 16);
            $b1 = $value >> 8;
            $stream = pack('c', $b0);
            $stream .= pack('c', $b1);
            $stream .= pack('c', $value);
            return $stream;
        } else {
            $stream = 'I';
            $stream .= pack('c', ($value >> 24));
            $stream .= pack('c', ($value >> 16));
            $stream .= pack('c', ($value >> 8));
            $stream .= pack('c', $value);
            return $stream;
        }
    }
```

```php
<?php
// Hessian2Writer 修改后
    function writeInt($value){
        if($this->between($value, -16, 47)){
            return pack('c', $value + 0x90);
        } else
        if($this->between($value, -2048, 2047)){
            $b0 = 0xc8 + ($value >> 8);
            $stream = pack('c', $b0);
            $stream .= pack('c', $value);
            return $stream;
        } else
        if($this->between($value, -262144, 262143)){
            $b0 = 0xd4 + ($value >> 16);
            $b1 = $value >> 8;
            $stream = pack('c', $b0);
            $stream .= pack('c', $b1);
            $stream .= pack('c', $value);
            return $stream;
        } else {
            $stream = 'L';
            $stream .= pack('c', ($value >> 56));
            $stream .= pack('c', ($value >> 48));
            $stream .= pack('c', ($value >> 40));
            $stream .= pack('c', ($value >> 32));
            $stream .= pack('c', ($value >> 24));
            $stream .= pack('c', ($value >> 16));
            $stream .= pack('c', ($value >> 8));
            $stream .= pack('c', $value);
            return $stream;
        }
    }
```

### change:可以在对象中传递远程类型
2016年08月24日

```php
<?php
// Hessian2Writer 修改后
    function writeObjectData($value){
        $stream = '';
        $class = get_class($value);
        $index = $this->refmap->getClassIndex($class);

        if($index === false){
            $classdef = new HessianClassDef();
            $classdef->type = $class;
            if($class == 'stdClass'){
                $classdef->props = array_keys(get_object_vars($value));
            } else
                $classdef->props = array_keys(get_class_vars($class));
            $index = $this->refmap->addClassDef($classdef);
            $total = count($classdef->props);

            $type = $this->typemap->getRemoteType($class);
            $class = $type ? $type : $class;

            $stream .= 'C';
            $stream .= $this->writeString($class);
            $stream .= $this->writeInt($total);
            foreach($classdef->props as $name){
                $stream .= $this->writeString($name);
            }
        }

        if($index < 16){
            $stream .= pack('c', $index + 0x60);
        } else{
            $stream .= 'O';
            $stream .= $this->writeInt($index);
        }

        $this->refmap->objectlist[] = $value;
        $classdef = $this->refmap->classlist[$index];
        foreach($classdef->props as $key){
            $val = $value->$key;
            $stream .= $this->writeValue($val);
        }

        return $stream;
    }
```

```php
<?php
// Hessian2Writer 修改后
    function writeObjectData($value){
        $stream = '';

        $class = get_class($value);

        if (isset($value->__type) && $value->__type) {
            $__type = $value->__type;
        } else {
            $__type = $class;
        }

        $index = $this->refmap->getClassIndex($__type);

        if($index === false){

            $classdef = new HessianClassDef();
            $classdef->type = $__type;
            if($class == 'stdClass'){
                $classdef->props = array_keys(get_object_vars($value));
            } else
                $classdef->props = array_keys(get_class_vars($class));

            $classdef->props = array_filter($classdef->props, function($item) {
                return $item !== '__type';
            });

            $index = $this->refmap->addClassDef($classdef);
            $total = count($classdef->props);

            if ($__type === $class) {
                $type = $this->typemap->getRemoteType($class);
                $__type = $type ? $type : $__type;
            }

            $stream .= 'C';
            $stream .= $this->writeString($__type);
            $stream .= $this->writeInt($total);
            foreach($classdef->props as $name){
                $stream .= $this->writeString($name);
            }
        }

        if($index < 16){
            $stream .= pack('c', $index + 0x60);
        } else{
            $stream .= 'O';
            $stream .= $this->writeInt($index);
        }

        $this->refmap->objectlist[] = $value;
        $classdef = $this->refmap->classlist[$index];
        foreach($classdef->props as $key){
            $val = $value->$key;
            $stream .= $this->writeValue($val);
        }

        return $stream;
    }
```


### fix:返回浮点数错误（写入错误）
2016年09月01日

```php
<?php
// self::$littleEndian => static::isLittleEndian()
    /**
     * Serializes a float into its 32-bit float representation considering endianess
     * @param float $number
     * @return string
     */
    public static function floatBytes($number){
        $bytes = pack("s", $number);
        // 修复前：return self::$littleEndian ? strrev($bytes) : $bytes;
        return static::isLittleEndian() ? strrev($bytes) : $bytes;
    }

    /**
     * Serializes a float into its 64-bit double representation considering endianess
     * @param float $number
     * @return string
     */
    public static function doubleBytes($number){
        $bytes = pack("d", $number);
        // 修复前：return self::$littleEndian ? strrev($bytes) : $bytes;
        return static::isLittleEndian() ? strrev($bytes) : $bytes;
    }

```


### fix:utf8mb4造成乱码（临时方案）

2016年09月17日
```php
<?php
// Hessian2Parser@readUTF8Bytes
    // 修改前
    function readUTF8Bytes($len){
        $string = $this->read($len);
        $pos = 0;
        $pass = 1;
        while($pass <= $len){
            $charCode = ord($string[$pos]);
            if($charCode < 0x80){
                $pos++;
            } elseif(($charCode & 0xe0) == 0xc0){
                $pos += 2;
                $string .= $this->read(1);
            } elseif (($charCode & 0xf0) == 0xe0) {
                $pos += 3;
                $string .= $this->read(2);
            }
            $pass++;
        }

        if(! HessianUtils::isInternalUTF8())
            return $string;
        return utf8_decode($string);
    }


    // 修改后
    function readUTF8Bytes($len){
        $string = $this->read($len);
        $pos = 0;
        $pass = 1;
        while($pass <= $len){
            $charCode = ord($string[$pos]);
            if($charCode < 0x80){
                $pos++;
            } elseif(($charCode & 0xe0) == 0xc0){
                $pos += 2;
                $string .= $this->read(1);
            } elseif (($charCode & 0xf0) == 0xe0) {
                $pos += 3;
                $string .= $this->read(2);
            }
            $pass++;
        }

        if(! HessianUtils::isInternalUTF8()){
            $string = utf8_decode($string);
        }

        // utf8mb4忽略无法理解的编码
        return iconv('GBK', 'UTF-8//IGNORE', iconv('UTF-8', 'GBK//IGNORE', $string));
    }
```

### fix:long -2147483648 ~ -2048负值错误
*原因* 由于64位的php 负整数表示为64位（头位为1）。然而，service端在`-2147483648 ~ -2048`范
围内返回的数据为32位（4字节）数字，在php端解析时此数字会变为`2147483648 ~ 4294965248`范围的数
字。当值大于32位最大值时，修改值为

```php
<?php
$value = $value - static::Long32Max - 1 + static::Long32Min;
```
这和在其前补32位1的效果相同
如 -2048

```
11111111111111111111100000000000 // 4294965248
1111111111111111111111111111111111111111111111111111100000000000 // -2048

```

```php
<?php
class Hessian2Parser{
    // ...
    // 修复long32位负数
    const Long32Max = 2147483647;
    const Long32Min = -2147483648;

    // ...

    function parseInt($code, $num){
        $data = unpack('N', $this->read(4));
        $value = $data[1];

        if ($value > static::Long32Max) {
            $value = $value - static::Long32Max - 1 + static::Long32Min;
        }
        return $value;
    }

    function long32($code, $num){
        $value = ($this->readNum() << 24) +
                ($this->readNum() << 16) +
                ($this->readNum() << 8) +
                $this->readNum();

        // 增加
        if ($value > static::Long32Max) {
            $value = $value - static::Long32Max - 1 + static::Long32Min;
        }

        return $value;
    }
}
```

### fix:utf8mb4 增加4字节字符解析
2016年09月26日

```php
<?php
// Hessian2Parser@readUTF8Bytes
    // 修改前
    function readUTF8Bytes($len){
        $string = $this->read($len);
        $pos = 0;
        $pass = 1;

        while($pass <= $len){
            $charCode = ord($string[$pos]);
            if($charCode < 0x80){
                $pos++;
            } elseif(($charCode & 0xe0) == 0xc0){
                $pos += 2;
                $string .= $this->read(1);
            } elseif (($charCode & 0xf0) == 0xe0) {
                $pos += 3;
                $string .= $this->read(2);
            }
            $pass++;
        }

        if(! HessianUtils::isInternalUTF8()){
            $string = utf8_decode($string);
        }

        return $string;
    }


    // 修改后
    function readUTF8Bytes($len){
        $string = $this->read($len);
        $pos = 0;
        $pass = 1;

        while($pass <= $len){
            $charCode = ord($string[$pos]);
            if($charCode < 0x80){
                $pos++;
            } elseif(($charCode & 0xe0) == 0xc0){
                $pos += 2;
                $string .= $this->read(1);
            } elseif (($charCode & 0xf0) == 0xe0) {
                $pos += 3;
                $string .= $this->read(2);
            } elseif (($charCode & 0xf8) == 0xf0) {
                $pos += 4;
                $string .= $this->read(3);
            }
            $pass++;
        }

        if(! HessianUtils::isInternalUTF8()){
            $string = utf8_decode($string);
        }

        return $string;
    }

```

```php
<?php
// Hessian2Parser@readUTF8Bytes
    // 修改前
    function readUTF8Bytes($len){
        $string = $this->read($len);
        $pos = 0;
        $pass = 1;
        while($pass <= $len){
            $charCode = ord($string[$pos]);
            if($charCode < 0x80){
                $pos++;
            } elseif(($charCode & 0xe0) == 0xc0){
                $pos += 2;
                $string .= $this->read(1);
            } elseif (($charCode & 0xf0) == 0xe0) {
                $pos += 3;
                $string .= $this->read(2);
            }
            $pass++;
        }
        return $string;
    }



    // 修改后
    function readUTF8Bytes($len){
        $string = $this->read($len);
        $pos = 0;
        $pass = 1;
        while($pass <= $len){
            $charCode = ord($string[$pos]);
            if($charCode < 0x80){
                $pos++;
            } elseif(($charCode & 0xe0) == 0xc0){
                $pos += 2;
                $string .= $this->read(1);
            } elseif (($charCode & 0xf0) == 0xe0) {
                $pos += 3;
                $string .= $this->read(2);
            } elseif (($charCode & 0xf8) == 0xf0) {
                $pos += 4;
                $string .= $this->read(3);
            }
            $pass++;
        }
        return $string;
    }


```

### fix:64位下，写入三位小数浮点数出错
2016年09月26日
```php
<?php
// Hessian2Writer

    function writeDouble($value){

        $frac = abs($value) - floor(abs($value));
        if($value == 0.0){
            return pack('c', 0x5b);
        }
        if($value == 1.0){
            return pack('c', 0x5c);
        }

        // Issue 10, Fix thanks to nesnnaho...@googlemail.com,
        if($frac == 0 && $this->between($value, -127, 128)){
            return pack('c', 0x5d) . pack('c', $value);
        }
        if($frac == 0 && $this->between($value, -32768, 32767)){
            $stream = pack('c', 0x5e);
            $stream .= HessianUtils::floatBytes($value);
            return $stream;
        }
        // TODO double 4 el del 0.001, revisar
        $mills = (int) ($value * 1000);
        /**
         * FIX 2016年09月26日18:58:21 64位下，写入浮点数出错
         * @author 炒饭
         */
        // - if (0.001 * $mills == $value)
        if (0.001 * $mills == $value
            && $this->between($mills, -2147483648, 2147483647))
        {
            $stream = pack('c', 0x5f);
            $stream .= pack('c', $mills >> 24);
            $stream .= pack('c', $mills >> 16);
            $stream .= pack('c', $mills >> 8);
            $stream .= pack('c', $mills);
            return $stream;
        }

        // 64 bit double
        $stream = 'D';
        $stream .= HessianUtils::doubleBytes($value);
        return $stream;
    }
```


## fix: 修复非64位负整浮点数情况
2016年09月26日20:47:38

```php
<?php
// Hessian2Parser
    // double

    function double1($code, $num){
        if($num == 0x5b)
            return (float)0;
        if($num == 0x5c)
            return (float)1.0;
        $bytes = $this->read(1);

        /**
         * FIX 2016年09月26日20:47:38 修复负整数情况
         * @author 炒饭
         */
        $num = ord($bytes);
        if ($num > 0x7f) {
            $num = $num - 0x100;
        }
        return (float)$num;
    }

    function double2($code, $num){
        $bytes = $this->read(2);
        $b = unpack('s', strrev($bytes));

        /**
         * FIX 2016年09月26日20:47:38 修复负整数情况
         * @author 炒饭
         */
        if ($b[1] > 0x7fff) {
            $b[1] = $b[1] - 0x10000;
        }

        return (float)$b[1];
    }

    function double4($code, $num){
        $b = $this->read(4);
        $num = (ord($b[0]) << 24) +
                (ord($b[1]) << 16) +
                (ord($b[2]) << 8) +
                ord($b[3]);

        /**
         * FIX 2016年09月26日20:47:38 修复负整数情况
         * @author 炒饭
         */
        if ($num > 0x7fffffff) {
            $num = $num - 0x100000000;
        }
        return 0.001 * $num;
        // from the java implementation, this makes no sense
        // why not just use the float bytes as any sane language and pack it like 'f'?
    }

```


## fix:修复写入double 128 -128 边际值错误
2016年10月06日15:49:13

```php
<?php
// Hessian2Write@writeDouble
        /**
         * FIX 2016年10月06日 范围搞错，应该为[-128, 127]
         * 此处原为 $frac == 0 && $this->between($value, -127, 128)
         * 而实际上边际应该为 [-0x80, 0x7f] 即 [-128, 127]
         *
         * @author 炒饭
         */
        if($frac == 0 && $this->between($value, -128, 127)){
            return pack('c', 0x5d) . pack('c', $value);
        }

```
