<?php

use LibHessian\HessianHelpers;

/**
* 临时测试
*/
class TmpTest extends TestCase
{

    protected $orderServiceUrl = 'http://192.168.30.126:8080/orderservice/querySellerOrder';
    protected $dormServiceUrl = 'http://192.168.30.88:8080/dormservice/dorm';
    protected $dormShopServiceUrl = 'http://192.168.30.196:8080/dormservice/dormshop';
    protected $purchaseOrderServiceUrl = 'http://192.168.30.200:8080/purchaseservice/purchaseorder';


    public function testFloat222($value='')
    {

        $url = $this->dormServiceUrl;
        $dormTransactionRecordFilter = (object) [
            'dormId' => 218,
            'limit' => 1,
        ];
        $records = HessianHelpers::query($url, 'getDormTransactionRecordList', [$dormTransactionRecordFilter]);

        $records = $records->data;
        $this->assertEquals(1, count($records));
        $record = $records[0];

        // print_r($record);
        // print_r($record->change);
    }

    public function testFloat($value='')
    {
        $url = $this->purchaseOrderServiceUrl;

        $orderFilter = (object)[
                'orderIds' => [
                    '77418965655093337'
                ],
                'detailFlag' => true,
            ];

        $orders = HessianHelpers::query($url, 'findPurchaseOrdersByFilter', [$orderFilter]);
        $this->assertEquals(0, $orders->status);

        $orders = $orders->data;

        $this->assertEquals(1, count($orders));

        $order = $orders[0];

        $orderDetails = $order->detailDTOs;

        // print_r($orderDetails[0]);
        // print_r($orderDetails[0]->price);

    }

    public function testQuery()
    {
        $url = $this->dormServiceUrl;
        $dorm = HessianHelpers::query($url, 'getDorm', [222]);
        // echo PHP_EOL;
        // echo ($dorm->data->uid);
        // echo PHP_EOL;
        $this->assertEquals(0, $dorm->status);
    }


    public function testOrder()
    {
        $url = $this->orderServiceUrl;

        // $order = HessianHelpers::query($url, 'queryOrdersPaging', [(object) [
        //     'withOrderItems' => true,
        //     'withOrderPays'  => true,
        //     ], 1, 1]);


        $re = HessianHelpers::query($url, 'queryOrder', ['03605400850810982202195', (object) [
            // 'withOrderItems' => true,
            // 'withOrderPays'  => true,
            ]]);

        $this->assertEquals(0, $re->status);

        $order = $re->data;
        // echo PHP_EOL;
        // print_r($re);
        // echo ($order->buyerRemark);
        // echo PHP_EOL;
    }

    public function testDormShop()
    {
        $url = $this->dormShopServiceUrl;

        // $order = HessianHelpers::query($url, 'queryOrdersPaging', [(object) [
        //     'withOrderItems' => true,
        //     'withOrderPays'  => true,
        //     ], 1, 1]);


        $re = HessianHelpers::query($url, 'findByShopId', [
            32777,
            false,
            false,
            false,
            false
            ]);

        $this->assertEquals(0, $re->status);

        $dormShop = $re->data;

        // print_r($re);


        // echo PHP_EOL;
        // echo ($order->buyerRemark);
        // echo PHP_EOL;
    }
}
