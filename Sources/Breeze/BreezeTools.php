<?php

/**
 * BreezeTools
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeTools
{
	protected $_pattern;
	protected $_app;

	function __construct($app)
	{
		$this->_pattern = Breeze::$name .'_';
		$this->_app = $app;
	}

	/**
	 * BreezeTools::text()
	 *
	 * Gets a string key, and returns the associated text string.
	 * @param string $var The text string key.
	 * @global $txt
	 * @return string|boolean
	 */
	public function text($var)
	{
		global $txt;

		if (empty($var))
			return false;

		// Load the mod's language file.
		loadLanguage(Breeze::$name);

		if (!empty($txt[$this->_pattern . $var]))
			return $txt[$this->_pattern . $var];

		else
			return false;
	}

	/**
	 * BreezeTools::adminText()
	 *
	 * Gets a string key, and returns the associated text string for admin related text.
	 * @param string $var The text string key.
	 * @global $txt
	 * @return string|boolean
	 */
	public function adminText($var)
	{
		global $txt;

		if (empty($var))
			return false;

		// Load the mod's language file.
		loadLanguage(Breeze::$name .'Admin');

		if (!empty($txt[$this->_pattern . $var]))
			return $txt[$this->_pattern . $var];

		else
			return false;
	}

	/**
	 * BreezeTools::enable()
	 *
	 * Gets a name and checks if the appropriated settings does exists, returns false otherwise.
	 * @param string $var the setting's name
	 * @global $modSettings
	 * @return boolean
	 */
	public function enable($var)
	{
		global $modSettings;

		if (empty($var))
			return false;

		if (isset($modSettings[$this->_pattern . $var]) && !empty($modSettings[$this->_pattern . $var]))
			return true;

		else
			return false;
	}

	/**
	 * BreezeTools::setting()
	 *
	 * returns the requested setting.
	 * @param string $var the setting's name
	 * @return string|boolean
	 */
	public function setting($var)
	{
		global $modSettings;

		if (empty($var))
			return false;

		global $modSettings;

		if (true == $this->enable($var))
			return $modSettings[$this->_pattern . $var];

		else
			return false;
	}

	/**
	 * BreezeTools::timeElapsed()
	 *
	 * Gets an unix timestamp and returns a relative date from the current time.
	 * @param integer $ptime An unix timestamp
	 * @link http://www.zachstronaut.com/posts/2009/01/20/php-relative-date-time-string.html
	 * @return string
	 */
	public function timeElapsed($ptime)
	{
		$etime = time() - $ptime;

		if ($etime < 1)
			return $this->text('time_just_now');

		$a = array(
			12 * 30 * 24 * 60 * 60	=> $this->text('time_year'),
			30 * 24 * 60 * 60		=> $this->text('time_month'),
			24 * 60 * 60			=> $this->text('time_day'),
			60 * 60					=> $this->text('time_hour'),
			60						=> $this->text('time_minute'),
			1						=> $this->text('time_second')
		);

		foreach ($a as $secs => $str)
		{
			$d = $etime / $secs;
			if ($d >= 1)
			{
				$r = round($d);
				return $r . ' ' . $str . ($r > 1 ? 's ' : ' '). $this->text('time_ago');
			}
		}
	}

	/**
	 * BreezeTools::isJson()
	 *
	 * Checks if a given string is a json string
	 * @param string $string a text to check
	 * @return boolean
	 */
	public function isJson($string)
	{
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	// A function to cut-off a string
	public function truncateString($string, $limit, $break = ' ', $pad = '...')
	{
		if(empty($string))
			return false;

		if(empty($limit))
			$limit = 30;

		 // return with no change if string is shorter than $limit
		if(strlen($string) <= $limit)
			return $string;

		// is $break present between $limit and the end of the string?
		if(false !== ($breakpoint = strpos($string, $break, $limit)))
			if($breakpoint < strlen($string) - 1)
				$string = substr($string, 0, $breakpoint) . $pad;

		return $string;
	}

	/**
	 * BreezeTools::returnKey()
	 *
	 * Checks if a value on a multidimensional array exists and return the main key
	 * @param string $value The value to check
	 * @param array $array The array to check against.
	 * @return string|boolean returns the key if it does exists.
	 */
	public function returnKey($value, $array)
	{
		if (empty($value) || empty($array))
			return false;

		foreach ($array as $k => $v)
		{
			if (is_array($v))
			{
				if (in_array($value, $v))
					return $k;

					else
						return false;
			}

			else
			{
				if ($v == $value)
					return $k;

				else
					return false;
			}
		}
	}

	/**
	 * BreezeTools::remove()
	 *
	 * Removes a key from a multidimensional array.
	 * @param array $array The array to remove the value from.
	 * @param string|boolean  the value to remove.
	 * @param boolean $preserve_keys
	 * @return array The modified array.
	 */
	public function remove($array, $val, $preserve_keys = true)
	{
		if (empty($array) || empty($val) || !is_array($array))
			return false;

		if (!is_array($val))
		{
			if (!in_array($val, $array))
				return $array;

			foreach($array as $key => $value)
			{
				if ($value == $val)
					unset($array[$key]);
			}
		}

		elseif (is_array($val))
		{
			foreach($val as $find)
				foreach($array as $key => $value)
				{
					if (empty($array) || !is_array($array))
						return false;

					if ($value == $find)
						unset($array[$key]);
				}
		}

		else
			return false;

		return ($preserve_keys === true) ? $array : array_values($array);
	}

	/**
	 * BreezeTools::loadUserInfo()
	 *
	 * Loads the specified user or users information.
	 * @param integer|array $id user(s) unique ID.
	 * @param boolean $returnID return the loaded ID.
	 * @return integer the loaded IDs
	 */
	public function loadUserInfo($id, $returnID = false)
	{
		global $memberContext;

		// If this isn't an array, lets change it to one
		if (!is_array($id))
			$id = array($id);

		// $memberContext gets set and globalized, we're gonna take advantage of it
		$loaded_ids = loadMemberData($id, false, 'profile');

		// Set the context var
		foreach ($id as $u)
		{
			// Avoid SMF showing an awful error message
			if (is_array($loaded_ids) && in_array($u, $loaded_ids))
			{
				loadMemberContext($u);

				// Normal context var
				BreezeUserInfo::profile($u);
			}

			// Poster is a guest
			else
				BreezeUserInfo::guest($u);
		}

		// Lastly, if the ID was requested, sent it back!
		if ($returnID)
			return $loaded_ids;
	}

	/**
	 * BreezeTools::permissions()
	 *
	 * Handles status/comments related permissions, it does it on a case per case basics.
	 * @param string $type Either a comment or a status.
	 * @param integer $profileOwner the profile where this status/comment was posted.
	 * @param integer $userPoster The person who posted this status/comment
	 * @return array all possible permissions as integer values. 0 can't, 1 can.
	 */
	public function permissions($type, $profileOwner = false, $userPoster = false)
	{
		global $user_info;

		// Add this bit here to make it easier in the future to add more permissions.
		$perm = array(
			'edit' => false,
			'delete' => '',
			'post' => false,
			'postComments' => false,
		);

		// NO! you don't have permission to do nothing...
		if ($user_info['is_guest'] || !$userPoster || !$profileOwner || empty($type))
			return $perm;

		// Profile owner?
		$isProfileOwner = $profileOwner == $user_info['id'];

		// Status owner?
		$isPosterOwner = $userPoster == $user_info['id'];


		// Lets check the posing bit first. Profile owner can always post.
		if ($isProfileOwner)
		{
			$perm['post'] = true;
			$perm['postComments'] = true;
		}

		else
		{
			$perm['post'] = allowedTo('breeze_post'. $type);
			$perm['postComments'] = allowedTo('breeze_postComments');
		}

		// It all starts with an empty vessel...
		$allowed = array();

		// Your own data?
		if ($isPosterOwner && allowedTo('breeze_deleteOwn'. $type))
			$allowed[] = 1;

		// Nope? then is this your own profile?
		if ($isProfileOwner && allowedTo('breeze_deleteProfile'. $type))
			$allowed[] = 1;

		// No poster and no profile owner, must be an admin/mod or something.
		if (allowedTo('breeze_delete'. $type))
			$allowed[] = 1;

		$perm['delete'] = in_array(1, $allowed);

		return $perm;
	}
}
