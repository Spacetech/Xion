<?php

session_start();

set_time_limit(0);

if(!is_dir("data"))
{
	mkdir("data");
}

require_once("config.php");

require_once("constants.php");
require_once("helper.lib.php");

require_once("classes/pages.class.php");
require_once("classes/session.class.php");

require_once("classes/parentchild.class.php");
require_once("classes/building.class.php");  
require_once("classes/tag.class.php");

require_once("classes/base.class.php");
require_once("classes/staff.class.php");
require_once("classes/client.class.php");
require_once("classes/ticket.class.php");

require_once("twilio-php-latest/Services/Twilio.php");

function ErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
	LogError(debug_backtrace());

	/*
	echo 'Into '.__FUNCTION__.'() at line '.__LINE__.
	"\n\n---ERRNO---\n". print_r( $errno, true).
	"\n\n---ERRSTR---\n". print_r( $errstr, true).
	"\n\n---ERRFILE---\n". print_r( $errfile, true).
	"\n\n---ERRLINE---\n". print_r( $errline, true).
	"\n\n---ERRCONTEXT---\n".print_r( $errcontext, true).
	"\n\nBacktrace of errorHandler()\n".
	print_r( debug_backtrace(), true);
	*/

	return true;
}

//set_error_handler("ErrorHandler");

Pages::Init();

$error = false;

$me = null;

function UpdateLoggedIn()
{
	global $me;

	$me = Session::GetStaff();

	if(!is_object($me) || !$me->IsValid() || !$me->IsActive())
	{
		$me = null;
	}
}

UpdateLoggedIn();

$twilio = new Services_Twilio($twilioAccountSid, $twilioAuthToken);

/*

$BUILDINGS = array(
	"College-in-the-Woods" => array(
		"Cayuga",
		"Mohawk",
		"Oneida",
		"Onondaga",
		"Seneca"),
	"Dickinson Community" => array(
		"Digman",
		"Johnson",
		"O'Connor",
		"Rafuse"),
	"Hinman College" => array(
		"Cleveland",
		"Hughes",
		"Lehman",
		"Roosevelt",
		"Smith"),
	"Newing College" => array(
		"Bingham",
		"Broome",
		"Delaware",
		"Endicott"),
	"Mountainview College" => array(
		"Cascade",
		"Hunter",
		"Marcy",
		"Windham"),
	"Apartment Communities" => array(
		"Hillside",
		"Adirondack",
		"Belmont",
		"Catskill",
		"Darien",
		"Evangola",
		"Filmore",
		"Glimmerglass",
		"Hempstead",
		"Jones",
		"Keuka",
		"Lakeside",
		"Minnewaska",
		"Nyack",
		"Palisades",
		"Rockland",
		"Saratoga",
		"Susquehanna",
		"Brandywine",
		"Choconut",
		"Glenwood",
		"Nanticoke")
);

$TAGS = array(
	"Platform" => array(
			"Desktop",
			"Laptop",
			"Other",
			"Smartphone",
			"Tablet",
			"Printer",
	),
	"Operating System" => array(
			"Linux",
			"OS X",
			"Windows",
			"Android",
			"iOS",
	),
	"Brand" => array(
			"Acer",
			"Apple",
			"Asus",
			"Dell",
			"Gateway",
			"HP",
			"Lenovo",
			"Sony",
	),
	"Hardware" => array(
			"Graphics Card",
			"Motherboard",
			"Network Card",
			"Optical Drive",
			"Power Supply",
			"Processor",
			"RAM",
			"Sound Card",
			"Speakers",
			"Screen",
			"USB Port",
			"Webcam",
	),
	"Console" => array(
			"PSP",
			"PSP Vita",
			"PS3",
			"PS4",
			"DS",
			"DSi",
			"DS Lite",
			"2DS",
			"3DS",
			"Wii",
			"Wii U",
			"Xbox 360",
			"Xbox One",
	)
);

foreach ($BUILDINGS as $key => $value)
{
	foreach($value as $building)
	{
		Building::Add($building, $key);
	}
}

foreach ($TAGS as $key => $value)
{
	foreach($value as $tag)
	{
		Tag::Add($tag, $key);
	}
}

*/

/*

$database->beginTransaction();

for($i=0; $i < 300000; $i++)
{
	Client::Add("Client #".$i, "client".$i, "O'Connor", "Room ".$i, "N/A");
}

$database->commit();

*/

/*
$database->beginTransaction();

$client = Client::Load(300039);
$community = Building::GetCommunity($client->GetBuilding())
/*;

for($i=0; $i < 300000; $i++)
{
	$ticket = Ticket::Add($client->GetID(), $me->GetID(), "This clients Mac is broken", STATUS_OPENED, array("Apple,Laptop"), $client->GetBuilding(), $community);
}

$database->commit();
*/

?>