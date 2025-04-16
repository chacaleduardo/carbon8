<?php
// constants
define('WITHSCORES', true);
#define('REDIS_CONNECTION', ((CLI) ? '/var/run/redis.socket' : '127.0.0.1')); // localhost or socket
define('REDIS_CONNECTION', '192.168.0.21'); // localhost only
define('REDIS_NAMESPACE', ''); // use custom prefix on all keys
define('REDIS_DB', 0);
#define('REDIS_AUTH', '');
define('REDIS_LOG_FILE', '/var/log/apache2/phpredis.txt');

// wrapper class
class re {
  // Singleton instance 
  private static $instance;
  // Redis instance
  private $redis;
  // Redis connect status
  private $connect;
  // Redis PID
  private $pid;

  // private constructor function 
  // to prevent external instantiation 
  private function __construct($pid = 0) { 
    if (class_exists('Redis')) {
      try {
        $this->redis = new Redis(); // needs https://github.com/nicolasff/phpredis
        $this->connect = $this->redis->connect(REDIS_CONNECTION);
        if (defined('REDIS_AUTH')) $redis->auth(REDIS_AUTH);
        if(REDIS_DB>0){
          $this->select(REDIS_DB);
        }
        $this->redis->setOption(Redis::OPT_PREFIX, REDIS_NAMESPACE);
        $this->pid = $pid;
      } catch (RedisException $e){
        $line = trim(date("[d/m @ H:i:s]") . "Redis connect Error: " . $e->getMessage()) . "\n";  
        error_log($line, 0);
        return false;
      }
    }
  }
  
  // destructor function
  function __destruct() {
    unset($this->redis);
    $this->connect = false;
    $this->pid = null;
  }

  // dis method 
  public static function dis($prozessor = null) { 
    if(is_null($prozessor)) $prozessor = posix_getpid();
    if(!self::$instance[$prozessor]) {
      self::$instance[$prozessor] = new self($prozessor); 
    } 
    return self::$instance[$prozessor]; 
  }
  
  // reInstance method 
  public function reInstance($prozessor = null) { 
    if(is_null($prozessor)) $prozessor = posix_getpid();
    $this->__construct($prozessor); 
  }

  // testPid method 
  public static function testPid($prozessor = null) { 
    if(is_null($prozessor)) $prozessor = posix_getpid();
    if(!self::$instance[$prozessor]) {
      return false;
    } 
    return (self::$instance[$prozessor]->pid == $prozessor) ? $prozessor : false; 
  }
  
  // Call a dynamically wrapper...
  public function __call($method, $args) { 
    if(method_exists($this->redis, $method)) { 
      try {
        $ret = call_user_func_array(array($this->redis, $method), $args);
        if($ret===false){
          $dbt=json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1));
          //maf030421: comentado para @todo: evitar lixo no caso de uso de set com nx (gabriel)
	  //error_log($dbt);
        }
        return $ret;
      } catch (RedisException $e){
        $line = trim(date("[d/m @ H:i:s]") . "Redis command ('{$method}') Error: " . $e->getMessage()) . "\n";  
        error_log($line, 3, REDIS_LOG_FILE);
        return false;
      }
    } else { 
      return false;
    } 
  }

  // Return an error
  public function status() {
    return ($this->connect && $this->PING() == '+PONG') ? true : false;
  }
  
  // Overwrite redis->getKeys
  public function getKeys($pattern) {
    $keys = $this->redis->keys($pattern);
    foreach($keys as $_k => $_v) if (strpos($_v, REDIS_NAMESPACE) === 0 ) $keys[$_k] = substr($_v, strlen(REDIS_NAMESPACE));
    return $keys;
  }
  
  // Key without REDIS_NAMESPACE and PATTERN
  public function clearKey($keys, $pattern = '//', $limit = -1) {
    if (!is_array($keys)) {
      $force_array = true; 
      $keys = array($keys);
    } else $force_array = false;
    if (!is_array($pattern)) $pattern = array($pattern);
    foreach($keys as $_k => $_v) if (strpos($_v, REDIS_NAMESPACE) === 0 ) $keys[$_k] = substr($_v, strlen(REDIS_NAMESPACE));
    $_keys = preg_replace($pattern, '', $keys, $limit);
    if(count($_keys) == 1 && $force_array) return $_keys[0];
    else return $_keys;
  }
}
