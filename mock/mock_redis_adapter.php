<?php 

class MockRedisAdapter{
	public $closeCalled = 0;

	public function get(){

	}
	public function connect(){

	}
	public function close(){
		$this->closeCalled++;
	}
	
	public function delete(){

	}

	public function setEx(){

	}
}