<?php 

//use reflection to get value of private
//instance property for test purposes
function getPrivateProperty($instance, string $propertyName){
	$reflector = new ReflectionProperty(get_class($instance), $propertyName);
	$reflector->setAccessible(true);
	return $reflector->getValue($instance, $propertyName);
}