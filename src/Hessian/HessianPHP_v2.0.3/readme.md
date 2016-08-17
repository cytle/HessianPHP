
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
    public static function isInternalUTF8(){


        // FXIME 现在使用的都是utf8，这里直接返回true
        return true;
        $encoding = ini_get('mbstring.internal_encoding');
        if(!$encoding)
            return false;
        return $encoding == 'UTF-8';
    }
```


### fix:共享对象造成hessianRef问题
2016年06月24日
```php
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
// Hessian2Parser.php @typedMap @untypedMap
    // if(HessianRef::isRef($key)) $key = &$this->objectlist->reflist[$key->index];
    // if(HessianRef::isRef($value)) $value = &$this->objectlist->reflist[$value->index];

    if(HessianRef::isRef($key)) $key = $this->refmap->objectlist[$value->index] ;
    if(HessianRef::isRef($value)) $value = $this->refmap->objectlist[$value->index] ;
```







