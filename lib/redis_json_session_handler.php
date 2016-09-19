<?php
class RedisJsonSessionHandler implements SessionHandlerInterface{
    const DEFAULT_REDIS_HOST = '127.0.0.1';
    const DEFAULT_REDIS_PORT = 6379;
    
    protected $redis; //instance Redis connection
    protected $redisConnectionParams = null; //associative array with string $host and optional int $port

    function __construct(string $host=null, int $port=null){
        $this->redisConnectionParams = self::bundleConnectionParams($host, $port);
    }

    /***********************************************************
    * Getters and Setters
    ************************************************************/
    public function getRedisConnectionParams() : array{
        return $this->redisConnectionParams;
    }

    /***********************************************************
    * Redis Connection Functions
    ************************************************************/

    //bundles parsed host and port into associative array
    public static function bundleConnectionParams(string $host=null, int $port=null) : array{
        if(empty($host)){
            return array('host' => self::DEFAULT_REDIS_HOST, 'port' => self:: DEFAULT_REDIS_PORT);
        }
        //socket
        if(preg_match('|^unix:///.+\.sock$|', $host)){
            return array('host' => $host);
        }
        //check for tcp
        if(!preg_match('|^tcp://.+|', $host)){
            throw new BadMethodCallException(get_called_class().' php.ini session.save_path must be a valid unix socket or tcp url');
        }
        $ret = array('host' => $host, 'port' => self::DEFAULT_REDIS_PORT);
        if(!empty($port)){
            $ret['port'] = $port;
        }
        return $ret;
    }

    public static function extractConnectionParamsFromSavePath($savePath) : array {
        $split = explode(':', $savePath);
        if(count($split) === 1){
            return self:: bundleConnectionParams($split[0]);
        }
        else{
            return self:: bundleConnectionParams($split[0], (int) $split[1]);
        }
    }

    public static function getRedisConnection(array $connectionParams){
        $redis = new Redis();
        if(!array_key_exists('host', $connectionParams)){
            throw new BadMethodCallException(get_called_class().' host not given for Redis connection');
        }
        if(array_key_exists('port', $connectionParams)){
            $redis->connect($connectionParams['host'], $connectionParams['port']);
        }
        else{
            $redis->connect($connectionParams['host']);
        }
        return $redis;
    }

    /***********************************************************
    * Serialize and deserialize session from JSON functions
    ************************************************************/

    /**
    * Encodes an array into session serialized format string
    */
    public static function sessionSerializeArray(array $data=null) : string{
        if(empty($data)){
            return '';
        }
        $ret = '';
        foreach ($data as $key => $value) {
            $serialized = "$key|".serialize($value);
            $ret = $ret.$serialized;
        }
        return $ret;
    }

    /**
    * Decodes session serialized string (session.serialize_handler = php_serialize format) 
    * into associative array
    * adapted from http://us.php.net/session_decode
    */
    public static function unserializeSessionData(string $session_data=null) : array{
        $return_data = array();
        $offset = 0;
        while ($offset < strlen($session_data)) {
            if (!strstr(substr($session_data, $offset), "|")) {
                throw new Exception("invalid data, remaining: " . substr($session_data, $offset));
            }
            $pos = strpos($session_data, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($session_data, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }

    public static function jsonEncodeSessionData(string $sessionData=null){
        $rawData = self::unserializeSessionData($sessionData);
        $jsonData = json_encode($rawData, JSON_FORCE_OBJECT);
        return $jsonData;
    }

    public static function sessionEncodeJsonData(array $jsonData=null){
        $decodedData = json_decode($jsonData);
        return self::sessionSerializeArray($decodedData);
    }

    /***********************************************************
    * SessionHandlerInterface Functions
    ************************************************************/

    public function open($savePath, $sessionName){
        //check to see if connection params overridden by constructor
        if(is_null($this->redisConnectionParams)){
            $this->redisConnectionParams = self::extractConnectionParamsFromSavePath($savePath);
        }
        $this->redis = self::getRedisConnection($this->redisConnectionParams);
        return true;
    }

    //Any teardown work that needs to be done on session close
    public function close(){
        $this->redis->close();
        return true;
    }

    //Return session serialized session data
    public function read($id){
        $rawData = $this->redis->get($id);
        if($rawData === false){
            return '';
        }
        $encodedData = self::sessionEncodeJsonData($rawData);
        return $encodedData;
    }

    //Save session data
    //session data is in session serialized format
    public function write($id, $data){
        $maxlifetime = ini_get("session.gc_maxlifetime");
        $jsonData = self::jsonEncodeSessionData($data);
        $this->redis->setEx($id, $maxlifetime, $jsonData);
        return true;
    }

    //delete session data
    public function destroy($id){
        $this->redis->delete($id);

        return true;
    }

    //Delete expired sessions
    public function gc($maxlifetime){
        return true;
    }
}





