 <?php

/**
 * @version     1.3.3
 * @package     com_metro_programme
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Jose <jose@aviationmedia.aero> - http://www.aviationmedia.aero
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
require_once JPATH_COMPONENT . '/helpers/metro_programme.php';
/**
 * Methods supporting a list of Metro_programme records.
 */
class Metro_programmeModelProgrammes extends JModelList {

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                                'id', 'a.id',
                'ordering', 'a.ordering',
                'state', 'a.state',
                'created_by', 'a.created_by',
                'title', 'a.title',
                'style', 'a.style',
                'day', 'a.day',
                'subtitle', 'a.subtitle',
                'sessiontime', 'a.sessiontime',
                'information', 'a.information',
                'bullet_points', 'a.bullet_points',
                'speakers', 'a.speakers',
                'isairportspeaker', 'a.isairportspeaker',
                'sqlspeakers', 'a.sqlspeakers',
                'speakername1', 'a.speakername1',
                'speakername2', 'a.speakername2',
                'speakername3', 'a.speakername3',
                'speakername4', 'a.speakername4',
                'speakername5', 'a.speakername5',
                'speakername6', 'a.speakername6',
                'refreshmentlogo', 'a.refreshmentlogo',
                'refreshmentlink', 'a.refreshmentlink',
                'refreshmentname', 'a.refreshmentname',
            );
        }
        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState($ordering = null, $direction = null) {

        // Initialise variables.
        $app = JFactory::getApplication();

        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
        $this->setState('list.limit', $limit);

        $limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);
        $this->setState('list.start', $limitstart);

        
		if(empty($ordering)) {
			$ordering = 'a.ordering';
		}

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery() {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
                $this->getState(
                        'list.select', 'DISTINCT a.*'
                )
        );

        $query->from('`#__metro_programme` AS a');

        
    // Join over the users for the checked out user.
    $query->select('uc.name AS editor');
    $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
    
		// Join over the created by field 'created_by'
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');
		// Join over the category 'day'
		$query->select('day.title AS day_title');
		$query->join('LEFT', '#__categories AS day ON day.id = a.day');
        

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                
            }
        }

        

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }
        $query->where('`state`= 1');
		$query->order('a.ordering ASC');
        return $query;
    }

    public function getItems() {

        $items = parent::getItems();
        foreach($items as $item){
	
					$item->style = JText::_('COM_METRO_PROGRAMME_PROGRAMMES_STYLE_OPTION_' . strtoupper($item->style));

			if ( isset($item->day) ) {

				// Get the title of that particular template
					$title = Metro_programmeFrontendHelper::getCategoryNameByCategoryId($item->day);

					// Finally replace the data object with proper information
					$item->day = !empty($title) ? $title : $item->day;
				}
				
			if (isset($item->sqlspeakers) && $item->sqlspeakers != '') {
				if(is_object($item->sqlspeakers)){
					$item->sqlspeakers = JArrayHelper::fromObject($item->sqlspeakers);
				}
				$values = (is_array($item->sqlspeakers)) ? $item->sqlspeakers : explode(',',$item->sqlspeakers);

				$textValue = array();
				foreach ($values as $value){
					$db = JFactory::getDbo();
					$query = "SELECT * FROM `lxshu_speaker` where `state`=1 and name LIKE '" . $value . "'";
					$db->setQuery($query);
					$results = $db->loadObject();
					if ($results) {
						$textValue[] = $results->name;
					}
				}

			$item->sqlspeakers = !empty($textValue) ? implode(', ', $textValue) : $item->sqlspeakers;

			}
}
        return $items;
    }
    public function getSpeakers() {
    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);
    
    	$query
    		->select($db->quoteName(array('id','salutation','name','surname','company','job_title')))
    		->from('#__speaker')
    		->order('`id` ASC');
    	$db->setQuery($query);
    	$results = $db->loadAssocList();
    	return $results;
    }

}
