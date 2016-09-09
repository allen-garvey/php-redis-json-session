<?php
class RedisJsonSessionHandler implements SessionHandlerInterface{
    const DEFAULT_REDIS_HOST = '127.0.0.1';
    const DEFAULT_REDIS_PORT = 6379;
    
    protected $savePath;
    protected $redis;
    protected $redisPort;
    protected $redisHost;

    function __construct(string $host=null, int $port=null){
        $this->redisHost = !is_null($host) ? $host : self::DEFAULT_REDIS_HOST;
        $this->redisPort = !is_null($port) ? $port : self::DEFAULT_REDIS_PORT;
    }

    public static function sessionSerializeArray($data) : string{
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

    //adapted from http://us.php.net/session_decode
    public static function unserializeSessionData($session_data) : array{
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

    protected function getRedisConnection(){
        $redis = new Redis();
        $redis->connect($this->redisHost, $this->redisPort);
        return $redis;
    }

    protected function jsonEncodeSessionData($sessionData){
        $rawData = self::unserializeSessionData($sessionData);
        $jsonData = json_encode($rawData, JSON_FORCE_OBJECT);
        return $jsonData;
    }

    protected function sessionEncodeJsonData($jsonData){
        $decodedData = json_decode($jsonData);
        return self::sessionSerializeArray($decodedData);
    }

    public function open($savePath, $sessionName){
        $this->savePath = $savePath;
        $this->redis = $this->getRedisConnection();
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
        $encodedData = $this->sessionEncodeJsonData($rawData);
        return $encodedData;
    }

    //Save session data
    //session data is in session serialized format
    public function write($id, $data){
        $maxlifetime = ini_get("session.gc_maxlifetime");
        $jsonData = $this->jsonEncodeSessionData($data);
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





