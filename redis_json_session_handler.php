<?php
class RedisJsonSessionHandler implements SessionHandlerInterface{
    private $savePath;


    protected function getRedisConnection(){
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        return $redis;
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
        return true;
    }

    //Any teardown work that needs to be done on session close
    public function close(){
        return true;
    }

    //Return session serialized session data
    public function read($id){
        $redis = $this->getRedisConnection();
        $rawData = $redis->get($id);
        if($rawData === false){
            $redis->close();
            return '';
        }
        $encodedData = $this->sessionEncodeJsonData($rawData);
        $redis->close();
        return $encodedData;
    }

    //Save session data
    //session data is in session serialized format
    public function write($id, $data){
        $maxlifetime = ini_get("session.gc_maxlifetime");
        $jsonData = $this->jsonEncodeSessionData($data);
        $redis = $this->getRedisConnection();
        $redis->setEx($id, $maxlifetime, $jsonData);
        $redis->close();

        return true;
    }

    //delete session data
    public function destroy($id){
        $redis = $this->getRedisConnection();
        $redis->delete($id);
        $redis->close();

        return true;
    }

    //Delete expired sessions
    public function gc($maxlifetime){
        return true;
    }
}





