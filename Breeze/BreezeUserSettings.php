<?php

/**
 * BreezeUserSettings.php
 *
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0 Beta 2 Beta 1
 * @author Jessica Gonz�lez <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica Gonz�lez
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica Gonz�lez.
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class BreezeUserSettings
{
	private $_data = array();
	private static $already = false;

	function __construct()
	{
		global $context;

		if (isset($context['Breeze']['UserSettings'][$context['member']['id']]) && !empty($context['Breeze']['UserSettings'][$context['member']['id']]))
		{
			$this->_data = $context['Breeze']['UserSettings'][$context['member']['id']];
			self::$already = true;
		}
	}

	function currentUserSettings()
	{
		global $context;

		if (!self::$already)
		{
			/* Load the user settings */
			$query_params = array(
				'rows' =>'*',
				'where' => 'user_id={int:user_id}',
			);
			$query_data = array(
				'user_id' => $context['member']['id'],
			);
			$query = new BreezeDB('breeze_user_settings');
			$query->params($query_params, $query_data);
			$query->getData(null, true);
			$this->_data = $query->dataResult();

			if (!empty($data))
				$context['Breeze']['UserSettings'][$context['member']['id']] = $this->_data;
		}
	}

	function LoadUserSettings($user)
	{
		global $context;

		if (!self::$already)
		{
			/* Load the user settings */
			$query_params = array(
				'rows' =>'*',
				'where' => 'user_id={int:user_id}',
			);
			$query_data = array(
				'user_id' => $user,
			);
			$query = new BreezeDB('breeze_user_settings');
			$query->params($query_params, $query_data);
			$query->getData(null, true);
			$this->_data = $query->dataResult();

			if (!empty($data))
				$context['Breeze']['UserSettings'][$user] = $this->_data;
		}
	}

	function enable($setting)
	{
		if (!empty($this->_data[$setting]))
			return true;
		else
			return false;
	}

	function setting($setting)
	{
		if (!empty($this->_data[$setting]))
			return $this->_data[$setting];
		else
			return false;
	}
}