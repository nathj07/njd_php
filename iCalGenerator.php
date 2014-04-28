<?php
/*
File: iCalGenerator.php
Created: 24th October 2008
Author: Nathan Davies
Purpose: To generate iCal file
*/
function __autoload($pcClassName)
{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/' . $pcClassName . '.php' ;
}
session_start();
$loDataObject	= new dataObject() ;
// Dates must be in the format YYYYMMDD
switch($_GET['type'])
{
case 1: // all future events
$lcEventsSelect = "SELECT a.ID, a.title, a.summary, concat(date_format(a.startDate, '%Y%m%d'), 'T', date_format(a.startTime, '%H%i%S')) as start, concat(date_format(a.endDate, '%Y%m%d'), 'T', date_format(a.endTime, '%H%i%S')) as end, b.name as location FROM tbl_event a LEFT OUTER JOIN tbl_venue b on b.ID = a.venueID WHERE a.startDate>now() AND a.publishDate<=now() ORDER by startDate ASC" ;
break;
case 2: // all my bookings
$lcEventsSelect = "SELECT a.ID, a.title, a.summary, concat(date_format(a.startDate, '%Y%m%d'), 'T', date_format(a.startTime, '%H%i%S')) as start, concat(date_format(a.endDate, '%Y%m%d'), 'T', date_format(a.endTime, '%H%i%S')) as end, b.name as location FROM tbl_event a LEFT OUTER JOIN tbl_venue b on b.ID = a.venueID WHERE a.startDate>now() AND a.publishDate<=now() AND a.ID IN (SELECT c.eventID FROM tbl_userevent c WHERE c.userID=$lnUserID) ORDER by startDate ASC" ;
break;
}
$laEvent = $loDataObject->queryGetData(false,$lcEventsSelect);
// Define the file as an iCalendar file
header("Content-Type: text/Calendar");
// Give the file a name and force download
header("Content-Disposition: inline; filename=clfcalendar.ics");
// Header of ics file
echo "BEGIN:VCALENDAR\n";
echo "VERSION:2.0\n";
echo "PRODID:PHP\n";
echo "METHOD:REQUEST\n";
// Loop through results and create an event for each item
foreach($laEvent as $laEventRow)
{
    echo "BEGIN:VEVENT\n";
    // The end date of an event is non-inclusive, so if the event is an all day event or one with no specific start and stop
    // times, the end date would be the next day.
    echo "DTSTART:".$laEventRow['start']."\n";
    echo "DTEND:".$laEventRow['end']."\n";
    // Only create Description field if there is a description
    if(isset($laEventRow['summary']) && $laEventRow['summary'] != '')
    {
            echo "DESCRIPTION:";
            echo $laEventRow['summary']."\n";
    }
	if(isset($laEventRow['location']) && $laEventRow['location'] != '')
    {
            echo "LOCATION:";
            echo $laEventRow['location']."\n";
    }
    echo "SUMMARY:{$laEventRow['title']}\n";
    echo "UID:{$laEventRow['ID']}\n";
    echo "SEQUENCE:0\n";
    echo "DTSTAMP:".date('Ymd').'T'.date('His')."\n";
    echo "END:VEVENT\n";
}
echo "END:VCALENDAR\n";