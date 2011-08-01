<?php

	class extension_position_picker extends Extension {

		/**
		 * About the author
		 */
		public function about() {
			return array(
				'name'			=> 'Field: Position Picker',
				'version'		=> '1.1',
				'release-date'	=> '2011-07-21',
				'author'		=> array(
					'name'			=> 'Giel Berkers',
					'website'		=> 'http://www.gielberkers.com',
					'email'			=> 'info@gielberkers.com'
				),
				'description' => 'A pixel/percentage picker.'
			);
		}

		/**
		 * Uninstallation script
		 * @return void
		 */
		public function uninstall() {
			Symphony::Database()->query("DROP TABLE `tbl_fields_positionpicker`");
		}

		/**
		 * Installation script
		 * @return void
		 */
		public function install() {
			Symphony::Database()->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_positionpicker` (
					`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`field_id` INT(11) UNSIGNED NOT NULL,
					`section_id` int(11) unsigned NULL ,
					`image_url` TINYTEXT NULL ,
					`unit` ENUM('pixels','percentage') NULL ,
					PRIMARY KEY (`id`),
					KEY `field_id` (`field_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			");

			return true;
		}

		/**
		 * Get the subscribed delegates.
		 * @return array		The delegates
		 */
		public function getSubscribedDelegates() {
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'addScriptToHead'
				)
			);
		}

		/**
		 * Add some javascript and a stylesheet to the head of the page to provide the picker-functionality.
		 * @param  $context		The context, provided by Symphony
		 * @return void
		 */
		public function addScriptToHead($context) {
			Administration::instance()->Page->addScriptToHead(URL.'/extensions/position_picker/assets/position_picker.js', 301, true);
			Administration::instance()->Page->addStylesheetToHead(URL.'/extensions/position_picker/assets/position_picker.css', 'screen', 302);
		}

	}

