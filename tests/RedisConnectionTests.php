<?php 
//tests to make sure Redis connection is correctly opened and closed, and that data is saved correctly

use PHPUnit\Framework\TestCase;

class ReddisConnectionTest extends TestCase{
	public function testConnectionIsClosedCorrectly(){
		$handler = new new RedisJsonSessionHandler(null, null, 'MockRedisAdapter');
		
		//use reflection to get access to protected redis connection
		$reflector = new ReflectionProperty('RedisJsonSessionHandler', 'redis');
		$reflector->setAccessible(true);
		$redis = $reflector->getValue($handler, 'redis');

		//close should not be called before close()
		$this->assertEquals(0, $redis->closeCalled);
		$handler->close();
		//close should have been called once
		$this->assertEquals(1, $redis->closeCalled);
	}

}