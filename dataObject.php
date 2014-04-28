<?php
/*
File: lib/dataObject.php
Author: Nathan Davies
Created: 08/06/2007
Purpose: To hold all the php-MySQL data connection functions and methods. The aim is to eventually build this as a global object that is initialised when index.php first loads
Ammendment;
ND 15/06/2007	Improving the code to make this a true Data Abstraction Layer, not using TRY in the new version of doing things
ND 18/06/2007	Completing the code for INSERT, DELETE, UPDATE, compareCheckum, exceptionHandler and getLastAutoInc
ND 20/06/2007	Parameterising dataObject() for future re-use
ND 18/07/2007	Added encryptData() to the object
ND 25/07/2007	Added new class - writeApplication()
ND 03/08/2007	Added new function passwordGenerator() to the dataObject() class. It has been added to this class as passwords are connected strongly with the database.
				It could in time be moved to a userObject()
ND 20/06/2008	Moved into new CLO development space and changed the database details - hardcoded for ease of development.
*/

class dataObject
{
	var $database_name ;
	var $database_user ;
	var $database_password ;
	var $database_host ;
	var $database_link ;
	var $nRecentCount ;
	var	$ldDate ;
	var $lnBadUserID ;

	public function dataObject()
	{
		$this->database_name		= "" ;
		$this->database_user		= "" ;
		$this->database_password	= "" ;
		$this->database_host		= "" ;

		$this->ldDate				= date("Y-m-d") ;
	}

	// Property Manipulation Functions
 	function changeUser($user)
    {
    	$this->database_user = $user;
    }

    function changePass($pass)
    {
    	$this->database_pass = $pass;
    }

    function changeHost($host)
    {
		$this->database_host = $host;
    }

    function changeName($name)
    {
    	$this->databse_name = $name;
    }

    function changeAll($user, $pass, $host, $name)
    {
    	$this->database_user = $user;
 	    $this->database_pass = $pass;
 	    $this->database_host = $host;
 	    $this->database_name = $name;
     }

/*
====================
Connection Functions
====================
*/

	function makeConnection()
	{
	// connect to the data server
		$this->database_link = mysql_connect($this->database_host, $this->database_user, $this->database_password) or die("Could not make the connection to MySQL");
	// connect to the database required
		mysql_select_db($this->database_name) or die("Could not open database: ". $this->database_name);
	}

	function closeConnection()
	{
		if(isset($this->database_link))
		{
		mysql_close($this->database_link);
		}
		else
		{
		mysql_close();
		}
	}

	function ensureConnection()
	{
		if(!isset($this->database_link))
		{
			$this->makeConnection();
		}
	}

/*
===============
Query Functions
===============
*/

	function queryGetData($plSetCount, $pcQuery)
	{
		// ensure there is a connection
		$this->ensureConnection();
		// run the supplied query
        $result = mysql_query($pcQuery, $this->database_link) or die("Error: ". mysql_error());
   	    $laReturnArray = array();
		$i=0;
		while ($row = mysql_fetch_array($result, MYSQL_BOTH))
		{
			if ($row)
			{
		     	$laReturnArray[$i++]=$row;
			}
		}
		if($plSetCount)
		{
			$this->nRecentCount = mysql_num_rows($result);
		}
		mysql_free_result($result);
		return $laReturnArray;
	}

	function queryInsert($pcTableName, $pcFieldList, $pcValueList, $plReturnID)
	{
		// ensure there is a connection
		$this->ensureConnection();
		// build up the insert string
		$lcInsertCommand = "INSERT INTO " . $pcTableName . "(" . $pcFieldList . ") VALUES(" . $pcValueList . ")" ;
		// insert the records
		$lvResult = mysql_query($lcInsertCommand, $this->database_link) or die("Error: ". mysql_error());
		if ($plReturnID)
		{
			$lvResult = $this->getLastAutoInc($pcTableName);
		}
		return $lvResult;
	}

	function iQuery($pcQuery)
	{
	// this can be used for delete statments and update statements
		$this->ensureConnection();
		$lnhandle = fopen('../sql.txt', 'a+')	;
		fwrite($lnhandle, $pcQuery)	;
		fclose($lnhandle) ;
		$lvResult = mysql_query($pcQuery, $this->database_link) or die("Error: ". mysql_error());
		return $lvResult;
	}

/*
====================
Ancilliary Functions
====================
*/

	function compareChecksum($pcValueList, $pcCheckSum)
	{
		$llIsValid = (crc32($pcValueList) == $pcCheckSum);
		return $llIsValid;
	}

	function handleException($pnExceptionNumber)
	{
	// handling errors that are not handled by the die option
		header("Location:potentialissue.php?pi=$pnExceptionNumber");
	}

	function getLastAutoInc($pcTableName)
	{
		$lnReturn = mysql_insert_id($this->database_link);
		return $lnReturn;
	}

	function encryptData($pcToEncrypt)
	{
		//hash
		return hash('sha256', $pcToEncrypt) ;
	}

	function secure($data, $plIsEmail)
	{
		// prevent the majority of attacks by removing certain elements from the data.
		if ($plIsEmail)
		{
			$replace = array('<' => '' , '>' => '' , '&' => '' , ',' => '' , '*' => '' , '/' => '' );
		}
		else
		{
			$replace = array('<' => '' , '>' => '' , '&' => '' , '.' => '' , ',' => '' , '*' => '' , '/' => '' , '@' => '');
		}

		$data = strtr($data , $replace);
		return $data;
	}

	function escapeString($pcString)
	{
		$lcReturnString = "" ;
		if (!empty($pcString))
		{
			$this->ensureConnection() ;
			if(get_magic_quotes_gpc())
			{
				$pcString = stripslashes($pcString) ;
			}
			$lcReturnString = mysql_real_escape_string($pcString, $this->database_link) ;
		}

		return $lcReturnString ;
	}

	function passwordGenerator($pnResultLength)
	{
		// list all possible characters
		$possible = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$code = '';
		$i = 0;
		while ($i < $pnResultLength)
		{
			$code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
			$i++;
		}
		return $code;
	}

	function usernameGenerator($pcPotentialName, $pnResultLength)
	{
		$lcUserName = !empty($pcPotentialName)?$pcPotentialName:$this->passwordGenerator($pnResultLength) ;
		// check for uniqueness
		$lcCheckUser =
		"SELECT
		a.ID
		FROM tbl_user a
		WHERE a.name = '$lcUserName' " ;

		$laResults = $this->queryGetData(true, $lcCheckUser) ;
		if($laResults)
		{
			// there is a match so it's no good, make a recursive call
			$lcUserName = $this->usernameGenerator("", $pnResultLength) ;
		}
		else
		{
			return $lcUserName ;
		}
	}

/*
======================
Moderation Functions
======================
*/
	function deleteThread($pnThreadID, $plIsBanned)
	{
		// get the USER ID of the thread starter
		$lcGetStarterSQL =
		"SELECT
		a.userID, a.title,
		b.email, b.display
		FROM tbl_thread a
		LEFT OUTER JOIN tbl_user b on b.ID = a.userID
		WHERE a.ID = $pnThreadID" ;

		$laThreadStarter = $this->queryGetData(false, $lcGetStarterSQL);
		$lcUserEmail	= $laThreadStarter[0]['email'];
		$lcUserDisplay	= $laThreadStarter[0]['display'];
		$lcThreadTitle	= $laThreadStarter[0]['title'] ;
		$this->lnBadUserID = $laThreadStarter[0]['userID'];

		// delete the thread and the post
		$lcDeleteThread = "DELETE FROM tbl_thread WHERE ID = $pnThreadID" ;
		$lcDeletePosts	= "DELETE FROM tbl_post WHERE threadID = $pnThreadID" ;

		$this->iQuery($lcDeleteThread) ;
		$this->iQuery($lcDeletePosts) ;

		// email the thread starter
		require_once '../lib/emailObject.php' ;
		$loEmailObject = new emailObject($lcUserDisplay .'&&' . $lcUserEmail, $_SESSION['email'], $_SESSION['email'], false, "", "") ;
		$lcMessage = $lcUserDisplay . "||" ;
		$lcMessage .= "We have deleted a thread you started in one of the forums. This thread was found to be in breach of our terms and conditions.||" ;
		$lcMessage .= "Thread title: " . $lcThreadTitle . "||" ;
		if($plIsBanned)
		{
			$lcMessage .= "Your account has now been suspended.||" ;
		}
		else
		{
			$lcMessage .= "If you continue to breach the terms and conditions your account will be removed.||" ;
		}
		$lcMessage .= "If you believe someone else has started this thread using your account then please contact us immediately and we will look into the matter further.||" ;
		$lcMessage .= "Many thanks||Christian Leaders Forum" ;
		$lcSubject = "Thread in breach of terms and conditions" ;
		$loEmailObject->sendEmail($lcSubject, $lcMessage) ;

		$lcDisplay = '<p>The thread &mdash; ' . $lcThreadTitle . ' &mdash; has been deleted and the member informed.</p>' ;
		return $lcDisplay ;
	}

	function deletePost($pnPostID, $plIsBanned) // doesn't delete the post but simply overwrites it with a comment about the removal
	{
		// get the user ID of the poster
		$lcGetPosterSQL =
		"SELECT
		a.userID,
		b.email, b.display,
		c.title
		FROM tbl_post a
		LEFT OUTER JOIN tbl_user b on b.ID = a.userID
		LEFT OUTER JOIN tbl_thread c ON c.ID = a.threadID
		WHERE a.ID = $pnPostID";

		$laPostStarter	= $this->queryGetData(false, $lcGetPosterSQL) ;
		$lcUserEmail	= $laPostStarter[0]['email'] ;
		$lcUserDisplay	= $laPostStarter[0]['display'] ;
		$lcThreadTitle	= $laPostStarter[0]['title'] ;
		$this->lnBadUserID = $laPostStarter[0]['userID'];

		// overwrite the post contents with a message to say it was deleted by moderator
		$lcNewPost		= "The original post has been removed due to a breach of terms and conditions.<br />" ;
		$lcNewPost		.= "Post originally made by " . $lcUserDisplay ;
		$lnModID		= $_SESSION['userID'] ;
		$lcUpdatePost 	= "UPDATE tbl_post SET userID = $lnModID, content='" . $lcNewPost . "', editDate=now(), isOnReport=0 WHERE ID=$pnPostID" ;
		$this->iQuery($lcUpdatePost) ;

		// email the poster
		require_once '../lib/emailObject.php' ;
		$loEmailObject = new emailObject($lcUserDisplay .'&&' . $lcUserEmail, $_SESSION['email'], $_SESSION['email'], false, "", "") ;
		$lcMessage .= $lcUserDisplay . "||" ;
		$lcMessage .= "We have deleted a post you submitted under thread listed below. This post was found to be in breach of our terms and conditions.||" ;
		$lcMessage .= "Thread title: " . $lcThreadTitle . "||" ;
		if($plIsBanned)
		{
			$lcMessage .= "Your account has now been suspended.||" ;
		}
		else
		{
			$lcMessage .= "If you continue to breach the terms and conditions your account will be removed.||" ;
		}
		$lcMessage .= "If you believe someone else has submitted this post using your account then please contact us immediately and we will look into the matter further.||" ;
		$lcMessage .= "Many thanks||Christian Leaders Forum" ;
		$lcSubject = "Post in breach of terms and conditions" ;
		$loEmailObject->sendEmail($lcSubject, $lcMessage) ;

		$lcDisplay = '<p>The post under thread &mdash; ' . $lcThreadTitle . ' &mdash; has been deleted and the member informed.</p>' ;
		return $lcDisplay ;
	}

	function deleteReview($pnType, $pnReviewID, $plIsBanned)
	{
		switch($pnType)
		{
			case 11: // download
				$lcGetSQL =
				"SELECT
				a.userID,
				b.email, b.display,
				c.title
				FROM tbl_resourcereview a
				LEFT OUTER JOIN tbl_user b on b.ID = a.userID
				LEFT OUTER JOIN tbl_resource c ON c.ID = a.resourceID
				WHERE a.ID = $pnReviewID";

				$lcDelete = "DELETE FROM tbl_resourcereview WHERE ID = $pnReviewID" ;
				break ;
			case 12: // article
				$lcGetSQL =
				"SELECT
				a.userID,
				b.email, b.display,
				c.title
				FROM tbl_articlereview a
				LEFT OUTER JOIN tbl_user b on b.ID = a.userID
				LEFT OUTER JOIN tbl_article c ON c.ID = a.articleID
				WHERE a.ID = $pnReviewID";

				$lcDelete = "DELETE FROM tbl_articlereview WHERE ID = $pnReviewID" ;
				break ;
			case 13: // event
				$lcGetSQL =
				"SELECT
				a.userID,
				b.email, b.display,
				c.title
				FROM tbl_eventreview a
				LEFT OUTER JOIN tbl_user b on b.ID = a.userID
				LEFT OUTER JOIN tbl_event c ON c.ID = a.eventID
				WHERE a.ID = $pnReviewID";

				$lcDelete = "DELETE FROM tbl_eventreview WHERE ID = $pnReviewID" ;
				break ;
			case 21: // training
				$lcGetSQL =
				"SELECT
				a.userID,
				b.email, b.display,
				c.title
				FROM tbl_trainingreview a
				LEFT OUTER JOIN tbl_user b on b.ID = a.userID
				LEFT OUTER JOIN tbl_training c ON c.ID = a.trainingID
				WHERE a.ID = $pnReviewID";

				$lcDelete = "DELETE FROM tbl_trainingreview WHERE ID = $pnReviewID" ;
				break ;
		}
		// get the user ID of the reviewer
		$laStarter		= $this->queryGetData(false, $lcGetSQL) ;
		$lcUserEmail	= $laStarter[0]['email'] ;
		$lcUserDisplay	= $laStarter[0]['display'] ;
		$lcResource		= $laStarter[0]['title'] ;
		$this->lnBadUserID = $laStarter[0]['userID'];

		// delete the review
		$this->iQuery($lcDelete) ;

		// email the poster
		require_once '../lib/emailObject.php' ;
		$loEmailObject = new emailObject($lcUserDisplay .'&&' . $lcUserEmail, $_SESSION['email'], $_SESSION['email'], false, "", "") ;
		$lcMessage = $lcUserDisplay . "||" ;
		$lcMessage .= "We have deleted a review you submitted for the item listed below. This review was found to be in breach of our terms and conditions.||" ;
		$lcMessage .= "Review of: " . $lcResource . "||" ;
		if($plIsBanned)
		{
			$lcMessage .= "Your account has now been suspended.||" ;
		}
		else
		{
			$lcMessage .= "If you continue to breach the terms and conditions your account will be removed.||" ;
		}
		$lcMessage .= "If you believe someone else has submitted this review using your account then please contact us immediately and we will look into the matter further.||" ;
		$lcMessage .= "Many thanks||Christian Leaders Forum" ;
		$lcSubject = "Review in breach of terms and conditions" ;
		$loEmailObject->sendEmail($lcSubject, $lcMessage) ;

		$lcDisplay = '<p>The review for ' . $lcResource . ' has been deleted and the member informed.</p>' ;
		return $lcDisplay ;
	}

/*
=========================
Admin Related Functions
=========================
*/
	function setDisplayOrder($pcTableName, $pnDesiredOrder, $pnRecordID)
	{
		// this re-sets all the orders in the table apart from the one being edited
		$llUpdate = false ;
		$lcUpdateStatement = "" ;
		// get the required information to make this work (if $pnRecordID == 0 then it's a new record we need to deal with ultimately)

		$lcSelectMaxOrder =
		"SELECT
		max(a.displayOrder) as maxOrder
		FROM " . $pcTableName . " a";

		$laMaxOrder = $this->queryGetData(false, $lcSelectMaxOrder) ;
		$lnMaxOrder = $laMaxOrder[0]['maxOrder'] ;
		$lnNextOrder = $lnMaxOrder + 1 ;

		// ensure the desired order doesn't exceed the max+1
		if($pnDesiredOrder > $lnNextOrder)
		{
			$pnDesiredOrder = $lnNextOrder ;
		}

		if($pnRecordID > 0)
		{
			$lcSelectCurrentOrder =
			"SELECT
			a.displayOrder
			FROM " . $pcTableName . " a
			WHERE ID = $pnRecordID" ;

			$laCurrentOrder = $this->queryGetData(false, $lcSelectCurrentOrder) ;
			$lnCurrentOrder = $laCurrentOrder[0]['displayOrder'] ;

			if($pnDesiredOrder < $lnCurrentOrder)
			{
				$lcUpdateStatement = "UPDATE " . $pcTableName ." SET displayOrder = displayOrder+1 WHERE displayOrder >= $pnDesiredOrder AND displayOrder < $lnCurrentOrder" ;
			}
			elseif($pnDesiredOrder > $lnCurrentOrder)
			{
				$lcUpdateStatement = "UPDATE " . $pcTableName ." SET displayOrder = displayOrder-1 WHERE displayOrder <= $pnDesiredOrder AND displayOrder > $lnCurrentOrder" ;
			}
			// note if current and desired are the same do nothing
		}
		else // new record being added
		{
			$lcUpdateStatement = "UPDATE " . $pcTableName . " SET displayOrder = displayOrder+1 WHERE displayOrder >= $pnDesiredOrder" ;
		}

		if(!empty($lcUpdateStatement))
		{
			if($pnDesiredOrder <> $lnNextOrder)
			{
				$llUpdate = $this->iQuery($lcUpdateStatement) ;
			}
			else
			{
				$llUpdated = true ; // mo uppdate neede as the desired order is next in sequence
			}
		}

		return $llUpdate ;
	}

	function dateChecker($pcYear, $pcMonth, $pcDay)
	{
		// format the parameters into a date
		$ldDate	= date('Y-m-d', strtotime($pcDay . '-' . $pcMonth . '-' . $pcYear)) ;

		// if this is 31 Feb 2003 (not a leap year) the result is 2nd March 2003
		// the way the date is created - via dropdowns menas this is the only issue there could be, rolling forward seems reasonable
		return $ldDate ;
	}

	function formatText($pcText)
	{
		$lcText			=	nl2br($pcText) ;
		$laParagraphs	=	explode('<br />', $lcText) ;
		$lcReturn 		=	'' ;
		$lnCount		=	count($laParagraphs) ;

		for($i = 0; $i < $lnCount; $i++)
		{
			$lcReturn .= '<p>' . $laParagraphs[$i] . '</p>' ;
		}
		// final formatting	- this allows for repeated calls to be made without extra tags being left in.
		$lcReturn = str_replace('<p></p>', '', $lcReturn) ;
		$lcReturn = str_replace('</p></p>', '', $lcReturn) ;
		$lcReturn = str_replace('<p><p>', '', $lcReturn) ;

		if(strpos($lcReturn, '['))
		{
			// update the story to replace any [] with proper html tags
			$lcReturn = str_replace('[', '<', $lcReturn) ;
			$lcReturn = str_replace(']', '>', $lcReturn) ;
		}


		return $lcReturn ;
	}

/*
====================
Specific Queries
====================
*/

} // end of class definition

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
