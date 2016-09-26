<?php

use LibHessian\Hessian\HessianClient;
use LibHessian\Hessian\HessianOptions;


$dirname = dirname(__FILE__);
include_once $dirname . '/../../src/Hessian/HessianPHP_v2.0.3/src/HessianInterfaces.php';
include_once $dirname . '/ServerManager.php';
include_once $dirname . '/until.php';

class ParamObject{
    var $test = 'Hola hessian';
    var $stringVar = 'vaca';
    var $hashVar;
}

class Interceptor implements IHessianInterceptor{
    var $base;
    function __construct(){
        $this->base = dirname(__FILE__).'/logs/';
    }

    function beforeRequest(HessianCallingContext $ctx){
        $ctx->options->saveRaw = true;
    }

    function afterRequest(HessianCallingContext $ctx){
        file_put_contents($this->base.'payload.bin', $ctx->payload);
        $this->writeAll($ctx);
    }

    function writeAll($ctx){
        file_put_contents($this->base.'parserLog.txt', implode("\n", $ctx->parser->log));
        file_put_contents($this->base.'writerLog.txt', implode("\n", $ctx->writer->log));
        file_put_contents($this->base.'result.bin', $ctx->transport->rawData);
    }

    function clear(){
        @unlink($this->base.'payload.bin');
        @unlink($this->base.'parserLog.txt');
        @unlink($this->base.'writerLog.txt');
        @unlink($this->base.'result.bin');
    }
}

/**
 * Base class for unit tests of the protocol features. It uses a local php service
 * to perform the remote calls.
 */
class BaseTestCases extends TestCase {
    var $version = 2;
    var $proxy;
    protected static $url;

    public static function setUpBeforeClass()
    {
        $server = ServerManager::getInstance();

        if (! $server->isRunning()) {
            throw new Exception("构建测试服务失败", 1);
        } else {
            // echo "成功构建测试环境" , PHP_EOL;q
        }

        self::$url = 'http://' . $server->getUrl();
    }

    function setUp() {
        $options = new HessianOptions();
        $options->version = $this->version;

        $this->proxy = new HessianClient(self::$url, $options);
    }

    function tearDown() {}

    public static function tearDownAfterClass() {}

    // Tests if sent and received values are equal
    function testEcho(){
        $values = array(
            555.00, 666.00, 102456.5646, 'Hello', 'Ámbito', 546546, false, true
        );
        foreach($values as $value){
            $ret = $this->proxy->testEcho($value);
            $this->assertEquals($ret, $value);
        }
    }

    // tests simple strings
    function testConcatString(){
        $str = $this->proxy->testConcatString("hello"," hessianphp");
        $this->assertEquals($str, "hello hessianphp");
    }

    // tests unicode strings
    function testConcatStringUnicode(){
        try{
            $expected = "áé";
            $str = $this->proxy->testConcatString("á","é");
            //var_dump($str);
            //var_dump($expected);
            $this->assertEquals($str, $expected);
        }catch(Exception $e){
            nLog(__METHOD__, $e);
            throw $e;
        }
    }

    function testStringToLong() {
        $val = $this->proxy->testStringToLong('5124567855432488');
        nLog(__METHOD__, $val);

        $this->assertEquals(5124567855432488, $val);
    }

    function testStringTo64Long() {
        if ($this->version === 1) {
            $this->markTestSkipped(
              'version 1 不支持 64位数字'
            );
        } else {
            $val = $this->proxy->testStringToLong('9223372036854775807');
            $this->assertEquals(9223372036854775807, $val);

            $val = $this->proxy->testStringToLong('-9223372036854775808');
            $this->assertEquals(-9223372036854775808, $val);
        }


    }

    function testStringToBoolean() {
        // fails with other values, works only with 'true' and 'false'
        $bool = $this->proxy->testStringToBoolean('true');
        $this->assertEquals($bool,true);
    }
    function testStringToDouble() {
        // Different format for .net
        $double = $this->proxy->testStringToDouble('545.54');
        $this->assertEquals($double,545.54);
    }
    function testStringToShort() {
        $short = $this->proxy->testStringToShort('17');
        $this->assertEquals($short,17);
    }
    function testStringToInt(){
        $int = $this->proxy->testStringToInt('17');
        $this->assertEquals($int,17);
    }
    function testStringToFloat(){
        // Different format for .net
        $float = $this->proxy->testStringToFloat('0.333333');
        $this->assertEquals($float,0.333333);
    }
    function testStringToByte(){
        // Just works with integers and returns integers
        $byte = $this->proxy->testStringToByte("01");
        $this->assertEquals($byte , 1);
    }

    function testIntToString(){
        $str = $this->proxy->testIntToString(83);
        $this->assertEquals($str , '83');
    }
    function testDoubleToString(){
        $str = $this->proxy->testDoubleToString(123.4);
        $this->assertEquals($str , '123.4');
    }
    function testBoolToString(){
        $str = $this->proxy->testBoolToString(true);
        $this->assertEquals($str , '1'); // returns 1 or 0, not true or false
        $str = $this->proxy->testBoolToStringStrict(false);
        $this->assertEquals($str , 'false');
    }

    function testCharToString(){
        // the ascii code of the char
        $str = $this->proxy->testCharToString(65);
        $this->assertEquals($str , '65');
    }

    function testIntArrToString(){
        $arr = $this->proxy->testIntArrToString(array(1,2,3,4));
        $stringArr = array('1','2','3','4');
        $this->assertEquals($arr , $stringArr);
    }
    function testStringArrToInt(){
        // only integers or mixed strings/integers
        $arr = $this->proxy->testStringArrToInt(array('1',2,'3',4));
        $intArr = array(1,2,3,4);
        $this->assertEquals($arr , $intArr);
    }

    function testDoubleArrToString(){
        $arr = $this->proxy->testDoubleArrToString(array(0.1,0.2,0.3,5.4));
        $doubleArr = array('0.1','0.2','0.3','5.4');
        $this->assertEquals($arr , $doubleArr);
    }

    function testStringArrToDouble(){
        // same as above
        $arr = $this->proxy->testStringArrToDouble(array(0.1,'0.2',0.3,'5.4'));
        $doubleArr = array(0.1,0.2,0.3,5.4);
        $this->assertEquals($arr , $doubleArr);
    }

    function testHashMap(){
        // switches order of indexes
        $map = $this->proxy->testHashMap(array('A','B','C'), array(1,2,3));
        $testMap = array('A' => 1,'B' => 2,'C' => 3);
        $this->assertEquals($map , $testMap);
    }

    function testHashMapParam(){
        // same as above
        $string = $this->proxy->testHashMapParam(array('A' => 1,'B'=>2,'C'=>3));
        $testString = 'A 1B 2C 3';
        $this->assertEquals($string , $testString);
    }

    function testNullParamObject(){
        $obj = $this->proxy->testParamObject(null);
        $this->assertNotNull($obj);
        $this->assertEquals($obj->stringVar , "ParamObject was empty");
        //$this->assertEquals($obj->hashVar['Message'] , "No Message");
    }

    function testParamObject(){
        $obj = $this->proxy->testParamObject(new ParamObject());
        $this->assertNotNull($obj);
        $this->assertEquals($obj->stringVar , "ParamObject not empty");
        //$this->assertEquals($obj->hashVar['Message'] , "vaca");
    }

    function testSendParamObject(){
        $str = $this->proxy->testSendParamObject(new ParamObject());
        $this->assertEquals($str , "vaca");
    }

    function testReceiveParamObject(){
        $obj = $this->proxy->testReceiveParamObject('burr');
        $this->assertNotNull($obj);
        $this->assertEquals($obj->stringVar , "burr");
    }

    function testArrayListParam(){
        // as arraylist
        $list = $this->proxy->testArrayListParam(array(1,2,3,4,5));
        $this->assertEquals($list,'1 2 3 4 5 ');
    }

    function testArrayList(){
        // doesn't seem to care what I send in the array
        $sendList = array(1,'2',3,4.5,5);
        $respList = $list = $this->proxy->testArrayList($sendList);
        $this->assertEquals($sendList, $respList);
    }

    function testList(){
        // as IList (.net) / List (Java)
        $count = $this->proxy->testList(array(1,'2'));
        $this->assertEquals($count, 2);
    }

    function testEmptyList(){
        // as IList (.net) / List (Java)
        $count = $this->proxy->testList(array());
        $this->assertEquals($count, 0);
    }

    function testStringToDate(){
        //var_dump($dt->format(DATE_ATOM));
        $time = 'H:i:s';
        $date = 'Y-m-d';
        $dt = $this->proxy->testStringToDate('2005-12-27 20:30:15');
        $this->assertTrue($dt instanceof DateTime);
        $this->assertEquals('20:30:15', $dt->format($time));
        $this->assertEquals('2005-12-27', $dt->format($date));

        $dt = $this->proxy->testStringToDate('2009-12-20 21:14');
        $this->assertTrue($dt instanceof DateTime);
        $this->assertEquals('21:14', $dt->format('H:i'));
        $this->assertEquals('2009-12-20', $dt->format($date));

    }

    function testDateToString(){
        // .NET: Returns incorrect information
        $dt1 = new DateTime('1998-05-08 02:51:31');
        $string1 = $this->proxy->testDateToString($dt1);
        $this->assertEquals($string1,'1998-05-08 02:51:31');

        $dt2 = new DateTime('1970-01-01 12:00:01');
        $string2 = $this->proxy->testDateToString($dt2);
        $this->assertEquals($string2,'1970-01-01 12:00:01');

        $dt3 = new DateTime('2006-11-14 11:16:44');
        $string3 = $this->proxy->testDateToString($dt3);
        $this->assertEquals($string3,'2006-11-14 11:16:44');
    }

    function testCurlTransport(){
        $options = new HessianOptions();
        $options->transport = "CURL";
        $options->version = $this->version;
        $this->proxy = new HessianClient(self::$url, $options);
        $this->testConcatString();
    }

    function testHttpStreamTransport(){
        $options = new HessianOptions();
        $options->transport = "http";
        $options->version = $this->version;
        $this->proxy = new HessianClient(self::$url, $options);
        $this->testConcatString();
    }

    function testInterceptor(){
        $interceptor = new Interceptor();
        $interceptor->clear();

        $options = new HessianOptions();
        $options->interceptors = array($interceptor);
        $options->version = $this->version;

        $this->proxy = new HessianClient(self::$url, $options);
        $str = $this->proxy->testConcatString("hello"," hessianphp");

        $file = dirname(__FILE__).'/logs/payload.bin';
        $this->assertTrue(file_exists($file));
    }

    /**
     * @expectedException Exception
     */
    function testFault(){
        $this->proxy->testFault();
    }

    function testSendFile(){
        $fp = fopen(dirname(__FILE__).'/resource/ok.png', "r");
        $this->proxy->testSendFile('ok3.png', $fp);
        $this->assertTrue(file_exists(dirname(__FILE__).'/resource/ok2.png'));
    }

    function testReceiveFile(){
        $filename = dirname(__FILE__).'/resource/notok.png';
        $size = filesize($filename);
        $bytes = $this->proxy->testReceiveFile();
        // feo truco para contar en bytes
        $totalbytes = count(str_split($bytes));
        $this->assertTrue($totalbytes == $size); //strlen($bytes)
    }
}

