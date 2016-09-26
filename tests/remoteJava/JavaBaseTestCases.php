<?php

use LibHessian\HessianHelpers;

/**
* 测试
*/
class JavaBaseTestCases extends TestCase
{
    var $version = 2;
    var $options;
    var $url = 'http://192.168.30.161:8088/phptest/phpApi';


    // java 数据边界
    public $intBoundary = [
        'max'        => 2147483647,
        'min'        => -2147483648,
        'twoByteMin' => -262144,
        'twoByteMax' => 262143,
        'oneByteMin' => -2048,
        'oneByteMax' => 2047,

    ];

    public $longBoundary = [
        'max' => 9223372036854775807,
        'min' => -9223372036854775808,
        'a' => 5124567855432488,
    ];

    public function testInt()
    {
        foreach ($this->intBoundary as $key => $value) {
            $this->assertVaule($value, 'intNum', "intBoundary=>${key}");
        }
    }

    public function testLong()
    {
        foreach ($this->longBoundary as $key => $value) {
            $this->assertVaule($value, 'longNum', "longBoundary=>${key}");
        }
    }

    public function testCreateLong()
    {
        foreach ($this->longBoundary as $key => $value) {

            $testModel = [
                'longNum' => HessianHelpers::createLong($value)
            ];
            $data = $this->query($testModel);

            $this->assertEquals($value, $data->longNum, "longBoundary=>${key}");
        }
    }


    public function testLongList()
    {
        $list = $this->longBoundary;
        $a = $this->longBoundary['max'];

        while ($a > 1) {
            $a = intval($a / 10);
            array_unshift($list, $a);
            $list[] = -1 * $a;
        }

        $list = array_values($list);
        $list = array_merge($list, array_values($this->intBoundary));

        $testModel = [
            'longList' => $list,
        ];

        $longList = $this->query($testModel)->longList;


        foreach ($list as $key => $value) {
            $this->assertEquals($value, $longList[$key]);
        }
    }

    public function testDoubleList()
    {
        $list = $this->longBoundary;
        $a = $this->longBoundary['max'];

        while ($a > 1) {
            $a = intval($a / 10);
            array_unshift($list, $a);
            $list[] = -1 * $a;
        }

        $list = array_values($list);
        $list = array_merge($list, array_values($this->intBoundary));

        $testModel = [

            'doubleList' => array_map(function ($v) {
                return floatval($v);
            }, $list)
        ];

        $doubleList = $this->query($testModel)->doubleList;


        foreach ($list as $key => $value) {
            $this->assertEquals($value, $doubleList[$key]);
        }
    }

    public function testDouble()
    {
        $this->assertVaule(123.0, 'doubleNum', "testDouble");

        $this->assertVaule(5124567855432488.0, 'doubleNum', "testDouble");
        $this->assertVaule(-126, 'doubleNum', "testDouble");
        $this->assertVaule(-92.0, 'doubleNum', "testDouble");
    }

    public function testFloat()
    {
        $this->assertVaule(1223.0, 'doubleNum', "testFloat");
    }

    public function testIntMap()
    {
        $value = [
            'asd@' => 123
        ];
        $testModel = [
            'intMap' => (object) $value
        ];

        $actual = $this->query($testModel)->intMap;

        $this->assertArraySubset($value, $actual);
    }


    public function testString()
    {
        $this->assertVaule('za啊实打实Á大三', 'str', "testString");
    }


    public function assertVaule($value, $name, $message = '')
    {
        $testModel = [
            $name => $value
        ];
        $actual = $this->query($testModel)->{$name};

        $this->assertEquals($value, $actual, $message);
    }

    public function query(array $testModel)
    {
        return HessianHelpers::query($this->url, 'test', [ new TestModel($testModel) ], [
            'version' => $this->version
        ]);
    }

    public function queryJson(array $testModel)
    {
        return HessianHelpers::query($this->url, 'toString', [ new TestModel($testModel) ], [
            'version' => $this->version
        ]);
    }

}

/**
*
*/
class TestModel
{
    public $str;
    public $longNum;
    public $intNum;
    public $shortNum;
    public $byteNum;
    public $bool;
    public $doubleNum;
    public $floatNum;
    public $date;
    public $orderStatus;
    public $longmap;
    public $intMap;
    public $shortMap;
    public $byteMap;
    public $booleanMap;
    public $doubleMap;
    public $floatMap;
    public $dateMap;
    public $enumMap;
    public $longList;
    public $intList;
    public $shortList;
    public $byteList;
    public $boolList;
    public $doubleList;
    public $floatList;
    public $dateList;
    public $enumList;
    public $strlist;

    function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}

