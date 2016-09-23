<?php


class MockRedisAdapter{
	public function get(){

	}
	public function connect(){

	}
	public function close(){

	}
	public function delete(){

	}

	public function setEx(){

	}
}


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
		$redis = RedisJsonSessionHandler::getDbConnection(['port'=> 6379], 'MockRedisAdapter');
	}

	public function testTCPConnectionParamsConstructorParsing1(){
		//test tcp with default port
		$handler = new RedisJsonSessionHandler('tcp://127.0.0.1');
		$connectionParams = $handler->getRedisConnectionParams();
		$expectedParams = ['host' => 'tcp://127.0.0.1', 'port' => 6379];
		$this->assertEquals($expectedParams, $connectionParams);
	}

	public function testTCPConnectionParamsConstructorParsing2(){
		//test tcp with custom port
		$handler = new RedisJsonSessionHandler('tcp://196.168.1.1', 1234);
		$connectionParams = $handler->getRedisConnectionParams();
		$expectedParams = ['host' => 'tcp://196.168.1.1', 'port' => 1234];
		$this->assertEquals($expectedParams, $connectionParams);
	}

	public function testTCPConnectionParamsConstructorParsing3(){
		//test unix socket
		$handler = new RedisJsonSessionHandler('unix:///var/run/redis/redis.sock');
		$connectionParams = $handler->getRedisConnectionParams();
		$expectedParams = ['host' => 'unix:///var/run/redis/redis.sock'];
		$this->assertEquals($expectedParams, $connectionParams);
	}

	public function testExtractConnectionParamsFromSavePath1(){
		//test tcp with default port
		$savePath = 'tcp://196.168.1.1';
		$connectionParams = RedisJsonSessionHandler::extractConnectionParamsFromSavePath($savePath);
		$expectedParams = ['host' => 'tcp://196.168.1.1', 'port' => 6379];
		$this->assertEquals($expectedParams, $connectionParams);
	}

	public function testExtractConnectionParamsFromSavePath2(){
		//test tcp with custom port
		$savePath = 'tcp://196.168.1.1:1234';
		$connectionParams = RedisJsonSessionHandler::extractConnectionParamsFromSavePath($savePath);
		$expectedParams = ['host' => 'tcp://196.168.1.1', 'port' => 1234];
		$this->assertEquals($expectedParams, $connectionParams);
	}

	public function testExtractConnectionParamsFromSavePath3(){
		//test unix socket
		$savePath = 'unix:///var/run/redis/redis.sock';
		$connectionParams = RedisJsonSessionHandler::extractConnectionParamsFromSavePath($savePath);
		$expectedParams = ['host' => 'unix:///var/run/redis/redis.sock'];
		$this->assertEquals($expectedParams, $connectionParams);
	}



}