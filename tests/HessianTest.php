<?php

use LibHessian\HessianHelpers;
/**
* 测试
*/
class HessianTest extends TestCase
{

    protected $dormServiceUrl = 'http://192.168.30.196:8080/dormservice/dorm';

    public function testGetClient()
    {
        $url = $this->dormServiceUrl;
        $oldClient = HessianHelpers::getClient($url);

        $dorm = $oldClient->getDorm(218);
        $oldClientS = serialize($oldClient);

        $newClient = HessianHelpers::getClient($url);

        $newClientS = serialize($newClient);

        $this->assertEquals($oldClientS, $newClientS);

    }


    public function testCreateClient()
    {
        $url = $this->dormServiceUrl;

        $oldClient = HessianHelpers::createClient($url);
        $newClient = HessianHelpers::createClient($url);

        $this->assertTrue(serialize($newClient) === serialize($oldClient));
        $this->assertFalse($newClient === $oldClient);

    }
}
