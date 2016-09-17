<?php

use PHPUnit\Framework\TestCase;

class InitializationTest extends TestCase{

	/**
     * @expectedException BadMethodCallException
     */
	public function testInvalidTCPConnectionString(){
		$this->redisSessionHandler = new RedisJsonSessionHandler('127.0.0.1');
	}
}