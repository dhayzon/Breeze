<?php

/**
 * BreezeNotifications
 *
 * The purpose of this file is to fetch all notifications for X user
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeNotifications
{
	protected $_params = array();
	protected $_user = 0;
	protected $_returnArray = array();
	protected $_usersData = array();
	public $types = array();
	protected $_currentUser;
	protected $_currentUserSettings = array();
	protected $_messages = array();
	protected $loadedUsers = array();
	protected $_app;

	/**
	 * BreezeNotifications::__construct()
	 *
	 * @return
	 */
	function __construct($app)
	{
		global $user_info;

		// Current user.
		$this->_currentUser = $user_info['id'];

		// Don't include the log type here since its, well, a log, and we'll retrieve it somewhere else...
		$this->types = array(
			'comments',
			'status',
			'like',
			// 'buddy', @todo refactor the buddy system.
			'mention',
			'messages',
			'topics',
			'wallOwner',
			'commentStatus',
			'commentStatusOwner',
		);

		// Say what again, I dare you, I double dare you!
		call_integration_hook('integrate_breeze_notifications_types', array(&$this->types));

		// We kinda need all this stuff, don't' ask why, just nod your head...
		$this->_app = $app;

		// Get the current user preferences.
		$this->_currentUserSettings = $this->_app['query']->getUserSettings($this->_currentUser);
	}

	public function getByReceiver($user)
	{
		return $this->_app['query']->getNotificationByReceiver($user);
	}

	public function getBySender($user)
	{
		return $this->_app['query']->getNotificationBySender($user);
	}

	/**
	 * BreezeNotifications::create()
	 *
	 * Take an array, figure it out the content and act according to it. Content can be a string, an array which gets converted to  json string
	 * or an anonymous function that gets executed right before been saved to the DB.
	 * @param array $params An array containing all sorts of goodies
	 * @return
	 */
	public function create($params)
	{
		// Don't do anything if the feature is disable
		if (!$this->_app['tools']->enable('notifications'))
			return false;

		// Before inserting...
		call_integration_hook('integrate_breeze_before_insertingNoti', array(&$params));

		// Is there additional content?
		if (!empty($params['content']))
			$params['content'] = is_array($params['content']) ? json_encode($params['content']) : (is_object($params['content']) ? $params['content']() : $params['content']);

		else
			$params['content'] = '';

		// If we didn't get this data, make it an empty string and be done with it...
		$params['type_id'] = !empty($params['type_id']) ? $params['type_id'] : '';
		$params['second_type'] = !empty($params['second_type']) ? $params['second_type'] : '';

		$this->_app['query']->insertNotification($params);
	}

	/**
	 * BreezeNotifications::createBuddy()
	 *
	 * @param mixed $params
	 * @return
	 */
	public function createBuddy($params)
	{
		loadLanguage(Breeze::$name);

		// if the type is buddy then let's do a check to avoid duplicate entries
		if (!empty($params) && in_array($params['type'], $this->types))
		{
			// Doing a quick query will be better than loading the entire notifications array
			$tempQuery = $this->_app['query']->quickQuery(
				array(
					'table' => 'breeze_notifications',
					'rows' => 'id',
					'where' => 'user = {int:user}',
					'and' => 'receiver = {int:receiver}',
					'andTwo' => 'type = {string:type}',
				),
				array(
					'user' => !empty($params['user']) ? $params['user'] : $this->_currentUser,
					'receiver' => $params['receiver'],
					'type' => $params['type'],
				),
				'id', false
			);

			// Patience is a virtue, you obviously don't know that, huh?
			if (!empty($tempQuery))
				fatal_lang_error('Breeze_buddyrequest_error_doublerequest', false);

			// We are good to go
			else
				$this->create($params);
		}

		else
			return false;
	}

	/**
	 * BreezeNotifications::prepare()
	 *
	 * Fetch all notifications assigned to a given user, loads the member settings if they aren't already loaded.
	 * Calls the appropriated local method if needed.
	 * @param int $user the user ID from where the notifications will be show
	 * @return
	 */
	public function prepare($user, $all = false)
	{
		// Safety
		if (empty($user))
			return false;

		// Get the right call
		$call = 'getNotificationByReceiver'. (!empty($all) ? 'All' : '');

		// Get all the notification for this user
		$this->_all = $this->_app['query']->$call($user);

		// Load the users data.
		$this->loadedUsers = $this->_app['query']->loadMinimalData($this->_all['users']);

		// Do this if there is actually something to show
		if (!empty($this->_all['data']))
		{
			// Call the methods
			foreach ($this->_all['data'] as $single)
				if (in_array($single['type'], $this->types) && $this->_app['tools']->isJson($single['content']))
				{
					// We're pretty sure there is a method for this noti and that content is a json string so...
					$single['content'] = json_decode($single['content'], true);

					$call = 'do' . ucfirst($single['type']);

					// Call the right method
					$this->$call($single);
				}

			// Let them know everything went better than expected!
			return true;
		}

		// Oh no! something went terrible wrong...
		else
			return false;
	}

	/**
	 * BreezeNotifications::doStream()
	 *
	 * @param int $user the user ID from where the notifications will be show
	 * @return
	 */
	public function doStream($user)
	{
		global $context;

		// Prepare the thingy...
		if (!$this->prepare($user))
			return false;

		return (array) $this->_messages;
	}

	/**
	 * BreezeNotifications::doBuddy()
	 *
	 * @param mixed $noti
	 * @return
	 */
	public function doBuddy($noti)
	{
		global $context;

		// Extra check
		if (empty($noti) || !is_array($noti) || $noti['receiver'] != $this->_currentUser)
			return false;

		// @todo let BreezeBuddy to handle all the logic here, you just need to take care of showing the actual message...

		$this->_messages[$noti['id']]['id'] = $noti['id'];
		$this->_messages[$noti['id']]['user'] = $noti['receiver'];
		$this->_messages[$noti['id']]['viewed'] = $noti['viewed'];

		// Fill out the messages property
		$this->_messages[$noti['id']]['message'] = sprintf($this->_app['tools']->text('buddy_messagerequest_message'),
			$this->loadedUsers[$noti['user']]['link'], $noti['id']);
	}

	/**
	 * BreezeNotifications::doMention()
	 *
	 * @param mixed $noti
	 * @return
	 */
	public function doMention($noti)
	{
		global $context, $scripturl;

		// Extra check
		if ($noti['receiver'] != $this->_currentUser)
			return false;

		// Yeah, we started with nothing!
		$text = '';

		// Build the status link
		$statusLink = $scripturl . '?action=wall;sa=single;u=' . $noti['content']['wall_owner'] .
			';bid=' . $noti['content']['status_id'];

		// Sometimes this data hasn't been loaded yet
		if (!isset($this->loadedUsers[$noti['content']['wall_poster']]) || !isset($this->loadedUsers[$noti['content']['wall_owner']]) || !isset($this->loadedUsers[$noti['content']['wall_mentioned']]))
			$this->loadedUsers = $this->loadedUsers + $this->_app['query']->loadMinimalData(array($noti['content']['wall_poster'], $noti['content']['wall_owner'], $noti['content']['wall_mentioned']));

		// Is this a mention on a comment?
		if (isset($noti['comment_id']) && !empty($noti['comment_id']))
		{
			// Is this the same user's wall?
			if ($noti['content']['wall_owner'] == $noti['receiver'])
				$text = sprintf($this->_app['tools']->text('mention_message_own_wall_comment'), $statusLink,
					$this->loadedUsers[$noti['content']['wall_poster']]['link'], $noti['id']);

			// This is someone else's wall, go figure...
			else
				$text = sprintf($this->_app['tools']->text('mention_message_comment'), $this->loadedUsers[$noti['content']['wall_poster']]['link'],
					$this->loadedUsers[$noti['content']['wall_owner']]['link'], $statusLink,
					$noti['id']);
		}

		// No? then this is a mention made on a status
		else
		{
			// Is this your own wall?
			if ($noti['content']['wall_owner'] == $noti['receiver'])
				$text = sprintf($this->_app['tools']->text('mention_message_own_wall_status'), $statusLink,
					$this->loadedUsers[$noti['content']['wall_poster']]['link'], $noti['id']);

			// No? don't worry, you will get your precious notification anyway
			elseif ($noti['content']['wall_owner'] != $noti['receiver'])
				$text = sprintf($this->_app['tools']->text('mention_message_comment'), $this->loadedUsers[$noti['content']['wall_poster']]['link'], $this->loadedUsers[$noti['content']['wall_owner']]['link'], $statusLink, $noti['id']);
		}

		// Create the message already
		$this->_messages[$noti['id']] = array(
			'id' => $noti['id'],
			'user' => $noti['receiver'],
			'message' => $text,
			'viewed' => $noti['viewed']
		);
	}

	public function doWallOwner($noti)
	{
		global $scripturl;

		// Extra check
		if ($noti['receiver'] != $this->_currentUser)
			return false;

		// Build the status link.
		$statusLink = $scripturl . '?action=wall;sa=single;u=' . $noti['content']['owner_id'] .
			';bid=' . $noti['content']['id'];

		// Sometimes this data hasn't been loaded yet
		$loadedUsers = $this->_app['query']->loadMinimalData(array($noti['content']['owner_id'], $noti['content']['poster_id'],));

		// Create the actual text.
		$text = sprintf($this->_app['tools']->text('noti_posted_wall'), $loadedUsers[$noti['content']['poster_id']]['link'], $statusLink);

		$this->_messages[$noti['id']] = array(
			'id' => $noti['id'],
			'user' => $noti['receiver'],
			'message' => $text,
			'viewed' => $noti['viewed']
		);
	}

	public function doCommentStatus($noti)
	{
		global $scripturl;

		// Build the status link.
		$statusLink = $scripturl . '?action=wall;sa=single;u=' . $noti['content']['profile_id'] .';bid='. $noti['content']['status_id'] .';cid=' . $noti['content']['id'];

		// Sometimes this data hasn't been loaded yet
		$loadedUsers = $this->_app['query']->loadMinimalData(array($noti['content']['profile_id'], $noti['content']['poster_id'], $noti['content']['status_owner_id']));

		// Is this your own wall?
		$preText = $noti['receiver'] == $noti['content']['profile_id'] ? 'noti_posted_comment_own_wall' : 'noti_posted_comment';

		// Create the actual text.
		$text = sprintf($this->_app['tools']->text($preText), $loadedUsers[$noti['content']['poster_id']]['link'], $statusLink, $loadedUsers[$noti['content']['profile_id']]['link']);

		$this->_messages[$noti['id']] = array(
			'id' => $noti['id'],
			'user' => $noti['receiver'],
			'message' => $text,
			'viewed' => $noti['viewed']
		);
	}

	public function doCommentStatusOwner($noti)
	{
		global $scripturl;

		// Build the status link.
		$statusLink = $scripturl . '?action=wall;sa=single;u=' . $noti['content']['profile_id'] .';bid='. $noti['content']['status_id'] .';cid=' . $noti['content']['id'];


		// Sometimes this data hasn't been loaded yet
		$loadedUsers = $this->_app['query']->loadMinimalData(array($noti['content']['profile_id'], $noti['content']['poster_id'], $noti['content']['status_owner_id']));

		// Create the actual text.
		$text = sprintf($this->_app['tools']->text('noti_posted_comment_owner'), $loadedUsers[$noti['content']['poster_id']]['link'], $statusLink);

		$this->_messages[$noti['id']] = array(
			'id' => $noti['id'],
			'user' => $noti['receiver'],
			'message' => $text,
			'viewed' => $noti['viewed']
		);
	}

	public function getMessages()
	{
		if (!empty($this->_messages))
			return $this->_messages;

		else
			return false;
	}

	public function getAll()
	{
		if (!empty($this->_all))
			return $this->_all;

		else
			return false;
	}
}
