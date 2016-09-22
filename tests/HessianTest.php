<?php

use LibHessian\HessianHelpers;
/**
* 测试
*/
class HessianTest extends TestCase
{

    protected $dormServiceUrl = 'http://192.168.30.207:8080/dormservice/dorm';
    protected $purchaseOrderServiceUrl = 'http://192.168.30.200:8080/purchaseservice/purchaseorder';

     public function testOne()
    {
        $this->assertEquals(1, 1);
    }
    // public function testGetClient()
    // {
    //     $url = $this->dormServiceUrl;
    //     $oldClient = HessianHelpers::getClient($url);

    //     $dorm = $oldClient->getDorm(218);
    //     $oldClientS = serialize($oldClient);

    //     $newClient = HessianHelpers::getClient($url);

    //     $newClientS = serialize($newClient);

    //     $this->assertEquals($oldClientS, $newClientS);

    // }


    // public function testCreateClient()
    // {
    //     $url = $this->dormServiceUrl;

    //     $oldClient = HessianHelpers::createClient($url);
    //     $newClient = HessianHelpers::createClient($url);

    //     $this->assertTrue(serialize($newClient) === serialize($oldClient));
    //     $this->assertFalse($newClient === $oldClient);

    // }

    // public function testFloat222($value='')
    // {

    //     $url = $this->dormServiceUrl;
    //     $dormTransactionRecordFilter = (object) [
    //         'dormId' => 218,
    //         'limit' => 1,
    //     ];
    //     $records = HessianHelpers::query($url, 'getDormTransactionRecordList', [$dormTransactionRecordFilter]);

    //     $records = $records->data;
    //     $this->assertEquals(1, count($records));
    //     $record = $records[0];

    //     print_r($record);
    //     // print_r($record->change);
    // }

    // public function testFloat($value='')
    // {
    //     $url = $this->purchaseOrderServiceUrl;

    //     $orderFilter = (object)[
    //             'orderIds' => [
    //                 '77418965655093337'
    //             ],
    //             'detailFlag' => true,
    //         ];

    //     $orders = HessianHelpers::query($url, 'findPurchaseOrdersByFilter', [$orderFilter]);
    //     $this->assertEquals(0, $orders->status);

    //     $orders = $orders->data;

    //     $this->assertEquals(1, count($orders));

    //     $order = $orders[0];

    //     $orderDetails = $order->detailDTOs;

    //     print_r($orderDetails[0]);
    //     print_r($orderDetails[0]->price);

    // }

    // public function testQuery()
    // {
    //     $url = $this->dormServiceUrl;
    //     $dorm = HessianHelpers::query($url, 'getDorm', [222]);
    //     echo PHP_EOL;
    //     echo ($dorm->data->uid);
    //     echo PHP_EOL;
    //     $this->assertEquals(0, $dorm->status);
    // }

    /**
     * @expectedException     LibHessian\Exceptions\HessianException
     * @expectedExceptionCode 10000
     */
    // public function testErrorQuery()
    // {
    //     $url = $this->dormServiceUrl;
    //     $dorm = HessianHelpers::query($url, 'foo', [218]);
    // }
}
