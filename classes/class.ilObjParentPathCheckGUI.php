<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

/**
* Check Access to Parent Object
*
* User interface classes process GET and POST parameter and call
* application classes to fulfill certain tasks.
*
* @author 		Jan Rocho <jan@rocho.eu>
*
* $Id$
*
* Integration into control structure:
* - The GUI class is called by ilRepositoryGUI
* - GUI classes used by this class are ilPermissionGUI (provides the rbac
*   screens) and ilInfoScreenGUI (handles the info screen).
*
* @ilCtrl_isCalledBy ilObjParentPathCheckGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjParentPathCheckGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
*
*/
class ilObjParentPathCheckGUI extends ilObjectPluginGUI
{
	/**
	* Initialisation
	*/
	protected function afterConstructor()
	{
		// anything needed after object has been constructed
		// - example: append my_id GET parameter to each request
		//   $ilCtrl->saveParameter($this, array("my_id"));
	}
	
	/**
	* Get type.
	*/
	final function getType()
	{
		return "xppc";
	}
	
	/**
	* Handles all commmands of this class, centralizes permission checks
	*/
	function performCommand($cmd)
	{
		switch ($cmd)
		{
			case "editProperties":		// list all commands that need write permission here
			case "updateProperties":
			//case "...":
				$this->checkPermission("write");
				$this->$cmd();
				break;
			
			case "showContent":			// list all commands that need read permission here
			//case "...":
			//case "...":
				$this->checkPermission("read");
				$this->$cmd();
				break;
		}
	}

	/**
	* After object has been created -> jump to this command
	*/
	function getAfterCreationCmd()
	{
		return "editProperties";
	}

	/**
	* Get standard command
	*/
	function getStandardCmd()
	{
		return "showContent";
	}
	
//
// DISPLAY TABS
//
	
	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $ilAccess;
		
		// tab for the "show content" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent"));
		}

		// standard info screen tab
		$this->addInfoTab();

		// a "properties" tab
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
		}

		// standard epermission tab
		$this->addPermissionTab();
	}
	

// THE FOLLOWING METHODS IMPLEMENT SOME EXAMPLE COMMANDS WITH COMMON FEATURES
// YOU MAY REMOVE THEM COMPLETELY AND REPLACE THEM WITH YOUR OWN METHODS.

//
// Edit properties form
//

	/**
	* Edit Properties. This commands uses the form class to display an input form.
	*/
	function editProperties()
	{
		global $tpl, $ilTabs;
		
		$ilTabs->activateTab("properties");
		$this->initPropertiesForm();
		$this->getPropertiesValues();
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	* Init  form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initPropertiesForm()
	{
		global $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// title
		$ti = new ilTextInputGUI($this->txt("title"), "title");
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// description
		$ta = new ilTextAreaInputGUI($this->txt("description"), "desc");
		$this->form->addItem($ta);
		
		// online
		$cb = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
		$this->form->addItem($cb);
		
		// include deleted
		$cb = new ilCheckboxInputGUI($this->txt("include_deleted"), "op_include_deleted");
		$this->form->addItem($cb);
		
		// include offline
		$cb = new ilCheckboxInputGUI($this->txt("include_offline"), "op_include_offline");
		$this->form->addItem($cb);
		
		// limit
		$ti = new ilTextInputGUI($this->txt("limit_page"), "op_limit");
		$ti->setMaxLength(5);
		$ti->setSize(5);
		$ti->setValidationRegexp('[\d+]');
		$ti->setValidationFailureMessage($this->txt("error_onlydigit"));
		
		$this->form->addItem($ti);

		$this->form->addCommandButton("updateProperties", $this->txt("save"));
	                
		$this->form->setTitle($this->txt("edit_properties"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}
	
	/**
	* Get values for edit properties form
	*/
	function getPropertiesValues()
	{
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getDescription();
		$values["online"] = $this->object->getOnline();
		$values["op_include_deleted"] = $this->object->getIncludeDeleted();
		$values["op_include_offline"] = $this->object->getIncludeOffline();
		$values["op_limit"] = $this->object->getLimit();
		$this->form->setValuesByArray($values);
	}
	
	/**
	* Update properties
	*/
	public function updateProperties()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initPropertiesForm();
		if ($this->form->checkInput())
		{
			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setDescription($this->form->getInput("desc"));
			$this->object->setIncludeDeleted($this->form->getInput("op_include_deleted"));
			$this->object->setIncludeOffline($this->form->getInput("op_include_offline"));
			$this->object->setLimit($this->form->getInput("op_limit"));
			$this->object->setOnline($this->form->getInput("online"));
			$this->object->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editProperties");
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
//
// Show content
//

	/**
	* Show content
	*/
	function showContent()
	{
		global $tpl, $ilTabs, $ilCtrl;
		
		$tpl->addCss('./Customizing/global/plugins/Services/Repository/RepositoryObject/ParentPathCheck/templates/css/parentpathcheck.css');
		$ilTabs->activateTab("content");
		
		if(isset($_GET['ppc_start'])) 
		{
			$ppc_start = $_GET['ppc_start'];
		}
		else
		{
			$ppc_start = 0;
		}
		$content = '<div class="pagination">'.$this->buildPagination($ppc_start).'</div>';	
		
		$content .= $this->object->fetchCourses($ppc_start);
		
		$content .= '<div class="pagination">'.$this->buildPagination($ppc_start).'</div>';			
		
		
		
		$tpl->setContent($content);
	}
	
	/**
	* Build the pagination
	*/
	function buildPagination($ppc_start)
	{
		global $ilCtrl;
		
		$content = $content .= '<div class="left">';
		if($ppc_start != 0)
		{
			if($ppc_start - $this->object->getLimit() < 0)
			{
				$p_start = 0;
			}
			else
			{
				$p_start = $ppc_start - $this->object->getLimit();
			}
		
			$this->ctrl->setParameter($this, "ppc_start", $p_start);
			$previous = '<a href="'.$this->ctrl->getLinkTarget($this, "showContent","").'">'.$this->txt('previous').' '.
						  $this->object->getLimit().'</a>';
			$content .= $previous;
		}
		
		if(isset($previous)) $content .= ' | ';
		
		
		if($ppc_start+ $this->object->getLimit() < $this->object->countCourses())
		{
			
			$n_start = $ppc_start + $this->object->getLimit();
			
			$this->ctrl->setParameter($this, "ppc_start", $n_start);
			$next = '<a href="'.$this->ctrl->getLinkTarget($this, "showContent","").'">'.$this->txt('next').' '.
						  $this->object->getLimit().'</a>';
			$content .= $next;
		}
		
		if(!isset($next)) $content = substr($content,0,-3);
		$content .= '</div>';
		
		
		$querySplit = explode("&",$_SERVER["QUERY_STRING"]);
		
		$content .= '<div class="right"><form action="ilias.php" method="get">';
					
		
		foreach($querySplit as $value)
		{
			$valueSplit = explode("=",$value);
			if($valueSplit[0] != 'ppc_start')
				$content .= '<input type="hidden" name="'.$valueSplit[0].'" value="'.rawurldecode($valueSplit[1]).'" />';
		}
		
		$content .= '<select name="ppc_start">';
		
		$pages = $this->object->countCourses() / $this->object->getLimit();
		$s_start = 0;
		
		for($i=0;$i<$pages;$i++)
		{
			
			$content .= '<option value="'.$s_start.'" ';
			if($s_start == $ppc_start) $content .= 'selected';
			$content .= '>'.($i+1).'</option>';
			$s_start = $s_start + $this->object->getLimit();
			
		}
		$content .= '</select><input class="submit" type="submit" value="'.$this->txt('jump_page').'" /></form></div>';
		
		return $content;
	}

}
?>
