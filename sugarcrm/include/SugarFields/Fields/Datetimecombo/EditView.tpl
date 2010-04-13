{*
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
*}
{{capture name=idname assign=idname}}{{sugarvar key='name'}}{{/capture}}
{{if !empty($displayParams.idName)}}
    {{assign var=idname value=$displayParams.idName}}
{{/if}}
<table border="0" cellpadding="0" cellspacing="0">
<tr valign="middle">
<td nowrap>
<input autocomplete="off" type="text" id="{{$idname}}_date" value="{$fields[{{sugarvar key='name' stringFormat=true}}].value}" size="11" maxlength="10" title='{{$vardef.help}}' tabindex="{{$tabindex}}" onblur="combo_{{$idname}}.update(); {{if isset($displayParams.updateCallback)}}{{$displayParams.updateCallback}}{{/if}}">
<img border="0" src="{sugar_getimagepath file='jscalendar.gif'}" alt="{$APP.LBL_ENTER_DATE}" id="{{$idname}}_trigger" align="absmiddle">&nbsp;
{{if empty($displayParams.splitDateTime)}}
</td>
<td nowrap>
{{else}}
<br>
{{/if}}
<div id="{{$idname}}_time_section"></div>
{{if $displayParams.showNoneCheckbox}}
<script type="text/javascript">
function set_{{$idname}}_values(form) {ldelim}
 if(form.{{$idname}}_flag.checked)  {ldelim} 
	form.{{$idname}}_flag.value=1;
	form.{{$idname}}.value="";
	form.{{$idname}}.readOnly=true;
 {rdelim} else  {ldelim} 
	form.{{$idname}}_flag.value=0;
	form.{{$idname}}.readOnly=false;
 {rdelim} 
{rdelim}
</script>
{{/if}}
</td>
</tr>
{{if $displayParams.showFormats}}
<tr valign="middle">
<td nowrap>
<span class="dateFormat">{$USER_DATEFORMAT}</span>
</td>
<td nowrap>
<span class="dateFormat">{$TIME_FORMAT}</span>
</td>
</tr>
{{/if}}
</table>
<input type="hidden" id="{{$idname}}" name="{{$idname}}" value="{$fields[{{sugarvar key='name' stringFormat=true}}].value}">
<script type="text/javascript" src="include/SugarFields/Fields/Datetimecombo/Datetimecombo.js"></script>
<script type="text/javascript">
var combo_{{$idname}} = new Datetimecombo("{$fields[{{sugarvar key='name' stringFormat=true}}].value}", "{{$idname}}", "{$TIME_FORMAT}", "{{$tabindex}}", '{{$displayParams.showNoneCheckbox}}', '{$fields[{{sugarvar key='name' stringFormat=true}}_flag].value}', true); 
//Render the remaining widget fields
text = combo_{{$idname}}.html('{{$displayParams.updateCallback}}');
document.getElementById('{{$idname}}_time_section').innerHTML = text;

//Call eval on the update function to handle updates to calendar picker object
eval(combo_{{$idname}}.jsscript('{{$displayParams.updateCallback}}'));

addToValidate('EditView',"{{$idname}}_date",'date',false,"{{$idname}}");
addToValidateBinaryDependency('EditView',"{{$idname}}_hours", 'alpha', false, "{$APP.ERR_MISSING_REQUIRED_FIELDS}{{$idname}}" ,"{{$idname}}_date");
addToValidateBinaryDependency('EditView', "{{$idname}}_minutes", 'alpha', false, "{$APP.ERR_MISSING_REQUIRED_FIELDS}{{$idname}}" ,"{{$idname}}_date");
addToValidateBinaryDependency('EditView', "{{$idname}}_meridiem", 'alpha', false, "{$APP.ERR_MISSING_REQUIRED_FIELDS}{{$idname}}","{{$idname}}_date");
</script>

<script type="text/javascript">
function update_{{$idname}}_available() {ldelim}
      YAHOO.util.Event.onAvailable("{{$idname}}_date", this.handleOnAvailable, this); 
{rdelim}

update_{{$idname}}_available.prototype.handleOnAvailable = function(me) {ldelim}
	Calendar.setup ({ldelim}
	onClose : update_{{$idname}},
	inputField : "{{$idname}}_date",
	ifFormat : "{$CALENDAR_FORMAT}",
	daFormat : "{$CALENDAR_FORMAT}",
	button : "{{$idname}}_trigger",
	singleClick : true,
	step : 1,
	weekNumbers:false
	{rdelim});
	
	//Call update for first time to round hours and minute values
	combo_{{$idname}}.update();
{rdelim}

var obj_{{$idname}} = new update_{{$idname}}_available(); 
</script>