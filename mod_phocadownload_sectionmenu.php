<?php
/*
 * @package Joomla 1.5
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @module Phoca - Phoca Module
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');// no direct access
if (!JComponentHelper::isEnabled('com_phocadownload', true)) {
	return JError::raiseError(JText::_('Phoca Download Error'), JText::_('Phoca Download is not installed on your system'));
}
//require_once( JPATH_BASE.DS.'components'.DS.'com_phocadownload'.DS.'helpers'.DS.'phocadownload.php' );
require_once( JPATH_BASE.DS.'components'.DS.'com_phocadownload'.DS.'helpers'.DS.'route.php' );
//require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocadownload'.DS.'helpers'.DS.'phocadownload.php' );

$user 		=& JFactory::getUser();
$aid 		= $user->get('aid', 0);	
$db 		=& JFactory::getDBO();
$menu 		=& JSite::getMenu();
$document	=& JFactory::getDocument();

// PARAMS 
$displayS 	= $params->get( 'display_sections', '' );
$hideS 		= $params->get( 'hide_sections', '' );

// SQL, QUERY
if (count($displayS) > 1) {
	JArrayHelper::toInteger($displayS);
	$displaySString	= implode(',', $displayS);
	$wheres[]	= ' s.id IN ( '.$displaySString.' ) ';
} else if ((int)$displayS > 0) {
	$wheres[]	= ' s.id IN ( '.$displayS.' ) ';
}

if (count($hideS) > 1) {
	JArrayHelper::toInteger($hideS);
	$hideSString	= implode(',', $hideS);
	$wheres[]	= ' s.id NOT IN ( '.$hideSString.' ) ';
} else if ((int)$hideS > 0) {
	$wheres[]	= ' s.id NOT IN ( '.$hideS.' ) ';
}

$wheres[] = " s.published = 1";
$wheres[] = " cc.published = 1";
$wheres[] = " s.id = cc.section";
	
if ($aid !== null) {
	$wheres[] = "s.access <= " . (int) $aid;
}

$query =  " SELECT s.id, s.title, s.alias, COUNT(cc.id) AS numcat, '' AS categories"
		. " FROM #__phocadownload_sections AS s, #__phocadownload_categories AS cc"
		. " WHERE " . implode( " AND ", $wheres )
		. " GROUP BY s.id"
		. " ORDER BY s.ordering";
		
$db->setQuery( $query );
$sections = $db->loadObjectList();

// DISPLAY
$output = '<div class="phoca-dl-sections-box-module">';
if (!empty($sections)) {
	foreach ($sections as $value) {
		$output .= '<p class="sections">';
		$output .= '<a href="'. JRoute::_(PhocaDownloadHelperRoute::getSectionRoute($value->id,$value->alias)).'">'. $value->title.'</a>';
		$output .= ' <small>('.$value->numcat.')</small></p>';
	}	
}
$output .= '</div>';

require(JModuleHelper::getLayoutPath('mod_phocadownload_sectionmenu'));
?>