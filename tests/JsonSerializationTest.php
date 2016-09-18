<?php

use PHPUnit\Framework\TestCase;

class JsonSerializationTest extends TestCase{

	//test array to session string
	public function testSessionSerializeArray(){
		$testArray = ['test' => 'hello', 'num' => 3, 'array' => [1, 2, 'test'], 'associative array' => ['key1' => 1, 'key2' => 'hello']];
		$serializedArray = 'test|s:5:"hello";num|i:3;array|a:3:{i:0;i:1;i:1;i:2;i:2;s:4:"test";}associative array|a:2:{s:4:"key1";i:1;s:4:"key2";s:5:"hello";}';
		$sessionSerializedArray = RedisJsonSessionHandler::sessionSerializeArray($testArray);
		$this->assertEquals($serializedArray, $sessionSerializedArray);
	}

	public function testSessionSerializeEmpty(){
		$this->assertEquals('', RedisJsonSessionHandler::sessionSerializeArray(null));
		$this->assertEquals('', RedisJsonSessionHandler::sessionSerializeArray([]));
	}

	//test session string to array
	public function unserializeSessionDataToArray(){
		$testArray = ['test' => 'hello', 'num' => 3, 'array' => [1, 2, 'test'], 'associative array' => ['key1' => 1, 'key2' => 'hello']];
		$serializedArray = 'test|s:5:"hello";num|i:3;array|a:3:{i:0;i:1;i:1;i:2;i:2;s:4:"test";}associative array|a:2:{s:4:"key1";i:1;s:4:"key2";s:5:"hello";}';
		$sessionUnserializedArray = RedisJsonSessionHandler::unserializeSessionData($serializedArray);
		$this->assertEquals($testArray, $sessionUnserializedArray);
	}

	public function unserializeEmptySessionDataToArray(){
		$this->assertEquals([], RedisJsonSessionHandler::unserializeSessionData(null));
		$this->assertEquals([], RedisJsonSessionHandler::unserializeSessionData(''));
	}
	
}