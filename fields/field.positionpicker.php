<?php

	require_once(TOOLKIT . '/class.xsltprocess.php');

	Class fieldPositionpicker extends Field
    {

        protected $_driver;

        /**
         * The constructor
         * @param  $parent      The parent, provided by Symphony
         */
		public function __construct(&$parent)
        {
			parent::__construct($parent);
			$this->_name = __('Position Picker');
			$this->_required = true;
            // Compatibility for both Symphony 2.1.2 as 2.2:
            if(isset($this->_engine)) {
                $this->_driver = $this->_engine->ExtensionManager->create('position_picker');
            } else {
                $this->_driver = Symphony::ExtensionManager()->create('position_picker');
            }

			$this->set('required', 'no');
		}
		
		/**
         * The settings-panel on the blueprints-screen
         * @param  $wrapper     The wrapper, provided by Symphony
         * @param null $errors  Errors
         * @return void
         */
		public function displaySettingsPanel(&$wrapper, $errors = null)
        {
			parent::displaySettingsPanel($wrapper, $errors);
			
			// Show the sections:
			$sm = new SectionManager($this);
			$sections = $sm->fetch();
            $options = array();
            $options[] = array('0', false, ' ');
			foreach($sections as $section)
			{
				$name = $section->get('name');
				$id   = $section->get('id');
				$selected  = $id == $this->get('section_id');
				$options[] = array($id, $selected, $name);
			}
            $group = new XMLElement('div', null, array('class'=>'group'));
            $label = Widget::Label(__('Enter the URL of the image:'));
            $label->appendChild(Widget::Input('fields['.$this->get('sortorder').'][image_url]', $this->get('image_url')));
            if(isset($errors['image_url']))
            {
                $group->appendChild(Widget::wrapFormElementWithError($label, $errors['image_url']));
            } else {
                $group->appendChild($label);
            }

            $label = Widget::Label(__('Or select a section to dynamicly select an image from:'));
            $selectBox = Widget::Select('fields['.$this->get('sortorder').'][section_id]', $options);
            $label->appendChild($selectBox);
            if(isset($errors['section_id']))
            {
                $group->appendChild(Widget::wrapFormElementWithError($label, $errors['section_id']));
            } else {
                $group->appendChild($label);
            }


            $label = Widget::Label();
            $input = Widget::Input("fields[{$order}][unit]", 'yes', 'checkbox');
            if ($this->get('unit') == 'yes') $input->setAttribute('checked', 'checked');
            $label->setValue(__('%s Show results in percentage', array($input->generate())));
            $group->appendChild($label);


            $wrapper->appendChild($group);

			$this->appendRequiredCheckbox($wrapper);
			$this->appendShowColumnCheckbox($wrapper);
		}

        public function checkFields(Array &$errors, $checkForDuplicates = true)
        {
            parent::checkFields($errors, $checkForDuplicates);
            if($this->get('image_url') != '' && $this->get('section_id') != 0)
            {
                // It cannot be both!
                $errors['image_url']  = __('You cannot both have a URL and a section selected');
                $errors['section_id'] = __('You cannot both have a URL and a section selected');
            }
        }

        /**
         * Save the settings-panel in the blueprints-section
         * @return bool     True on success, false on failure
         */
		public function commit()
        {
			if(!parent::commit()) return false;
			
			$id = $this->get('id');			
			if($id === false) return false;
			
			$fields = array();
			$fields['field_id'] = $id;
			$fields['section_id'] = $this->get('section_id');
            $fields['image_url'] = $this->get('image_url');
            $fields['unit'] = $this->get('unit');
            if($fields['section_id'] == 0)
            {
                $fields['section_id'] = null;
            } else {
                $fields['image_url'] = null;
            }

			Symphony::Database()->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '$id' LIMIT 1");
			return Symphony::Database()->insert($fields, 'tbl_fields_' . $this->handle());
		}
		
        /**
         * The publish-panel on the entry editor:
         * @param  $wrapper                 The wrapper, provided by Symphony
         * @param null $data                The data
         * @param null $flagWithError       Should the error box be shown?
         * @param null $fieldnamePrefix
         * @param null $fieldnamePostfix
         * @return void
         */
		public function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL)
        {
			$label = Widget::Label($this->get('label'));
			if($this->get('required') != 'yes') $label->appendChild(new XMLElement('i', __('Optional')));
			
			// Get the entries from the section:
            if($this->get('section_id') != null)
            {
                $em = new EntryManager($this);
                $entries = $em->fetch(null, $this->get('section_id'));
                $options = array(
                    array(
                        0, false, __('-- Choose an image --')
                    )
                );
                $files = array();
                foreach($entries as $entry)
                {
                    $fields = $entry->getData();
                    $id   = $entry->get('id');
                    $name = $id;
                    foreach($fields as $field)
                    {
                        if(isset($field['value']))
                        {
                            $name = $field['value'];
                            break;
                        }
                    }
                    // Filename:
                    foreach($fields as $field)
                    {
                        if(isset($field['file']))
                        {
                            $files[$id] = $field['file'];
                            break;
                        }
                    }
                    if($data != null) {
                        $selected  = $id == $data['relation_id'];
                    } else {
                        $selected = false;
                    }
                    $options[] = array($id, $selected, $name);
                }
            } else {
                $files = array('url'=>$this->get('image_url'));
                $options = array(
                    array(
                        0, false, __('-- Choose an image --')
                    ),
                    array(
                        -1, true, 0
                    )
                );
            }

            $selectBox = Widget::Select('fields['.$this->get('element_name').'][relation_id]', $options);
            $label->appendChild($selectBox);

			$picker = new XMLElement('div', null, array('class'=>'position_picker'));
			$label->appendChild($picker);
			$vars = new XMLElement('div', null, array('class'=>'position_picker_vars'));
			foreach($files as $id => $val)
			{
				// Get image sizes:
				$image = $id == 'url' ? URL.$val : URL.'/workspace' . $val;
				list($width, $height) = getimagesize($image);
				$vars->appendChild(new XMLElement('var', $val.'*'.$width.'*'.$height, array('rel'=>$id)));
			}
			$label->appendChild($vars);
			$value = $data == null ? null : $data['xpos'].','.$data['ypos'];
			$label->appendChild(Widget::Input('fields['.$this->get('element_name').'][position]', (strlen($value) != 0 ? $value : NULL), 'hidden'));
			if($flagWithError != NULL) $wrapper->appendChild(Widget::wrapFormElementWithError($label, $flagWithError));
			else $wrapper->appendChild($label);
		}
		
        /**
         * Check post data
         * @param  $data            The data
         * @param  $message         The message
         * @param null $entry_id    The entry_id
         * @return int              The status
         */
		public function checkPostFieldData($data, &$message, $entry_id=NULL)
        {
			$message = NULL;
			
			if($this->get('required') == 'yes' && $data['relation_id'] == 0){
				$message = __("'%s' is a required field.", array($this->get('label')));
				return self::__MISSING_FIELDS__;
			}
			
			return self::__OK__;
		}
		
        /**
         * Store the information when an entry is created or edited:
         * @param  $data            The data
         * @param  $status          The status
         * @param bool $simulate    Simulate or not?
         * @param null $entry_id    The ID of the entry
         * @return array            The result
         */
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = null)
        {
			$status = self::__OK__;
			
			$coords = explode(',', $data['position']);
			
			if(count($coords) != 2)
			{
				$coords = array(0, 0);
			}
			
			$result = array(
				'relation_id' => $data['relation_id'],
				'xpos' => $coords[0],
				'ypos' => $coords[1]
			);

            if($data['relation_id'] == -1) {
                // Static image:
                $data['relation_id'] = null;
            }
            
			
			return $result;
		}
		
        /**
         * Add the XML element to the datasource output:
         * @param  $wrapper         The wrapper, provided by Symphony
         * @param  $data            The data
         * @param bool $encode      Should encoding be used?
         * @return void
         */
		public function appendFormattedElement(&$wrapper, $data, $encode=false)
        {
			if($this->get('section_id') != null)
            {
                $wrapper->appendChild(
                    new XMLElement(
                        $this->get('element_name'), null, array('relation-id' => $data['relation_id'], 'xpos' => $data['xpos'], 'ypos' => $data['ypos'])
                    )
                );
                // Also pass the ID to the driver so it can output it in the parameters:
                $this->_driver->addID($data['relation_id']);
            } else {
                // Static image:
                $wrapper->appendChild(
                    new XMLElement(
                        $this->get('element_name'), null, array('image-url' => $this->get('image_url'), 'xpos' => $data['xpos'], 'ypos' => $data['ypos'])
                    )
                );
            }
		}
		
        /**
         * The data to show in the table
         * @param  $data                    The data
         * @param null|XMLElement $link     The link
         * @return                          The value to show in the table
         */
		function prepareTableValue($data, XMLElement $link=NULL)
        {
            if($this->get('section_id') != null)
            {
                $em = new EntryManager($this);
                $related_item = $em->fetch($data['relation_id']);
                if($related_item != false)
                {
                    $fields = $related_item[0]->getData();
                    $info = $this->get();
                    $section = Symphony::Database()->fetchVar('handle', 0, 'SELECT `handle` FROM `tbl_sections` WHERE `id` = '.$info['section_id'].';');
                    $url = URL.'/symphony/publish/'.$section.'/edit/'.$related_item[0]->get('id').'/';
                    $name = 'Unknown entry';
                    foreach($fields as $field)
                    {
                        if(isset($field['value']))
                        {
                            $name = $field['value'];
                            break;
                        }
                    }

                    $value = '<a href="'.$url.'">'.$name.'</a> - <em>('.$data['xpos'].', '.$data['ypos'].')</em>';
                    return(trim($value));
                }
            } else {
                return $data['xpos'].', '.$data['ypos'];
            }
		}
		
        /**
         * Create the table for each field
         * @return bool
         */
		public function createTable()
        {
			return Symphony::Database()->query(
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
                    `id` int(11) unsigned NOT NULL auto_increment,
                    `entry_id` int(11) unsigned NOT NULL,
                    `relation_id` int(11) unsigned NULL,
                    `xpos` int(11) default NULL,
                    `ypos` int(11) default NULL,
                    PRIMARY KEY  (`id`),
                    KEY `entry_id` (`entry_id`),
                    KEY `relation_id` (`relation_id`)
				);"
			);
		}
	}

