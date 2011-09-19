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

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
* Check Access to Parent Object
*
* @author 		Jan Rocho <jan@rocho.eu>
*
* $Id$
*/
class ilObjParentPathCheck extends ilObjectPlugin
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id);
	}
	

	/**
	* Get type.
	*/
	final function initType()
	{
		$this->setType("xppc");
	}
	
	/**
	* Create object
	*/
	function doCreate()
	{
		global $ilDB;
		
		$ilDB->manipulate("INSERT INTO rep_robj_xppc_data ".
			"(id, is_online, include_deleted, include_offline, limit) VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(1, "integer").",".
			$ilDB->quote(50, "integer").
			")");
	}
	
	/**
	* Read data from db
	*/
	function doRead()
	{
		global $ilDB,$ilUser;
		
		$set = $ilDB->query("SELECT * FROM rep_robj_xppc_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->setOnline($rec["is_online"]);
			$this->setIncludeDeleted($rec["include_deleted"]);
			$this->setIncludeOffline($rec["include_offline"]);
			$this->setLimit($rec["limit"]);
		}
		
		$user = $ilDB->query("SELECT COUNT(*) AS cnt FROM rbac_ua WHERE ".
			    "usr_id = ".$ilDB->quote($ilUser->id, "integer")." and ".
				"rol_id = ".$ilDB->quote(2, "integer"));

		$rec = $ilDB->fetchAssoc($user);
		
		$this->setAdminUser($rec["cnt"]);
	}
	
	
	/**
	* Update data
	*/
	function doUpdate()
	{
		global $ilDB;
		
		$ilDB->manipulate($up = "UPDATE rep_robj_xppc_data SET ".
			" is_online = ".$ilDB->quote($this->getOnline(), "integer").",".
			" include_deleted = ".$ilDB->quote($this->getIncludeDeleted(), "integer").",".
			" include_offline = ".$ilDB->quote($this->getIncludeOffline(), "integer").",".
			" `limit` = ".$ilDB->quote($this->getLimit(), "integer").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
	}
	
	/**
	* Delete data from db
	*/
	function doDelete()
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM rep_robj_xppc_data WHERE ".
			" id = ".$ilDB->quote($this->getId(), "integer")
			);
		
	}
	
	/**
	* Do Cloning
	*/
	function doClone($a_target_id,$a_copy_id,$new_obj)
	{
		global $ilDB;
		
		$new_obj->setOnline($this->getOnline());
		$new_obj->setIncludeDeleted($this->getIncludeDeleted());
		$new_obj->setIncludeOffline($this->getIncludeOffline());
		$new_obj->setLimit($this->getLimit());
		$new_obj->update();
	}
	
	/**
	*   Count Courses
	*/
	
	function countCourses()
	{
		global $ilDB, $ilUser;
		
		$coursesSQL = "SELECT COUNT(*) AS cnt FROM object_data as od join ".
		"object_reference as ref on od.obj_id = ref.obj_id join crs_settings as cs on od.obj_id = cs.obj_id ".
		"where od.type = 'crs' ";
		
		if(!$this->getIncludeDeleted()) 
			$coursesSQL .= "and ref.deleted is NULL";
			
		// if not an ILIAS admin, only show courses where user is owner
		if(!$this->getAdminUser())
			$courseSQL .= " and od.owner = ".$ilUser->id." ";
			
		if(!$this->getIncludeOffline())
			$coursesSQL .= " and cs.activation_type != 0 ".
						   "and ((cs.activation_type = 1) or (cs.activation_type = 2 and activation_start < ".time()." and ".
					       "activation_end > ".time().")) ";
	
		$coursesSQL .= " order by od.obj_id asc";
		
		$courses = $ilDB->query($coursesSQL);
		$rec = $ilDB->fetchAssoc($courses);
		
		return $rec["cnt"];
	}
	
	/**
	*	Fetch Courses
	*/
	function fetchCourses($start=0)
	{
		global $ilDB;
	
		$coursesSQL = "SELECT od.title, od.obj_id, od.description, ref.ref_id FROM object_data as od join ".
		"object_reference as ref on od.obj_id = ref.obj_id join crs_settings as cs on od.obj_id = cs.obj_id ".
		"where od.type = 'crs' ";
		
		if(!$this->getIncludeDeleted()) 
			$coursesSQL .= "and ref.deleted is NULL";

		// if not an ILIAS admin, only show courses where user is owner
		if(!$this->getAdminUser())
			$courseSQL .= " and od.owner = ".$ilUser->id." ";
			
		if(!$this->getIncludeOffline())
			$coursesSQL .= " and cs.activation_type != 0 ".
						   "and ((cs.activation_type = 1) or (cs.activation_type = 2 and activation_start < ".time()." and ".
					       "activation_end > ".time().")) ";
	
		$coursesSQL .= " order by od.obj_id asc LIMIT ".$ilDB->quote($start, "integer").",".
		$ilDB->quote($this->getLimit(), "integer");
		
		$courses = $ilDB->query($coursesSQL);
		

		$output = '<table class="tb_outer">';
		$i=0;
		
		while ($rec = $ilDB->fetchAssoc($courses))
		{
			
			$users = $this->fetchUsers($rec['obj_id'], $rec['ref_id']);
			//if($users)
			//{
				$output .= ($i%2) ? '<tr class="course_name odd">' : '<tr class="course_name even">';
				
				$output .= '<td colspan="3"><strong>'.$rec['title'].' [ref: '.$rec['ref_id'].']</strong> '.
							'<a href="./repository.php?ref_id='.$rec['ref_id'].'&cmdClass=ilobjcoursegui&cmd=members" target="_blank">'.
							'<img src="./Customizing/global/plugins/Services/Repository/RepositoryObject/ParentPathCheck/templates/images/external.png" alt="Open Course" title="Open Course" width="16" height="16" />'.
							'</a></td></tr>';
				$output .= $users;
			//}
			$i++;
		}
		
		$output .= '</table>';
		return $output;
	}
	
	function fetchUsers($a_crs_obj_id,$a_crs_ref_id)
	{
		global $ilDB, $ilAccess;

		$users = $ilDB->query("SELECT rbua.usr_id, ud.login, ud.firstname, ud.lastname FROM rbac_ua as rbua join usr_data as ".
		"ud on rbua.usr_id = ud.usr_id where rbua.rol_id in ".
		"(select obj_id from object_data where description like ".
		"'%course obj_no.".$ilDB->quote($a_crs_obj_id, "integer")."')");
		
		$title = "";
		$error = FALSE;
		$header = FALSE;
		$i = 0;
		
		while ($rec = $ilDB->fetchAssoc($users))
		{
			
			if(!$ilAccess->doPathCheck('read', '', $a_crs_ref_id, $rec['usr_id']))
			{
				
				if(!$header)
				{
					$title .= '<tr><th>Login</th><th>usr_id</th><th>Name</th></tr>';
				}
				
				$title .= ($i%2) ? '<tr class="odd_n">' : '<tr class="even_n">';
				$title .= '<td class="ppc_login">'.$rec['login'].'</td>'.
						  '<td class="ppc_usr_id">'.$rec['usr_id'].'</td>'.
						  '<td class="ppc_name">'.$rec['firstname'].' '.$rec['lastname'].
						  '</td></tr>';
				
				
				$error = TRUE;
				$header = TRUE;
				$i++;
			}
			
			
			
		}
		
		if($error)
			return $title.'<tr><td colspan="3">&nbsp;</td></tr>';
			
		return FALSE;
	}
	
//
// Set/Get Methods for our example properties
//

	/**
	* Set online
	*
	* @param	boolean		online
	*/
	function setOnline($a_val)
	{
		$this->online = $a_val;
	}
	
	/**
	* Get online
	*
	* @return	boolean		online
	*/
	function getOnline()
	{
		return $this->online;
	}
	
	/**
	* Set deleted courses option
	*
	* @param	int		include_deleted
	*/
	function setIncludeDeleted($a_val)
	{
		$this->include_deleted = $a_val;
	}
	
	/**
	* Get deleted courses option
	*
	* @return	int		include_deleted
	*/
	function getIncludeDeleted()
	{
		return $this->include_deleted;
	}
	
	/**
	* Set offline courses option
	*
	* @param	int		include_offline
	*/
	function setIncludeOffline($a_val)
	{
		$this->include_offline = $a_val;
	}
	
	/**
	* Get offline courses option
	*
	* @return	int		include_offline
	*/
	function getIncludeOffline()
	{
		return $this->include_offline;
	}
	
	/**
	* Set deleted courses option
	*
	* @param	int		limit
	*/
	function setLimit($a_val)
	{
		$this->limit = $a_val;
	}
	
	/**
	* Get deleted courses option
	*
	* @return	int		limit
	*/
	function getLimit()
	{
		return $this->limit;
	}
	
	/**
	* Set admin user flag
	*
	* @param	int		adminUser
	*/
	function setAdminUser($a_val)
	{
		$this->adminUser = $a_val;
	}
	
	/**
	* Get deleted courses option
	*
	* @return	int		adminUser
	*/
	function getAdminUser()
	{
		return $this->adminUser;
	}

}
?>
