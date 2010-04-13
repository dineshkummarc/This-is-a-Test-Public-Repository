<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * SugarCRM is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2010 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/
require_once('modules/DynamicFields/templates/Fields/TemplateDatetimecombo.php');

function get_body(&$ss, $vardef){
	$defaultTime = '12:00pm';
	$hours = "12";
	$minitues = "00";
	$meridiem = "pm";
	$td = new TemplateDatetimecombo();
	$ss->assign('default_values', array_flip($td->dateStrings));
	$ss->assign('default_hours_values', array_flip($td->hoursStrings));
	$ss->assign('default_minutes_values', array_flip($td->minutesStrings));
	$ss->assign('default_meridiem_values', array_flip($td->meridiemStrings));
	if(isset($vardef['display_default']) && strstr($vardef['display_default'] , '&')){
		$dt = explode("&", $vardef['display_default']); //+1 day&06:00pm
		$date = $dt[0];
		$defaultTime = $dt[1];
		$hours = substr($defaultTime, 0, 2); 
		$minitues = substr($defaultTime, 3, 2);
		$meridiem = substr($defaultTime, 5, 2);
		$ss->assign('default_date', $date);
	}
	$ss->assign('default_hours', $hours);
	$ss->assign('default_minutes', $minitues);
	$ss->assign('default_meridiem', $meridiem);
	$ss->assign('defaultTime', $defaultTime);
	return $ss->fetch('modules/DynamicFields/templates/Fields/Forms/datetimecombo.tpl');
}

?>