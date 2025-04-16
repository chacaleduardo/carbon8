<?PHP

$SAFESTRING_javascriptTR = array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',
	"\n"=>'\\n', '<'=>'\\074','>'=>'\\076','&'=>'\\046','--'=>'\\055\\055');
$SAFESTRING_javascriptTRbinary = array("\x0"=>'\\x0', "\x1"=>'\\x1', "\x2"=>'\\x2', "\x3"=>'\\x3', "\x4"=>'\\x4', 
	"\x5"=>'\\x5', "\x6"=>'\\x6', "\x7"=>'\\x7', "\x8"=>'\\x8', "\x9"=>'\\x9', "\xb"=>'\\xb', "\xc"=>'\\xc',
	"\xe"=>'\\xe', "\xf"=>'\\xf', "\x10"=>'\\x10', "\x11"=>'\\x11', "\x12"=>'\\x12', "\x13"=>'\\x13', "\x14"=>'\\x14', 
	"\x15"=>'\\x15', "\x16"=>'\\x16' ,"\x17"=>'\\x17', "\x18"=>'\\x18', "\x19"=>'\\x19', "\x1a"=>'\\x1a',
	"\x1b"=>'\\x1b', "\x1c"=>'\\x1c', "\x1d"=>'\\x1d', 
	"\x1e"=>'\\x1e', "\x1f"=>'\\x1f', "\x7f"=>'\\x7f', "\xff"=>'\\xff', 
	'\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n', '<'=>'\\074','>'=>'\\076','&'=>'\\046','--'=>'\\055\\055');

$THIS_SAFESTRING=strval("XXX_SOME_MAGIC_VALUE"); # the magic for __call

function is_SafeString( &$object ) {
	if (is_object($object)) {
		$object_name = get_class($object);
		return ( $object_name == $check );
	}   
	return false;
}

class SafeString
{
    var $UnsafeRawString=false; # PRIVATE :-)
    
    // constructor, takes the raw string
    function __construct( $RawString )
    {
        $this->UnsafeRawString = (string)$RawString;
    }

    # PHP4 constructor
    function SafeString( $RawString ) {
        $this->UnsafeRawString = (string)$RawString;
    }
    
    // returns the string safe for html output
    function toHTML()
    {
        return htmlentities( $this->UnsafeRawString );
    }
    
    // returns the string safe for SQL
    function toSQL()
    {
        return mysql_real_escape_string( $this->UnsafeRawString );
    }

	// return string escaped for use in Javascript (assumes no escapes in string)
	// This can come handy ;-)
	function toJavascriptString($escapeBinary=false)
	{
		global $SAFESTRING_javascriptTR, $SAFESTRING_javascriptTRbinary;
		if ($escapeBinary) {
			$res = strtr( $this->UnsafeRawString , $SAFESTRING_javascriptTRbinary);
		} else {
			$res = strtr( $this->UnsafeRawString , $SAFESTRING_javascriptTR);
		}
		return $res;
	}

	// returns the string suitable for usage in HTTP-headers
	// result must not contain \0,\x0d,\x0a -- prevent HTTP-response splitting attacks
	function toHeader()
	{
        return str_replace("\0","",str_replace("\x0d", "", str_replace("\x0a","",$this->UnsafeRawString)));
	}

	// returns the string suitable for a file-name
	function toFilename()
	{
		$result = $this->toHeader(); # no \0 etc. in filenames!
		$result = preg_replace("/[^\.\-\s_a-zA-Z\d]/","",$result); # remove everything bad: / \ | > < etc.
		return $result;
	}

	// returns the string suitable for usage in Cookies
	// FIXME: delete stuff unsuitable for cookies such as ;
	function toCookie()
	{
		return $this->toHeader();
	}

    // returns the string safe for Regular Expressions
	// such that all meta-characters are escaped (i.e. the user can NOT specify
	// a pattern; "(abc)*" will become "\(abc\)\*" )
    function toRegEx($delim="/")
    {
        return preg_quote( str_replace("\0","",$this->UnsafeRawString) , $delim );
    }

    // returns the string safe for Regular Expressions
	// same as above, but the user can have patterns; escapes delimiters.
    function toRegExIsRegEx($delim="/")
    {
        return str_replace($delim, "\\".$delim, str_replace("\0","",$this->UnsafeRawString));
    }

    // returns the string safe for Shell Arguments
    function toShellArg()
    {
        return escapeshellarg( $this->toHeader() );
    }

    // returns the string safe for Shell Commands
    function toShellCmd()
    {
        return escapeshellcmd( $this->toHeader() );
    }

    function toInt()
    {
        return (int) intval( $this->UnsafeRawString );
    }

    // returns the raw (unescaped) string
    function toUnsafeRawString()
    {
        return $this->UnsafeRawString;
    }

	// make this call an arbitrary function for the string say $foo->strcmp would call __call
	// deal with params in array;
	// mixed __call ( string $name, array $arguments )
	// Caller, applied when $function isn't defined
	function __call($function, $arguments, &$result) {
		global $THIS_SAFESTRING;
		// Constructor called in PHP version < 5
		if ($function != __CLASS__) {
			foreach ($arguments as $key => $val) { ## WAS &$val; only in PHP5!
				if (is_string($val)) {
					if (strcmp($val,$THIS_SAFESTRING)==0) {
						$arguments[$key] = $this->toUnsafeRawString();
					}
				} else {
					if (is_SafeString($val)) {
						$arguments[$key] = $arguments[$key]->toUnsafeRawString();
					}
				}
			}
			$r = call_user_func_array($function,$arguments);
			if (is_string($r)) {
				$result=new SafeString($r);
			} else {
				$result = $r;
			}
		}
		if (phpversion() < 5) return true;
	}
}

// Call the overload() function when appropriate
if (function_exists("overload") && phpversion() < 5) {
   overload("SafeString");
}

function cleanKey($key) {
  # by convention we now assume, that all array keys (that come from user-input)
  # may only contain letters and numbers
  # otherwise, people might do sneaky things like:
  # /url?www[][<SCRIPT>]=42
  return preg_replace("/[^\w\d]+/i","",$key);
}

function convertToSaveString($var,$depth=0) {
  if (is_array($var)) {
    foreach ($var as $key => $value) {
		$key2=cleanKey($key);
		if (strcmp($key2,$key)!=0) {
		  $var[$key2] = convertToSaveString($var[$key], $depth+1);
		  unset($var[$key]);
		} else {
		  $var[$key] = convertToSaveString($var[$key], $depth+1);
		}
    }
    return $var;
  } else {
    if (is_string($var)) {
      return new SafeString(strval($var));
    } else {
      echo "SafeString Warning: NOT A STRING: $var<BR>\n";
      return new SafeString(strval($var));
    }
  }
}

?>
