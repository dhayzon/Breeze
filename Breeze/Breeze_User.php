<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');
	
	/* A bunch of wrapper functions so static methods can be callable with a string by SMF */
	function Breeze_Wrapper_Wall(){Breeze_User::Wall();}
	function Breeze_Wrapper_Settings(){Breeze_User::Settings();}
	function Breeze_Wrapper_Permissions(){Breeze_User::Permissions();}

class Breeze_User
{
	/* Handle the actions and set the proper files to handle all */
	public function  __construct()
	{
	}

	public static function Wall()
	{
		global $txt, $scripturl, $context, $memID;

		loadLanguage('Breeze');
		loadtemplate('Breeze');
		LoadBreezeMethod(array(
			'Breeze_Settings',
			'Breeze_Subs',
			'Breeze_Globals'
		));
		Breeze_Subs::Headers();

		/* Set all the page stuff */
		$context['page_title'] = $txt['breeze_general_wall'];
		$context['sub_template'] = 'user_wall';

	}

}
?>