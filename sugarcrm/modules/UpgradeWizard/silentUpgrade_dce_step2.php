<?php

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

//////////////////////////////////////////////////////////////////////////////////////////
//// This is a stand alone file that can be run from the command prompt for upgrading a
//// Sugar Instance. Three parameters are required to be defined in order to execute this file.
//// php.exe -f silentUpgrade.php [Path to Upgrade Package zip] [Path to Log file] [Path to Instance]
//// See below the Usage for more details.
/////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
////	UTILITIES THAT MUST BE LOCAL :(
function prepSystemForUpgradeSilent() {
	global $subdirs;
	global $cwd;
	global $sugar_config;

	// make sure dirs exist
	foreach($subdirs as $subdir) {
	    mkdir_recursive(clean_path("{$cwd}/{$sugar_config['upload_dir']}upgrades/{$subdir}"));
	}
}

//local function for clearing cache
function clearCacheSU($thedir, $extension) {
	if ($current = @opendir($thedir)) {
		while (false !== ($children = readdir($current))) {
			if ($children != "." && $children != "..") {
				if (is_dir($thedir . "/" . $children)) {
					clearCacheSU($thedir . "/" . $children, $extension);
				}
				elseif (is_file($thedir . "/" . $children) && substr_count($children, $extension)) {
					unlink($thedir . "/" . $children);
				}
			}
		}
	}
 }
 //Bug 24890, 24892. default_permissions not written to config.php. Following function checks and if
 //no found then adds default_permissions to the config file.
 function checkConfigForPermissions(){
     if(file_exists(getcwd().'/config.php')){
         require(getcwd().'/config.php');
     }
     global $sugar_config;
     if(!isset($sugar_config['default_permissions'])){
             $sugar_config['default_permissions'] = array (
                     'dir_mode' => 02770,
                     'file_mode' => 0660,
                     'user' => '',
                     'group' => '',
             );
         ksort($sugar_config);
         if(is_writable('config.php') && write_array_to_file("sugar_config", $sugar_config,'config.php')) {
        	//writing to the file
 		}
     }
}
function checkLoggerSettings(){
	if(file_exists(getcwd().'/config.php')){
         require(getcwd().'/config.php');
     }
    global $sugar_config;
	if(!isset($sugar_config['logger'])){
	    $sugar_config['logger'] =array (
			'level'=>'fatal',
		    'file' =>
		     array (
		      'ext' => '.log',
		      'name' => 'sugarcrm',
		      'dateFormat' => '%c',
		      'maxSize' => '10MB',
		      'maxLogs' => 10,
		      'suffix' => '%m_%Y',
		    ),
		  );
		 ksort($sugar_config);
         if(is_writable('config.php') && write_array_to_file("sugar_config", $sugar_config,'config.php')) {
        	//writing to the file
 		}
	 }
}
 
function checkResourceSettings(){
	if(file_exists(getcwd().'/config.php')){
         require(getcwd().'/config.php');
     }
    global $sugar_config;
	if(!isset($sugar_config['resource_management'])){
	  $sugar_config['resource_management'] =
		  array (
		    'special_query_limit' => 50000,
		    'special_query_modules' =>
		    array (
		      0 => 'Reports',
		      1 => 'Export',
		      2 => 'Import',
		      3 => 'Administration',
		      4 => 'Sync',
		    ),
		    'default_limit' => 1000,
		  );
		 ksort($sugar_config);
         if(is_writable('config.php') && write_array_to_file("sugar_config", $sugar_config,'config.php')) {
        	//writing to the file
 		}
	}
}


//rebuild all relationships...
function rebuildRelations($pre_path = ''){
	$_REQUEST['silent'] = true;
	include($pre_path.'modules/Administration/RebuildRelationship.php');
	 $_REQUEST['upgradeWizard'] = true;
	 include($pre_path.'modules/ACL/install_actions.php');
}

function createMissingRels(){
	$relForObjects = array('leads'=>'Leads','campaigns'=>'Campaigns','prospects'=>'Prospects');
	foreach($relForObjects as $relObjName=>$relModName){
		//assigned_user
		$guid = create_guid();
		$query = "SELECT id FROM relationships WHERE relationship_name = '{$relObjName}_assigned_user'";
		$result= $GLOBALS['db']->query($query, true);
		$a = null;
		$a = $GLOBALS['db']->fetchByAssoc($result);
		if($GLOBALS['db']->checkError()){
			//log this
		}
		if(!isset($a['id']) && empty($a['id']) ){
			$qRel = "INSERT INTO relationships (id,relationship_name, lhs_module, lhs_table, lhs_key, rhs_module, rhs_table, rhs_key, join_table, join_key_lhs, join_key_rhs, relationship_type, relationship_role_column, relationship_role_column_value, reverse, deleted)
						VALUES ('{$guid}', '{$relObjName}_assigned_user','Users','users','id','{$relModName}','{$relObjName}','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,'0','0')";
			$GLOBALS['db']->query($qRel);
			if($GLOBALS['db']->checkError()){
				//log this
			}
		}
		//modified_user
		$guid = create_guid();
		$query = "SELECT id FROM relationships WHERE relationship_name = '{$relObjName}_modified_user'";
		$result= $GLOBALS['db']->query($query, true);
		if($GLOBALS['db']->checkError()){
			//log this
		}
		$a = null;
		$a = $GLOBALS['db']->fetchByAssoc($result);
		if(!isset($a['id']) && empty($a['id']) ){
			$qRel = "INSERT INTO relationships (id,relationship_name, lhs_module, lhs_table, lhs_key, rhs_module, rhs_table, rhs_key, join_table, join_key_lhs, join_key_rhs, relationship_type, relationship_role_column, relationship_role_column_value, reverse, deleted)
						VALUES ('{$guid}', '{$relObjName}_modified_user','Users','users','id','{$relModName}','{$relObjName}','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,'0','0')";
			$GLOBALS['db']->query($qRel);
			if($GLOBALS['db']->checkError()){
				//log this
			}
		}
		//created_by
		$guid = create_guid();
		$query = "SELECT id FROM relationships WHERE relationship_name = '{$relObjName}_created_by'";
		$result= $GLOBALS['db']->query($query, true);
		$a = null;
		$a = $GLOBALS['db']->fetchByAssoc($result);
    	if(!isset($a['id']) && empty($a['id']) ){
			$qRel = "INSERT INTO relationships (id,relationship_name, lhs_module, lhs_table, lhs_key, rhs_module, rhs_table, rhs_key, join_table, join_key_lhs, join_key_rhs, relationship_type, relationship_role_column, relationship_role_column_value, reverse, deleted)
						VALUES ('{$guid}', '{$relObjName}_created_by','Users','users','id','{$relModName}','{$relObjName}','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,'0','0')";
			$GLOBALS['db']->query($qRel);
			if($GLOBALS['db']->checkError()){
				//log this
			}
    	}
	}
	//Also add tracker perf relationship
}


/**
 * This function will merge password default settings into config file
 * @param   $sugar_config
 * @param   $sugar_version
 * @return  bool true if successful
 */
function merge_passwordsetting($sugar_config, $sugar_version) {	
    $passwordsetting_defaults = array(
    'passwordsetting' => array (
        'SystemGeneratedPasswordON' => '',
        'generatepasswordtmpl' => '',
        'lostpasswordtmpl' => '',
        'forgotpasswordON' => false,
        'linkexpiration' => '1',
        'linkexpirationtime' => '30',
        'linkexpirationtype' => '1',
        'systexpiration' => '0',
        'systexpirationtime' => '',
        'systexpirationtype' => '0',
        'systexpirationlogin' => '',
        ) ,
    );    
   
        
    $sugar_config = sugarArrayMerge($passwordsetting_defaults, $sugar_config );

    // need to override version with default no matter what
    $sugar_config['sugar_version'] = $sugar_version;

    ksort( $sugar_config );

    if( write_array_to_file( "sugar_config", $sugar_config, "config.php" ) ){
        return true;
    }
    else {
        return false;
    }
}

function addDefaultModuleRoles($defaultRoles = array()) {
	foreach($defaultRoles as $roleName=>$role){
        foreach($role as $category=>$actions){
            foreach($actions as $name=>$access_override){
                    $query = "SELECT * FROM acl_actions WHERE name='$name' AND category = '$category' AND acltype='$roleName' AND deleted=0 ";
					$result = $GLOBALS['db']->query($query);
					//only add if an action with that name and category don't exist
					$row=$GLOBALS['db']->fetchByAssoc($result);
					if ($row == null) {
	                	$guid = create_guid();
	                	$currdate = gmdate($GLOBALS['timedate']->get_db_date_time_format());
	                	$query= "INSERT INTO acl_actions (id,date_entered,date_modified,modified_user_id,name,category,acltype,aclaccess,deleted ) VALUES ('$guid','$currdate','$currdate','1','$name','$category','$roleName','$access_override','0')";
						$GLOBALS['db']->query($query);
						if($GLOBALS['db']->checkError()){
							//log this
						}
	                }
            }
        }
	}
}

function verifyArguments($argv,$usage_dce,$usage_regular){
    $upgradeType = '';
    $cwd = getcwd(); // default to current, assumed to be in a valid SugarCRM root dir.
    if(isset($argv[3])) {
        if(is_dir($argv[3])) {
            $cwd = $argv[3];
            chdir($cwd);
        } else {
            echo "*******************************************************************************\n";
            echo "*** ERROR: 3rd parameter must be a valid directory.  Tried to cd to [ {$argv[3]} ].\n";
            die();
        }
    }

    //check if this is an instance
    if(is_file("{$cwd}/ini_setup.php")){
        // this is an instance
        $upgradeType = constant('DCE_INSTANCE');
        //now that this is dce instance we want to make sure that there are
        // 7 arguments
        if(count($argv) < 7) {
            echo "*******************************************************************************\n";
            echo "*** ERROR: Missing required parameters.  Received ".count($argv)." argument(s), require 7.\n";
            echo $usage_dce;
            echo "FAILURE\n";
            die();
        }
        // this is an instance
        if(!is_dir($argv[1])) { // valid directory . template path?
            echo "*******************************************************************************\n";
            echo "*** ERROR: First argument must be a full path to the template. Got [ {$argv[1]} ].\n";
            echo $usage_dce;
            echo "FAILURE\n";
            die();
        }
    }
    else if(is_file("{$cwd}/include/entryPoint.php")) {
        //this should be a regular sugar install
        $upgradeType = constant('SUGARCRM_INSTALL');
        //check if this is a valid zip file
        if(!is_file($argv[1])) { // valid zip?
            echo "*******************************************************************************\n";
            echo "*** ERROR: First argument must be a full path to the patch file. Got [ {$argv[1]} ].\n";
            echo $usage_regular;
            echo "FAILURE\n";
            die();
        }
        if(count($argv) < 5) {
            echo "*******************************************************************************\n";
            echo "*** ERROR: Missing required parameters.  Received ".count($argv)." argument(s), require 5.\n";
            echo $usage_regular;
            echo "FAILURE\n";
            die();
        }
    }
    else {
        //this should be a regular sugar install
        echo "*******************************************************************************\n";
        echo "*** ERROR: Tried to execute in a non-SugarCRM root directory.\n";
        die();      
    }

    if(isset($argv[7]) && file_exists($argv[7].'SugarTemplateUtilties.php')){
        require_once($argv[7].'SugarTemplateUtilties.php');
    }
    
    return $upgradeType;
}

function upgradeDCEFiles($argv,$instanceUpgradePath){
	//copy and update following files from upgrade package
	$upgradeTheseFiles = array('cron.php','download.php','index.php','install.php','soap.php','sugar_version.php','vcal_server.php');
	foreach($upgradeTheseFiles as $file){
		$srcFile = clean_path("{$instanceUpgradePath}/$file");
		$destFile = clean_path("{$argv[3]}/$file");
		if(file_exists($srcFile)){
			if(!is_dir(dirname($destFile))) {
				mkdir_recursive(dirname($destFile)); // make sure the directory exists
			}
			copy_recursive($srcFile,$destFile);
			$_GET['TEMPLATE_PATH'] = $destFile;
			$_GET['CONVERT_FILE_ONLY'] = true;
			if(!class_exists('TemplateConverter')){
				include($argv[7].'templateConverter.php');
			}else{
				TemplateConverter::convertFile($_GET['TEMPLATE_PATH']);
			}


		}
	}
}



function threeWayMerge(){
	//using threeway merge apis
}
////	END UTILITIES THAT MUST BE LOCAL :(
///////////////////////////////////////////////////////////////////////////////


// only run from command line
if(isset($_SERVER['HTTP_USER_AGENT'])) {
	die('This utility may only be run from the command line or command prompt.');
}
//Clean_string cleans out any file  passed in as a parameter
$_SERVER['PHP_SELF'] = 'silentUpgrade.php';


///////////////////////////////////////////////////////////////////////////////
////	USAGE
$usage_dce =<<<eoq1
Usage: php.exe -f silentUpgrade.php [upgradeZipFile] [logFile] [pathToSugarInstance]

On Command Prompt Change directory to where silentUpgrade.php resides. Then type path to
php.exe followed by -f silentUpgrade.php and the arguments.

Example:
    [path-to-PHP/]php.exe -f silentUpgrade.php [path-to-upgrade-package/]SugarEnt-Upgrade-4.5.1-to-5.0.0b.zip [path-to-log-file/]silentupgrade.log  [path-to-sugar-instance/]Sugar451e
                             [Old Template path] [skipdbupgrade] [exitOrContinue]

Arguments:
    New Template Path or Upgrade Package : Upgrade package name. Template2 (upgrade to)location.
    silentupgrade.log                    : Silent Upgarde log file.
    Sugar451e/DCE                        : Sugar or DCE Instance instance being upgraded.
    Old Template path                    : Template1 (upgrade from) Instance is being upgraded.
    skipDBupgrade                        : If set to Yes then silentupgrade will only upgrade files. Default is No.
    exitOnConflicts                      : If set to No and conflicts are found then Upgrade continues. Default Yes.
    pathToDCEClient                      : This is path to to DCEClient directory

eoq1;

$usage_regular =<<<eoq2
Usage: php.exe -f silentUpgrade.php [upgradeZipFile] [logFile] [pathToSugarInstance] [admin-user]

On Command Prompt Change directory to where silentUpgrade.php resides. Then type path to
php.exe followed by -f silentUpgrade.php and the arguments.

Example:
    [path-to-PHP/]php.exe -f silentUpgrade.php [path-to-upgrade-package/]SugarEnt-Upgrade-5.2.0-to-5.5.0.zip [path-to-log-file/]silentupgrade.log  [path-to-sugar-instance/] admin

Arguments:
    upgradeZipFile                       : Upgrade package file.
    logFile                              : Silent Upgarde log file.
    pathToSugarInstance                  : Sugar Instance instance being upgraded.
    admin-user                           : admin user performing the upgrade
eoq2;
////	END USAGE
///////////////////////////////////////////////////////////////////////////////



///////////////////////////////////////////////////////////////////////////////
////	STANDARD REQUIRED SUGAR INCLUDES AND PRESETS
if(!defined('sugarEntry')) define('sugarEntry', true);

$_SESSION = array();
$_SESSION['schema_change'] = 'sugar'; // we force-run all SQL
$_SESSION['silent_upgrade'] = true;
$_SESSION['step'] = 'silent'; // flag to NOT try redirect to 4.5.x upgrade wizard

$_REQUEST = array();
$_REQUEST['addTaskReminder'] = 'remind';


define('SUGARCRM_INSTALL', 'SugarCRM_Install');
define('DCE_INSTANCE', 'DCE_Instance');

global $cwd;
$cwd = getcwd(); // default to current, assumed to be in a valid SugarCRM root dir.

$upgradeType = verifyArguments($argv,$usage_dce,$usage_regular);

///////////////////////////////////////////////////////////////////////////////
//////  Verify that all the arguments are appropriately placed////////////////

///////////////////////////////////////////////////////////////////////////////
////	PREP LOCALLY USED PASSED-IN VARS & CONSTANTS
//$GLOBALS['log']	= LoggerManager::getLogger('SugarCRM');
//require_once('/var/www/html/eddy/sugarnode/SugarTemplateUtilities.php');

$path			= $argv[2]; // custom log file, if blank will use ./upgradeWizard.log
//$db				= &DBManagerFactory::getInstance();  //<---------


//$UWstrings		= return_module_language('en_us', 'UpgradeWizard');
//$adminStrings	= return_module_language('en_us', 'Administration');
//$mod_strings	= array_merge($adminStrings, $UWstrings);
$subdirs		= array('full', 'langpack', 'module', 'patch', 'theme', 'temp');

//$_REQUEST['zip_from_dir'] = $zip_from_dir;

define('SUGARCRM_PRE_INSTALL_FILE', 'scripts/pre_install.php');
define('SUGARCRM_POST_INSTALL_FILE', 'scripts/post_install.php');
define('SUGARCRM_PRE_UNINSTALL_FILE', 'scripts/pre_uninstall.php');
define('SUGARCRM_POST_UNINSTALL_FILE', 'scripts/post_uninstall.php');



echo "\n";
echo "********************************************************************\n";
echo "***************This Upgrade process may take sometime***************\n";
echo "********************************************************************\n";
echo "\n";

global $sugar_config;
$isDCEInstance = false;
$errors = array();


if($upgradeType == constant('DCE_INSTANCE')){
   	//$instanceUpgradePath = "{$argv[1]}/DCEUpgrade/{$zip_from_dir}";
   	//$instanceUpgradePath = "{$argv[1]}";
	include ("ini_setup.php");

	//get new template path for use in later processing
    $dceupgrade_pos = strpos($argv[1], '/DCEUpgrade');
    $newtemplate_path = substr($argv[1], 0, $dceupgrade_pos);
	
	require("{$argv[4]}/sugar_version.php");
	global $sugar_version;
	$pre_550 = false;
	if($sugar_version < '5.5.0'){
		//set variable to use later in script
		$pre_550 = true;
		//require classes if they do not exist, as these were not in pre 550 entrypoint.php and need to be loaded first
        if(!class_exists('VardefManager')){
                require_once("{$argv[4]}/include/SugarObjects/VardefManager.php");
        }
        if (!class_exists('Sugar_Smarty')){
                require_once("{$argv[4]}/include/Sugar_Smarty.php");
        }
        if (!class_exists('LanguageManager')){
			require_once("{$argv[4]}/include/SugarObjects/LanguageManager.php");
		}
        
	}

	//load up entrypoint from original template
   	require_once("{$argv[4]}/include/entryPoint.php");

   	//load up missing values from pre 550 entrypoint.
	if($sugar_version < '5.5.0'){
		//require classes if they do not exist, as these were not in pre-550 entrypoint.php and need to be loaded after
	    if (! class_exists('UserPreference')){
	            require_once("{$newtemplate_path}/modules/UserPreferences/UserPreference.php");
	    }
	    if (! function_exists('unformat_number')){
	            require_once("{$newtemplate_path}/modules/Currencies/Currency.php");
	    }
	}   	

	require_once("{$argv[4]}/include/utils/zip_utils.php");
	require_once("{$argv[4]}/modules/Administration/UpgradeHistory.php");

	// We need to run the silent upgrade as the admin user, 
	global $current_user;
	$current_user = new User();
	$current_user->retrieve('1');
	
	
	//This is DCE instance
      global $sugar_config;
      global $sugar_version;
//    require_once("{$cwd}/sugar_version.php"); //provides instance version, flavor etc..
     //provides instance version, flavor etc..
    $isDCEInstance = true;
    $configOptions = $sugar_config['dbconfig'];

	$GLOBALS['log']	= LoggerManager::getLogger('SugarCRM');
	$db				= &DBManagerFactory::getInstance();
       		///////////////////////////////////////////////////////////////////////////////
	////	MAKE SURE PATCH IS COMPATIBLE

	if(is_file("{$argv[1]}/manifest.php")) {
		// provides $manifest array
		include("{$argv[1]}/manifest.php");
	}
	//If Instance then the files will be accessed from Template/DCEUpgrade folder
	$zip_from_dir = '';
    if( isset( $manifest['copy_files']['from_dir'] ) && $manifest['copy_files']['from_dir'] != "" ){
	    $zip_from_dir   = $manifest['copy_files']['from_dir'];
	}
	$instanceUpgradePath = "{$argv[1]}/{$zip_from_dir}";
	global $instancePath;
	$instancePath = $instanceUpgradePath;
	$_SESSION['instancePath'] = $instancePath;
	if(file_exists("{$instanceUpgradePath}/modules/UpgradeWizard/uw_utils.php")){
		require_once("{$instanceUpgradePath}/modules/UpgradeWizard/uw_utils.php");
	}
	else{
		require_once("{$argv[4]}/modules/UpgradeWizard/uw_utils.php");
	}

    $ce_to_pro_ent = isset($manifest['name']) && ($manifest['name'] == 'SugarCE to SugarPro' || $manifest['name'] == 'SugarCE to SugarEnt');
	$_SESSION['upgrade_from_flavor'] = $manifest['name'];
	
    //check for db upgrade
    //check exit on conflicts
    $skipDBUpgrade = 'no'; //default
    if($argv[6] != null && !empty($argv[6])){
    	if(strtolower($argv[6]) == 'yes'){
    	  $skipDBUpgrade = 'yes'; //override
    	}
    }
    global $unzip_dir;
    $unzip_dir = $argv[1];
    $_SESSION['unzip_dir'] = $unzip_dir;
    global $path;
    $path = $argv[2];

    if($skipDBUpgrade == 'no'){
    	//upgrade the db
	    	///////////////////////////////////////////////////////////////////////////////
		////	HANDLE PREINSTALL SCRIPTS
		//if(empty($errors)) {
			$file = "{$argv[1]}/".constant('SUGARCRM_PRE_INSTALL_FILE');
			if(is_file($file)) {
				include($file);
				logThis('Running pre_install()...', $path);
				pre_install();

				//if this is a pre 550 install, and not a flavor conversion from ce, then run
				//extra cleanup on aclroles table for tracker related records
				if($pre_550 && !$ce_to_pro_ent){
			        _logThis("Begin remove ACL query for Trackers", $path);
					$query = " update  acl_actions set deleted = 1 where category = 'Trackers' and deleted = 0 ";
			        $result = $GLOBALS['db']->query($query);
			        _logThis("executed query:  $query", $path);
				}
				logThis('pre_install() done.', $path);
			}
		//}


        //run the 3-way merge
        if(file_exists($newtemplate_path.'/modules/UpgradeWizard/SugarMerge/SugarMerge.php')){
            logThis('Running 3 way merge()...', $path);
            require_once($newtemplate_path.'/modules/UpgradeWizard/SugarMerge/SugarMerge.php');
            $merger = new SugarMerge($instanceUpgradePath, $argv[4].'/', $argv[3].'/custom');
            $merger->mergeAll();
            logThis('Finished 3 way merge()...', $path);
        }        
    		///////////////////////////////////////////////////////////////////////////////
	////	HANDLE POSTINSTALL SCRIPTS
		//if(empty($errors)) {
		logThis('Starting post_install()...', $path);
		$file = "{$argv[1]}/".constant('SUGARCRM_POST_INSTALL_FILE');
		if(is_file($file)) {
			include($file);
			post_install();
		}
			//clean vardefs
		logThis('Performing UWrebuild()...', $path);
			UWrebuild();
		logThis('UWrebuild() done.', $path);

		logThis('begin check default permissions .', $path);
	    	checkConfigForPermissions();
	    logThis('end check default permissions .', $path);

        logThis('begin check logger settings .', $path);
            checkLoggerSettings();
        logThis('begin check logger settings .', $path);
	
        //check to see if there are any new files that need to be added to systems tab
        //retrieve old modules list
        logThis('check to see if new modules exist',$path);
        $oldModuleList = array();
        $newModuleList = array();
        include($argv[4].'/include/modules.php');
        $oldModuleList = $moduleList;
        include($newtemplate_path.'/include/modules.php');
        $newModuleList = $moduleList;

        //include tab controller 
        require_once($newtemplate_path.'/modules/MySettings/TabController.php');
        $newTB = new TabController();
        
        //make sure new modules list has a key we can reference directly
        $newModuleList = $newTB->get_key_array($newModuleList);
        $oldModuleList = $newTB->get_key_array($oldModuleList);
        
        //iterate through list and remove commonalities to get new modules
        foreach ($newModuleList as $remove_mod){
            if(in_array($remove_mod, $oldModuleList)){
                unset($newModuleList[$remove_mod]);
            }   
        }
        //new modules list now has left over modules which are new to this install, so lets add them to the system tabs
        logThis('new modules to add are '.var_export($newModuleList,true),$path);
        
        //grab the existing system tabs
        $tabs = $newTB->get_system_tabs();

        //add the new tabs to the array
        foreach($newModuleList as $nm ){
          $tabs[$nm] = $nm;
        }

        //now assign the modules to system tabs
        $newTB->set_system_tabs($tabs);
        logThis('module tabs updated',$path);
        
	    

	    if($ce_to_pro_ent){
	    	//add the global team if it does not exist
			$globalteam = new Team();
			$globalteam->retrieve('1');
			include($newtemplate_path.'/modules/Administration/language/en_us.lang.php');
			if(isset($globalteam->name)){
				
			   echo 'Global '.$mod_strings['LBL_UPGRADE_TEAM_EXISTS'].'<br>';
			   logThis(" Finish Building private teams", $path);
			
			}else{
			   $globalteam->create_team("Global", $mod_strings['LBL_GLOBAL_TEAM_DESC'], $globalteam->global_team);
			}
	    	
	    	//build private teams
		    logThis(" Start Building private teams", $path);
		    upgradeModulesForTeam();
		    logThis(" Finish Building private teams", $path); 

			//build team sets
		    logThis(" Start Building the team_set and team_sets_teams", $path);
		    upgradeModulesForTeamsets();
		    logThis(" Finish Building the team_set and team_sets_teams", $path);
		    
		    //upgrade teams
			if(file_exists($newtemplate_path.'/modules/Administration/upgradeTeams.php')) {
				logThis(" Start {$newtemplate_path}/modules/Administration/upgradeTeams.php", $path);
				include($newtemplate_path.'/modules/Administration/upgradeTeams.php');	
				logThis(" Finish {$newtemplate_path}/modules/Administration/upgradeTeams.php", $path);

				//update the users records to have default team
				logThis('running query to populate default_team on users table',$path);
				$GLOBALS['db']->query("update users set default_team = (select teams.id from teams where teams.name = concat('(',users.user_name, ')') or team.associated_user_id = users.id)");
				
			}

			//run upgrade script for dashlets to include sales/marketing
			if(function_exists('upgradeDashletsForSalesAndMarketing')){
	           logThis('calling upgradeDashlets script',$path);
			   upgradeDashletsForSalesAndMarketing();
			}
		    
	    }
	    

	    //if we are coming from a version prior to 550, then install password seed data
        if($pre_550)
        {
	    	include($newtemplate_path.'/install/seed_data/Advanced_Password_SeedData.php'); 
    	}
	    
		require("sugar_version.php");
		require('config.php');
		global $sugar_config;

		require("{$instanceUpgradePath}/sugar_version.php");
		if(!rebuildConfigFile($sugar_config, $sugar_version)) {
			logThis('*** ERROR: could not write config.php! - upgrade will fail!', $path);
			$errors[] = 'Could not write config.php!';
		}
		checkConfigForPermissions();
		 //}
    }
		//as last step, rebuild the language files and rebuild relationships
		if(file_exists($newtemplate_path.'/modules/Administration/RebuildJSLang.php')) {
			logThis("begin rebuilding js language files.", $path);
			include($newtemplate_path.'/modules/Administration/RebuildJSLang.php');
			@rebuildRelations($newtemplate_path.'/');
		}
		
	logThis('post_install() done.', $path);    
    logThis("***** SilentUpgrade completed successfully.", $path);
    logThis("***** SUCCESS.", $path);

	echo "********************************************************************\n";
	echo "*************************** SUCCESS*********************************\n";
	echo "********************************************************************\n";
} //END OF BIG if block


//Also set the tracker settings if  flavor conversion ce->pro or ce->ent
if(isset($_SESSION['current_db_version']) && isset($_SESSION['target_db_version'])){
	if($_SESSION['current_db_version'] == $_SESSION['target_db_version']){
	    $_REQUEST['upgradeWizard'] = true;
	    ob_start();
			include('include/Smarty/internals/core.write_file.php');
		ob_end_clean();
	 	$db =& DBManagerFactory::getInstance();
		if($ce_to_pro_ent){
	        //Also set license information
	        $admin = new Administration();
			$category = 'license';
			$value = 0;
			$admin->saveSetting($category, 'users', $value);
			$key = array('num_lic_oc','key','expire_date');
			$value = '';
			foreach($key as $k){
				$admin->saveSetting($category, $k, $value);
			}
		}
	}
}


//Also set the tracker settings if  flavor conversion ce->pro or ce->ent
if(isset($_SESSION['current_db_version']) && isset($_SESSION['target_db_version'])){
	if($_SESSION['current_db_version'] == $_SESSION['target_db_version']){
	    $_REQUEST['upgradeWizard'] = true;
	    ob_start();
			include('include/Smarty/internals/core.write_file.php');
		ob_end_clean();
	 	$db =& DBManagerFactory::getInstance();
		if($ce_to_pro_ent){
	        //Also set license information
	        $admin = new Administration();
			$category = 'license';
			$value = 0;
			$admin->saveSetting($category, 'users', $value);
			$key = array('num_lic_oc','key','expire_date');
			$value = '';
			foreach($key as $k){
				$admin->saveSetting($category, $k, $value);
			}
		}
	}
}

set_upgrade_progress('end','done','end','done');

if(file_exists('modules/Configurator/Configurator.php')){
	set_upgrade_progress('configurator','in_progress');
	require_once('include/utils/array_utils.php');
	if(!class_exists('Configurator')){
		require_once('modules/Configurator/Configurator.php');
	}
	$Configurator = new Configurator();
	if(class_exists('Configurator')){
		$Configurator->parseLoggerSettings();
	}
	set_upgrade_progress('configurator','done');
}

//unset the logger previously instantiated
if(file_exists('include/SugarLogger/LoggerManager.php')){
	set_upgrade_progress('logger','in_progress');
	if(!class_exists('LoggerManager')){
		
	}
	if(class_exists('LoggerManager')){
		unset($GsLOBALS['log']);
		$GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
	}
	set_upgrade_progress('logger','done');
}

///////////////////////////////////////////////////////////////////////////////
////	RECORD ERRORS
$phpErrors = ob_get_contents();
ob_end_clean();

if(count($errors) > 0) {
	foreach($errors as $error) {
		logThis("****** SilentUpgrade ERROR: {$error}", $path);
	}
	echo "FAILED\n";
} else {
	logThis("***** SilentUpgrade completed successfully.", $path);
	echo "********************************************************************\n";
	echo "*************************** SUCCESS*********************************\n";
	echo "********************************************************************\n";
	echo "******** If your pre-upgrade Leads data is not showing  ************\n";
	echo "******** Or you see errors in detailview subpanels  ****************\n";
	echo "************* In order to resolve them  ****************************\n";
	echo "******** Log into application as Administrator  ********************\n";
	echo "******** Go to Admin panel  ****************************************\n";
	echo "******** Run Repair -> Rebuild Relationships  **********************\n";
	echo "********************************************************************\n";
}


?>