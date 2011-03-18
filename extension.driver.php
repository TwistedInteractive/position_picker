<?php

	Class extension_position_picker extends Extension
    {

        private $_ids;

        /**
         * The constructor
         * @param  $context     The context provided by Symphony
         */
        public function __construct($context)
        {
            parent::__construct($context);
            $this->_ids = array();
        }

		/**
         * Provide a little information about this extension
         * @return array        The information
         */
        public function about()
        {
			return array('name' => 'Field: Position Picker',
				'version' => '1.0',
				'release-date' => '2011-03-18',
				'author' => array('name' => 'Giel Berkers',
					'website' => 'http://www.gielberkers.com',
					'email' => 'info@gielberkers.com')
			);
		}

        /**
         * Get the subscribed delegates.
         * @return array        The delegates
         */
		public function getSubscribedDelegates()
		{
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
         * Add some javascript and a stylesheet to the head of the page to provide the picker-functionality.
         * @param  $context     The context, provided by Symphony
         * @return void
         */
		public function addScriptToHead($context)
		{
			Administration::instance()->Page->addScriptToHead(URL.'/extensions/position_picker/assets/position_picker.js', 301, true);
			Administration::instance()->Page->addStylesheetToHead(URL.'/extensions/position_picker/assets/position_picker.css', 'screen', 302);
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
         * Installation script
         * @return void
         */
        public function install()
        {
            Symphony::Database()->query("CREATE TABLE `tbl_fields_positionpicker` (
                `id` int(11) unsigned NOT NULL auto_increment ,
                `field_id` int(11) unsigned NOT NULL ,
                `section_id` int(11) unsigned NULL ,
                `image_url` TINYTEXT NULL ,
                PRIMARY KEY  (`id`),
                UNIQUE KEY `field_id` (`field_id`)
            )");
        }

        /**
         * Uninstallation script
         * @return void
         */
        public function uninstall()
        {
            Symphony::Database()->query("DROP TABLE `tbl_fields_positionpicker`");
        }

	}

