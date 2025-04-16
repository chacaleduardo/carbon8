<?
define('RTS_HOST', '192.168.0.1');
define('RTS_PORT', '6380');

require_once "vendor/autoload.php";
use Palicao\PhpRedisTimeSeries\TimeSeries;
use Palicao\PhpRedisTimeSeries\Client\RedisClient;
use Palicao\PhpRedisTimeSeries\Client\RedisConnectionParams;
use Palicao\PhpRedisTimeSeries\Sample;
use Palicao\PhpRedisTimeSeries\Label;

class ts{
	private static $instance;

	public static function init() {
		if(!self::$instance) {
			self::$instance = new TimeSeries(
				new RedisClient(
					new Redis(),
					new RedisConnectionParams(RTS_HOST, RTS_PORT)
				)
			);
		}
	}

	public static function add($k,$v,$retentionMs=0,$labels=[]){
		self::init();

		$oLb=[];
		foreach ($labels as $key => $value) {
			array_push($oLb, new Label($key, $value));
		}

		self::$instance->add(
			new Sample($k, $v)
			, $retentionMs
			, $oLb
		);
	}

}
