<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
 
/**
* Check Access to Parent Object
*
* @author 		Jan Rocho <jan@rocho.eu>
* @version $Id$
*
*/
class ilParentPathCheckPlugin extends ilRepositoryObjectPlugin
{
	function getPluginName()
	{
		return "ParentPathCheck";
	}
}
?>
