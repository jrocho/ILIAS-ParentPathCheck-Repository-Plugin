<#1>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'is_online' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'option_one' => array(
		'type' => 'text',
		'length' => 10,
		'fixed' => false,
		'notnull' => false
	),
	'option_two' => array(
		'type' => 'text',
		'length' => 10,
		'fixed' => false,
		'notnull' => false
	)
);

$ilDB->createTable("rep_robj_xppc_data", $fields);
$ilDB->addPrimaryKey("rep_robj_xppc_data", array("id"));
?>
<#2>

<#3>
<?php
if($ilDB->tableColumnExists("rep_robj_xppc_data", "option_one"))
{
    $query = "ALTER TABLE `rep_robj_xppc_data` CHANGE `option_one` `include_deleted` INT( 9 ) DEFAULT '0'";
    $res = $ilDB->query($query);
}
if($ilDB->tableColumnExists("rep_robj_xppc_data", "option_two"))
{
    $query = "ALTER TABLE `rep_robj_xppc_data` CHANGE `option_two` `include_offline` INT( 9 ) DEFAULT '1'";
    $res = $ilDB->query($query);
}
?>
<#4>
<?php
if(!$ilDB->tableColumnExists("rep_robj_xppc_data", "limit"))
{
    $query = "ALTER TABLE  `rep_robj_xppc_data` ADD  `limit` INT( 9 ) DEFAULT 50";
    $res = $ilDB->query($query);
}
?>


