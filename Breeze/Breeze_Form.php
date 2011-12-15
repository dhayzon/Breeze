<?php

/**
 * Breeze_
 *
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonz�lez <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica Gonz�lez
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
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

	class Breeze_Form
	{
		public $method;
		public $action;
		public $name;
		public $id_css;
		public $class;
		public $elements;
		public $status;
		public $buffer;
		public $onsubmit;

		function __construct($form = array())
		{
			global $scripturl;

			if (empty($form) || !is_array($form))
				return;

			$this->action = $scripturl . '?action=' . $form['action'];
			$this->method = $form['method'];
			$this->id_css= $form['id_css'];
			$this->name = $form['name'];
			$this->onsubmit = empty($form['onsubmit']) ? '' : 'onsubmit="'. $form['onsubmit'] .'"';
			$this->class_css = $form['class_css'];
			$elements = array();
			$this->status = 0;
			$this->buffer = '';
		}

		private function AddElement($element)
		{
			$plus = $this->CountElements();
			$element['id'] = $this->CountElements();
			$this->elements[$element['id']] = $element;
		}

		private function CountElements()
		{
			return count($this->elements);
		}

		private function GetElement($id)
		{
			return $this->elements[$id];
		}

		private function GetNextElement()
		{
			if( $this->status == $this->CountElements())
				$this->status = 0;

			$element = $this->GetElement($this->status);
			$this->status++;
		}

		function AddSelect($name, $text, $values = array())
		{
			$element['type'] = 'select';
			$element['name'] = $name;
			$element['values'] = $values;
			$element['text']  = $text;
			$element['html_start'] = '<'. $element['type'] .' name="' .$element['name']. '">';
			$element['html_end'] = '</'. $element['type'] .'>';

			foreach($values as $k => $v)
			{
				$element['values'][$k] = '<option value="' .$k. '">' .$v. '</option>';
			}

			return $this->AddElement($element);
		}

		function AddHiddenField($name,$value)
		{
			$element['type'] = 'hidden';
			$element['name'] = $name;
			$element['value'] = $value;
			$element['html'] = '<input type="'. $element['type'] .'" name="'. $element['name'] .'" value="'. $element['value'] .'">';

			return $this->AddElement($element);
		}

		function AddCheckBox($name,$value, $text, $checked = false)
		{
			$element['type'] = 'checkbox';
			$element['name'] = $name;
			$element['value'] = $value;
			$element['checked'] = empty($checked) ? '' : 'checked="checked"';
			$element['text'] = $text;
			$element['html'] = '<input type="'. $element['type'] .'" name="'. $element['name'] .'" value="'. $element['value'] .'" '. $element['checked'] .'>';

			return $this->AddElement($element);
		}

		function AddText($name,$value, $text, $size, $maxlength)
		{
			$element['type'] = 'text';
			$element['name'] = $name;
			$element['value'] = $value;
			$element['text'] = $text;
			$element['size'] = empty($size) ? 'size="20"' : 'size="' .$size. '"';
			$element['maxlength'] = empty($maxlength) ? 'maxlength="20"' : 'maxlength="' .$maxlength. '"';
			$element['html'] = '<input type="'. $element['type'] .'" name="'. $element['name'] .'" value="'. $element['value'] .'" '. $element['size'] .' '. $element['maxlength'] .'>';

			return $this->AddElement($element);
		}

		function AddTextArea($name,$value, $text)
		{
			$element['type'] = 'textarea';
			$element['name'] = $name;
			$element['value'] = empty($value) ? '' : $value;
			$element['text'] = $text;
			$element['html'] = '<'. $element['type'] .' name="'. $element['name'] .'">'. $element['value'] .'</'. $element['type'] .'>';

			return $this->AddElement($element);
		}

		function AddSubmitButton($name,$value)
		{
			$element['type'] = 'submit';
			$element['name'] = $name;
			$element['value']= $value;
			$element['html'] = '<input class="'. $element['type'] .'" type="'. $element['type'] .'" name="'. $element['name'] .'" value="'. $element['value'] .'">';

			return $this->AddElement($element);
		}

		function Display()
		{
			$this->buffer = '<form action="'. $this->action .'" method="'. $this->method .'" id="'. $this->id_css .'" class="'. $this->class_css .'"  '. $this->onsubmit .' >';
			$this->buffer .= '<dl>';
			$element = $this->GetNextElement();

			foreach($this->elements as $element)
			{
				switch($element['type'])
				{
					case 'textarea':
					case 'checkbox':
					case 'text':
						$this->buffer .= '<dt>
							<span id="caption_'. $element['name'] .'">'. $element['text'] .'</span>
						</dt>
						<dd>
							'. $element['html'] .'
						</dd>';
						break;
					case 'select':
						$this->buffer .= '<dt>
							<span id="caption_'. $element['name'] .'">'. $element['text'] .'</span>
						</dt>
						<dd>
							'. $element['html_start'] .'';

						foreach($element['values'] as $k => $v)
							$this->buffer .= $v .'';

						$this->buffer .= $element['html_end'] .'
						</dd>';
					case 'hidden':
					case 'submit':
						$this->buffer .= '<dt></dt>
						<dd>
							'. $element['html'] .'
						</dd>';
						break;
				}
			}

			$this->buffer .= '</dl></form>';

			return $this->buffer;
		}
	}