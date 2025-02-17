<?php


namespace Box\Mod\Invoice\Api;


class GuestTest extends \BBTestCase {
    /**
     * @var \Box\Mod\Invoice\Api\Guest
     */
    protected $api = null;

    public function setup(): void
    {
        $this->api = new \Box\Mod\Invoice\Api\Guest();
    }

    public function testgetDi()
    {
        $di = new \Pimple\Container();
        $this->api->setDi($di);
        $getDi = $this->api->getDi();
        $this->assertEquals($di, $getDi);
    }

    public function testget()
    {
        $serviceMock = $this->getMockBuilder('\Box\Mod\Invoice\Service')->getMock();
        $serviceMock->expects($this->atLeastOnce())
            ->method('toApiArray')
            ->will($this->returnValue(array()));

        $validatorMock = $this->getMockBuilder('\FOSSBilling\Validate')->getMock();
        $validatorMock->expects($this->atLeastOnce())
            ->method('checkRequiredParamsForArray');

        $dbMock = $this->getMockBuilder('\Box_Database')->getMock();
        $model = new \Model_Invoice();
        $model->loadBean(new \DummyBean());
        $dbMock->expects($this->atLeastOnce())
            ->method('findOne')
            ->will($this->returnValue($model));

        $di = new \Pimple\Container();
        $di['validator'] = $validatorMock;
        $di['db'] = $dbMock;

        $this->api->setDi($di);
        $this->api->setService($serviceMock);
        $this->api->setIdentity(new \Model_Admin());

        $data['hash'] = md5(1);
        $result = $this->api->get($data);
        $this->assertIsArray($result);
    }

    public function testgetInvoiceNotFound()
    {
        $validatorMock = $this->getMockBuilder('\FOSSBilling\Validate')->getMock();
        $validatorMock->expects($this->atLeastOnce())
            ->method('checkRequiredParamsForArray');

        $dbMock = $this->getMockBuilder('\Box_Database')->getMock();
        $model = new \Model_Invoice();
        $model->loadBean(new \DummyBean());
        $dbMock->expects($this->atLeastOnce())
            ->method('findOne')
            ->will($this->returnValue(null));

        $di = new \Pimple\Container();
        $di['validator'] = $validatorMock;
        $di['db'] = $dbMock;

        $this->api->setDi($di);
        $this->api->setIdentity(new \Model_Admin());

        $data['hash'] = md5(1);
        $this->expectException(\FOSSBilling\Exception::class);
        $this->expectExceptionMessage('Invoice was not found');
        $this->api->get($data);
    }

    public function testupdate()
    {
        $serviceMock = $this->getMockBuilder('\Box\Mod\Invoice\Service')->getMock();
        $serviceMock->expects($this->atLeastOnce())
            ->method('updateInvoice')
            ->will($this->returnValue(true));

        $validatorMock = $this->getMockBuilder('\FOSSBilling\Validate')->getMock();
        $validatorMock->expects($this->atLeastOnce())
            ->method('checkRequiredParamsForArray');

        $dbMock = $this->getMockBuilder('\Box_Database')->getMock();
        $model = new \Model_Invoice();
        $model->loadBean(new \DummyBean());
        $dbMock->expects($this->atLeastOnce())
            ->method('findOne')
            ->will($this->returnValue($model));

        $di = new \Pimple\Container();
        $di['validator'] = $validatorMock;
        $di['db'] = $dbMock;

        $this->api->setDi($di);
        $this->api->setService($serviceMock);
        $this->api->setIdentity(new \Model_Admin());

        $data['hash'] = md5(1);
        $result = $this->api->update($data);
        $this->assertIsBool($result);
        $this->assertTrue(true);
    }

    public function testupdateInvoiceNotFound()
    {
        $validatorMock = $this->getMockBuilder('\FOSSBilling\Validate')->getMock();
        $validatorMock->expects($this->atLeastOnce())
            ->method('checkRequiredParamsForArray');

        $dbMock = $this->getMockBuilder('\Box_Database')->getMock();
        $model = new \Model_Invoice();
        $model->loadBean(new \DummyBean());
        $dbMock->expects($this->atLeastOnce())
            ->method('findOne')
            ->will($this->returnValue(null));

        $di = new \Pimple\Container();
        $di['validator'] = $validatorMock;
        $di['db'] = $dbMock;

        $this->api->setDi($di);
        $this->api->setIdentity(new \Model_Admin());

        $data['hash'] = md5(1);
        $this->expectException(\FOSSBilling\Exception::class);
        $this->expectExceptionMessage('Invoice was not found');
        $this->api->update($data);
    }

    public function testupdateInvoiceIsPaid()
    {
        $validatorMock = $this->getMockBuilder('\FOSSBilling\Validate')->getMock();
        $validatorMock->expects($this->atLeastOnce())
            ->method('checkRequiredParamsForArray');

        $dbMock = $this->getMockBuilder('\Box_Database')->getMock();
        $model = new \Model_Invoice();
        $model->loadBean(new \DummyBean());
        $model->status = 'paid';
        $dbMock->expects($this->atLeastOnce())
            ->method('findOne')
            ->will($this->returnValue($model));

        $di = new \Pimple\Container();
        $di['validator'] = $validatorMock;
        $di['db'] = $dbMock;

        $this->api->setDi($di);
        $this->api->setIdentity(new \Model_Admin());

        $data['hash'] = md5(1);
        $this->expectException(\FOSSBilling\Exception::class);
        $this->expectExceptionMessage('Paid Invoice can not be modified');
        $this->api->update($data);
    }

    public function testgateways()
    {
        $gatewayServiceMock = $this->getMockBuilder('\Box\Mod\Invoice\ServicePayGateway')->getMock();
        $gatewayServiceMock->expects($this->atLeastOnce())
            ->method('getActive')
            ->will($this->returnValue(array()));

        $di = new \Pimple\Container();
        $di['mod_service'] = $di->protect(function () use($gatewayServiceMock) {return $gatewayServiceMock;});

        $this->api->setDi($di);

        $result = $this->api->gateways(array());
        $this->assertIsArray($result);
    }

    public function testpayment()
    {
        $data = array(
            'hash' => '',
            'gateway_id' => '',
        );
        $serviceMock = $this->getMockBuilder('\Box\Mod\Invoice\Service')->getMock();
        $serviceMock->expects($this->atLeastOnce())
            ->method('processInvoice')
            ->will($this->returnValue(array()));

        $this->api->setService($serviceMock);

        $result = $this->api->payment($data);
        $this->assertIsArray($result);
    }

    public function testpaymentMissingHashParam()
    {
        $data = array(
            'gateway_id' => '',
        );

        $this->expectException(\FOSSBilling\Exception::class);
        $this->expectExceptionCode(810);
        $this->expectExceptionMessage('Invoice hash not passed. Missing param hash');
        $this->api->payment($data);
    }

    public function testpaymentMissingGatewayIdParam()
    {
        $data = array(
            'hash' => '',
        );

        $this->expectException(\FOSSBilling\Exception::class);
        $this->expectExceptionCode(811);
        $this->expectExceptionMessage('Payment method not found. Missing param gateway_id');
        $this->api->payment($data);
    }

    public function testpdf()
    {
        $data = array(
            'hash' => '',
        );
        $serviceMock = $this->getMockBuilder('\Box\Mod\Invoice\Service')->getMock();
        $serviceMock->expects($this->atLeastOnce())
            ->method('generatePDF');

        $validatorMock = $this->getMockBuilder('\FOSSBilling\Validate')->disableOriginalConstructor()->getMock();
        $validatorMock->expects($this->atLeastOnce())
            ->method('checkRequiredParamsForArray')
            ->will($this->returnValue(null));

        $di = new \Pimple\Container();
        $di['validator'] = $validatorMock;
        $this->api->setDi($di);

        $this->api->setService($serviceMock);

        $this->api->pdf($data);
    }
}
