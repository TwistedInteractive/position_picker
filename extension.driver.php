<?php
	
	class extension_position_picker extends Extension {

        private $_ids;

        /**
         * The constructor
         * @param  $context     The context provided by Symphony
         */
        public function __construct($context) {
            parent::__construct($context);
            $this->_ids = array();
        }
        
		public function about() {
			return array(
				'name'			=> 'Field: Position Picker',
				'version'		=> '1.1',
				'release-date'	=> '2011-06-05',
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
            $this->_Parent->Database->query("DROP TABLE `tbl_fields_positionpicker`");
		}
		
        /**
         * Installation script
         * @return void
         */
		public function install() {
			$this->_Parent->Database->query("
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
         * @return array        The delegates
         */
		public function getSubscribedDelegates() {
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'addScriptToHead'
				),
                array(
                    'page' => '/frontend/',
                    'delegate' => 'FrontendParamsPostResolve',
                    'callback' => 'addParameters'
                )
			);
		}
		
        /**
         * Add the id's of all picker instances to the frontend, to provide a way to retreive the related entries.
         * @param  $context     The context, provided by Symphony
         * @return void
         */
        public function addParameters($context)
        {
            // Add the used ID's as a parameter so they can be used by other data sources:
            $this->_ids = array_unique($this->_ids);
            sort($this->_ids);
            $context['params']['position-picker-ids'] = implode(', ', $this->_ids);
        }

        /**
         * Adds an ID to the arry of ID's that is used for the parameter output
         * @param  $id          The ID
         * @return void
         */
        public function addID($id)
        {
            $this->_ids[] = $id;
        }
		
		
        /**
         * Add some javascript and a stylesheet to the head of the page to provide the picker-functionality.
         * @param  $context     The context, provided by Symphony
         * @return void
         */
		public function addScriptToHead($context) {
			Administration::instance()->Page->addScriptToHead(URL.'/extensions/position_picker/assets/position_picker.js', 301, true);
			Administration::instance()->Page->addStylesheetToHead(URL.'/extensions/position_picker/assets/position_picker.css', 'screen', 302);
		}

	}
	
?>
