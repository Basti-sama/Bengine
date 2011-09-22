<?php
/**
 * Object-oriented typing: String
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: String.util.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class String extends Type
{
	/**
	 * This is the actual string.
	 *
	 * @var string
	 */
	protected $string = "";

	/**
	 * If the string can be sent via sql search.
	 *
	 * @var boolean
	 */
	public $validSearchString = false;

	/**
	 * Min. length for the string to be a valid search string.
	 *
	 * @var integer
	 */
	protected $minLenghtForSearch = 3;

	/**
	 * Allows the following encryptions with encoding function.
	 *
	 * @var array
	 */
	protected $validEncryptions = array("md5", "sha1", "crypt", "crc32", "base64_encode");

	/**
	 * Initializes a newly created String object.
	 *
	 * @param string
	 * @return void
	 */
	public function __construct($string)
	{
		$this->string = (string) $string;
		return;
	}

	/**
	 * Destructor.
	 *
	 * @return void
	 */
	public function __destruct()
	{
		unset($this);
		return;
	}

	/**
	 * Returns the String as a character set.
	 *
	 * @return string
	 */
	public function get()
	{
		return $this->string;
	}

	/**
	 * Compares two strings.
	 *
	 * @param mixed		String to compare with
	 * @param boolean	Case sensitive comparision [optional]
	 *
	 * @return boolean True, if strings are equal, false, if not
	 */
	public function compareTo($string, $strict = false)
	{
		try { $compareTo = $this->getFromArgument($string); }
		catch(Exception $e) { $e->printError(); }
		return Str::compare($this->string, $compareTo->get(), $strict);
	}

	/**
	 * Returns true if and only if this string contains the
	 * specified sequence of char values.
	 *
	 * @param string	The sequence to search for
	 *
	 * @return boolean	True if this String contains string, false otherwise
	 */
	public function contains($string)
	{
		try { $contains = $this->getFromArgument($string); }
		catch(Exception $e) { $e->printError(); }
		if(strpos($this->string, $contains->get()) !== false)
		{
			return true;
		}
		return false;
	}

	/**
	 * Creates a new string resulting from replacing all occurrences
	 * of needle in this string with replacement.
	 *
	 * @param string	Needle
	 * @param string	Replacement
	 *
	 * @return String
	 */
	public function replace($needle, $replacement)
	{
		try { $search = $this->getFromArgument($needle); }
		catch(Exception $e) { $e->printError(); }
		try { $replace = $this->getFromArgument($replacement); }
		catch(Exception $e) { $e->printError(); }

		$this->string = Str::replace($search->get(), $replace->get(), $this->string);
		return $this;
	}

	/**
	 * Creates a new string that is a substring of this string.
	 *
	 * @param integer	The beginning index, inclusive
	 * @param integer	The ending index, exclusive [optional]
	 *
	 * @return String
	 */
	public function substring($beginIndex, $endIndex = null)
	{
		$this->string = Str::substring($this->string, $beginIndex, $endIndex);
		return $this;
	}

	/**
	 * Tells whether or not this string matches the given regular expression.
	 *
	 * @param string	The regular expression to which this string is to be matched
	 *
	 * @return boolean	True if, and only if, this string matches the given regular expression
	 */
	public function matches($regex)
	{
		$regex = strval($regex);
		if(preg_match($regex, $this->string) === 0)
		{
			return false;
		}
		return true;
	}

	/**
	 * Perform a regular expression search and replace.
	 *
	 * @param string	Regular expression
	 * @param String	Replacement
	 * @param integer	The maximum possible replacements for each pattern [optional]
	 *
	 * @return String
	 */
	public function regEx($regex, $replacement)
	{
		$replacement = $this->getFromArgument($replacement, "String");
		$this->string = preg_replace($regex, $replacement->get(), $this->string);
		return $this;
	}

	/**
	 * Converts all of the characters in this String to lower case.
	 *
	 * @return String
	 */
	public function toLowerCase()
	{
		$this->string = strtolower($this->string);
		return $this;
	}

	/**
	 * Converts all of the characters in this String to upper case.
	 *
	 * @return String
	 */
	public function toUpperCase()
	{
		$this->string = strtoupper($this->string);
		return $this;
	}

	/**
	 * Overwrites this string.
	 *
	 * @param string	String to put into this object
	 *
	 * @return String
	 */
	public function set($string)
	{
		$string = $this->getFromArgument($string);
		$this->string = (string) $string->get();
		return $this;
	}

	/**
	 * Adds string to the end of this string.
	 *
	 * @param mixed
	 *
	 * @return String
	 */
	public function push($string)
	{
		try { $push = $this->getFromArgument($string); }
		catch(Exception $e) { $e->printError(); }
		$this->string .= $push->get();
		return $this;
	}

	/**
	 * Adds string to the beginning of this string.
	 *
	 * @param mixed
	 *
	 * @return String
	 */
	public function pop($string)
	{
		try { $pop = $this->getFromArgument($string); }
		catch(Exception $e) { $e->printError(); }
		$this->string = $pop->get().$this->string;
		return $this;
	}

	/**
	 * Returns the number of characters containing this string.
	 *
	 * @return integer	Length
	 */
	public function length()
	{
		return Str::length($this->string);
	}

	/**
	 * Encodes string to specified encryption method.
	 *
	 * @param string	Encryption method
	 *
	 * @return String
	 */
	public function encode($encryption = "md5")
	{
		if(in_array($encryption, $this->validEncryptions))
		{
			$this->string = $encryption($this->string);
			return $this;
		}
		throw new Recipe_Exception_Issue("Unkown encryption method.");
		return $this;
	}

	/**
	 * Sets an random string.
	 * Warning: Overwrites current string.
	 *
	 * @param integer	The length of the random string
	 *
	 * @return String
	 */
	public function random($length)
	{
		try { $length = $this->getFromArgument($length); }
		catch(Exception $e) { $e->printError(); }
		$this->string = randString($length->get());
		return $this;
	}

	/**
	 * Kills this object.
	 *
	 * @return void
	 */
	public function kill()
	{
		$this->__destruct();
		return;
	}

	/**
	 * Empty string.
	 *
	 * @return String
	 */
	public function flush()
	{
		$this->string = "";
		return $this;
	}

	/**
	 * Strip whitespace from the beginning and end of a string.
	 *
	 * @return String
	 */
	public function trim()
	{
		$this->string = trim($this->string);
		return $this;
	}

	/**
	 * Prints the string.
	 *
	 * @return String
	 */
	public function out()
	{
		echo $this->string;
		return $this;
	}

	/**
	 * Shuffles a string.
	 *
	 * @return String
	 */
	public function shuffle()
	{
		$this->string = str_shuffle($this->string);
		return $this;
	}

	/**
	 * Returns an Integer object made of this string.
	 *
	 * @return Integer
	 */
	public function getInteger()
	{
		return new Integer($this->toInteger());
	}

	/**
	 * Converts this string to an int value.
	 *
	 * @return integer
	 */
	public function toInteger()
	{
		return (int) $this->string;
	}

	/**
	 * Returns an Integer object made of this string.
	 *
	 * @return Float
	 */
	public function getFloat()
	{
		return new Float($this->toFloat());
	}

	/**
	 * Converts this string to a float value.
	 *
	 * @return float
	 */
	public function toFloat()
	{
		return floatval($this->string);
	}

	/**
	 * Prepares the string for SQL search.
	 *
	 * @return String
	 */
	public function prepareForSearch()
	{
		$this->replace("%", "");
		if(Str::length(Str::replace("*", "", $this->string)) >= $this->minLenghtForSearch)
		{
			$this->validSearchString = true;
		}
		else { $this->validSearchString = false; }
		$this->replace("*", "%");
		$this->string = "%".$this->string."%";
		return $this;
	}

	/**
	 * Sets the min length for valid search strings.
	 *
	 * @param integer	Value to set
	 *
	 * @return String
	 */
	public function setMinSearchLenght($length)
	{
		$this->minLenghtForSearch = (int) $length;
		return $this;
	}

	/**
	 * Splits a string by string into an array.
	 *
	 * @param string	The boundary
	 * @param integer	The returned array will contain a maximum of limit [optional]
	 *
	 * @return array
	 */
	public function toArray($delmitter, $limit = -1)
	{
		$delmitter = $this->getFromArgument($delmitter);
		$limit = $this->getFromArgument($limit);
		return explode($delmitter->get(), $this->string, $limit->get());
	}

	/**
	 * Returns a Map object by splitting the string.
	 *
	 * @param string	The boundary
	 * @param integer	The returned array will contain a maximum of limit [optional]
	 *
	 * @return Map
	 */
	public function getMap($delmitter, $limit = -1)
	{
		return new Map($this->toArray($delmitter, $limit));
	}

	/**
	 * Uppercases the first character of each word.
	 *
	 * @return String
	 */
	public function upperWords()
	{
		$this->string = ucwords($this->string);
		return $this;
	}

	/**
	 * Called when an unkown method has been requested.
	 * Warning: Use only functions with a string as return value!
	 *
	 * @param string	Method name
	 * @param array		Arguments [optional]
	 *
	 * @return String
	 */
	public function __call($method, array $args = null)
	{
		$callback = $this->call($method, $args);
		if($callback !== false)
		{
			$this->string = $callback->get();
		}
		return $this;
	}

	/**
	 * Returns the string when trying to invoke the class by echo:
	 * echo $stringObj;
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->get();
	}

	/**
	 * Produces a string according to the given format.
	 *
	 * @param mixed		Argument 1 [optional]
	 * @param mixed		Argument 2 [optional]
	 * @param mixed		... [optional]
	 *
	 * @return String
	 */
	public function fromat()
	{
		$args = func_get_args();
		array_unshift($args, $this->string);
		$this->string = call_user_func_array("sprintf", $args);
		return $this;
	}
}
?>