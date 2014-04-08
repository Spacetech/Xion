<?php

class Building extends ParentChild
{
	public static function GetCommunity($building)
	{
		return self::GetRealParent($building)->GetParent();
	}
}

?>
