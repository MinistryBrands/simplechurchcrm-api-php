<?php

use PHPUnit\Framework\TestCase;
use SCCRM\SimpleChurchApi;

class SimpleChurchApiTest extends TestCase
{
    /**
     * Test that the class throws errors without
     * the required subDomain argument
     * @expectedException Exception
     * @expectedExceptionMessage Argument "subDomain" is required.
     */
    public function testThrowsExceptionWithoutSubDomain()
    {
        new SimpleChurchApi([]);
    }

    /**
     * Test that we can instantiate the class
     * and the class contains our subDomain
     */
    public function testClassCreatedWithArguments()
    {
        $api = new SimpleChurchApi([
            'subDomain' => 'test',
        ]);

        $this->assertInstanceOf(SimpleChurchApi::class, $api);
        $this->assertEquals('test', $api->getSubDomain());
        $this->assertEquals('', $api->getSessionId());
    }

    /**
     * Test the sessionId is set if supplied
     * in the arguments array
     */
    public function testSetSessionIdIfSupplied()
    {
        $api = new SimpleChurchApi([
            'subDomain' => 'test',
            'sessionId' => 'TestSessionPlzIgnore',
        ]);

        $this->assertEquals('TestSessionPlzIgnore', $api->getSessionId());
    }
}