<?php

use PHPUnit\Framework\TestCase;

class InitializationTest extends TestCase{

	/**
     * @expectedException BadMethodCallException
     */
	public function testInvalidTCPConnectionString(){
		$redisSessionHandler = new RedisJsonSessionHandler('127.0.0.1');
	}

	/**
     * @expectedException BadMethodCallException
     */
	public function testInvalidConnectionParams(){
		$redis = RedisJsonSessionHandler::getRedisConnection(['port'=> 6379]);
	}

	public function testTCPConnectionParamsConstructorParsing(){
		//test tcp with default port
		$handler = new RedisJsonSessionHandler('tcp://127.0.0.1');
		$connectionParams = $handler->getRedisConnectionParams();
		$expectedParams = ['host' => 'tcp://127.0.0.1', 'port' => 6379];
		$this->assertEquals($expectedParams, $connectionParams);

		//test tcp with custom port
		$handler = new RedisJsonSessionHandler('tcp://196.168.1.1', 1234);
		$connectionParams = $handler->getRedisConnectionParams();
		$expectedParams = ['host' => 'tcp://196.168.1.1', 'port' => 1234];
		$this->assertEquals($expectedParams, $connectionParams);

		//test unix socket
		$handler = new RedisJsonSessionHandler('unix:///var/run/redis/redis.sock');
		$connectionParams = $handler->getRedisConnectionParams();
		$expectedParams = ['host' => 'unix:///var/run/redis/redis.sock'];
		$this->assertEquals($expectedParams, $connectionParams);
	}



}