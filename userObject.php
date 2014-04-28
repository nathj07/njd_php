<?php
/*
File: lib/userObject
Author: Nathan Davies
Created: 8th August 2007
Purpose: To hold the functions used to control access etc
Ammendment:
3rd July 2008 - ND - removing unrequired functions under the new CLO development structure
7th July 2008 - ND - added ensureUser()
20th August 2008 - ND - Added isModerator
*/

class userObject
{
	var $loDataObject ;
	var $lnUserID ;
	var $llIsModerator ;
	var $llIsAdministrator ;
	var $lcEmail ;
	var $lcDisplay ;
	var $lcDisplayName ;
	var $lcName ;
	var $lcPostOrder ;
	var $lnFoodID;
	var $lnChurchID ;
	var $lnLoginCount ;
	var $ldDate ;
	var $llNewUser ;
	var $llReactivation ;
	var $llUserNameChanged ;
	var $lcAccess ;
	var $lnBibleRef ;
	// NewZapp API details
	var $lcNZAPIPWord ;
	var $lcNZUserName ;
	var $lcNZCID ;
	var $lcNZRequest ;
	var $lcNZResponse ;

	public function userObject($poDB)
	{
		$this->loDataObject = $poDB ;
		$this->ldDate		= date("Y-m-d") ;
	}

	function setUserSession($pcUserName, $pcPassword)
	{
		if(!empty($pcUserName) && !empty($pcPassword))
		{
			$lcUserName = $pcUserName ;
			// hash the password ready for the data check
			$lcPassword = $this->loDataObject->encryptData($pcPassword) ;

			// database check
			$llContinue = $this->checkForUserInDB($lcUserName, $lcPassword) ;

			if($llContinue)
			{
				if (!empty($this->lnUserID))
				{
					// set some session variables
					$_SESSION['userID']			= $this->lnUserID ;
					$_SESSION['isModerator']	= $this->llIsModerator ;
					$_SESSION['isAdmin']		= $this->llIsAdministrator ;
					$_SESSION['email']			= $this->lcEmail;
					$_SESSION['display']		= $this->lcDisplay ;
					$_SESSION['name']			= $this->lcName ;
					$_SESSION['postOrder']		= $this->lnPostOrder ;
					$_SESSION['foodID']			= $this->lnFoodID ;
					$_SESSION['churchID']		= $this->lnChurchID ;
					$_SESSION['bibleRef']		= $this->lnBibleRef ;

					$lnLoginCount 	= $this->lnLoginCount ;
					$lnUserID		= $this->lnUserID ;

					$lcUpdateLoginHistory =
					"UPDATE tbl_user
					SET
					tbl_user.lastLogin = now(),
					tbl_user.loginCount = $lnLoginCount
					WHERE tbl_user.ID = $lnUserID" ;

					$this->loDataObject->iQuery($lcUpdateLoginHistory);
					$_SESSION['hasUpdated'] = true;
					$_SESSION['isLoggedIn'] = true;
				}
			}
			else
			{
				$_SESSION['accessname'] = null ;
				$_SESSION['isLoggedID'] = false ;
				$lnAccessType = 99 ; // erroneous data entry
			}
		}
	}

	function checkForUserInDB($pcUserName, $pcPassword)
	{
		$llReturn = false ;

		$lcAccessCheck =
		"SELECT
		a.ID, a.email, a.display, a.name, a.postOrder, a.foodID, a.churchID, a.bibleID, a.loginCount + 1 as newLoginCount, a.isModerator, isAdministrator
		FROM tbl_user a
		WHERE a.email = '$pcUserName' AND a.access = '$pcPassword' AND a.isRegistered = 2" ;

		$laUserData = $this->loDataObject->queryGetData(false, $lcAccessCheck) ;

		if($laUserData)
		{
			$llReturn = true ;
			$this->lnUserID 		= $laUserData[0]['ID'] ;
			$this->llIsModerator	= $laUserData[0]['isModerator'] ;
			$this->llIsAdministrator= $laUserData[0]['isAdministrator'] ;
			$this->lcEmail 			= $laUserData[0]['email'] ;
			$this->lcDisplay 		= $laUserData[0]['display'] ;
			$this->lcName 			= $laUserData[0]['name'] ;
			$this->lnPostOrder		= $laUserData[0]['postOrder'] ;
			$this->lnFoodID 		= $laUserData[0]['foodID'] ;
			$this->lnChurchID 		= $laUserData[0]['churchID'] ;
			$this->lnBibleRef		= $laUserData[0]['bibleID'] ;
			$this->lnLoginCount		= $laUserData[0]['newLoginCount'] ;
		}
		return $llReturn ;
	}

	function logOut()
	{
	   	// not used at present - as this needs to be done before the main page is loaded to ensure it loads correctly
		$_SESSION['userID']			= 0 ;
		$_SESSION['isModerator']	= 0 ;
		$_SESSION['isAdmin']		= 0 ;
		$_SESSION['email']			= null;
		$_SESSION['display']		= null ;
		$_SESSION['name']			= null ;
		$_SESSION['postOrder']		= 0 ;
		$_SESSION['foodID']			= 0 ;
		$_SESSION['churchID']		= 0 ;
		$_SESSION['bibleRef']		= 31 ;
		$_SESSION['isLoggedIn']		= 0 ;
	}

	function updatePassword($pcPasswordToStore, $pnUserID)
	{
		$lcUpdatePassword =
		"UPDATE tbl_user
		SET
		tbl_user.access = '$pcPasswordToStore',
		tbl_user.isRegistered = 2,
		tbl_user.editDate = '" . $this->ldDate ."'
		WHERE tbl_user.ID = $pnUserID";

		$llUpdated = $this->loDataObject->iQuery($lcUpdatePassword);
		return $llUpdated ;
	}

	function isRegistered($pnEventID, $plCheckBooked)
	{
		$llReturn	= false ;
		$lnUserID	= $_SESSION['userID'] ;

		if (!empty($pnEventID))
		{
			$lcCheckPreviousRegistration =
			"SELECT
			a.ID
			FROM tbl_userevent a
			WHERE a.userID = '$lnUserID' AND a.eventID = '$pnEventID'" ;

			if($plCheckBooked)
			{
				$lcCheckPreviousRegistration .= " AND a.isBooked=1" ;
			}

			$laPreviousReg = $this->loDataObject->queryGetData(false, $lcCheckPreviousRegistration) ;

			if($laPreviousReg)
			{
				$llReturn = true ;
			}

		}
		return $llReturn ;
	}

	function onCourse($pnCourseID, $plCheckBooked)
	{
		$llReturn	= false ;
		$lnUserID	= $_SESSION['userID'] ;

		if (!empty($pnCourseID))
		{
			$lcCheckPreviousRegistration =
			"SELECT
			a.ID
			FROM tbl_usertraining a
			WHERE a.leaderID = '$lnUserID' AND a.courseID = '$pnCourseID'" ;

			if($plCheckBooked)
			{
				$lcCheckPreviousRegistration .= " AND a.isBooked=1" ;
			}

			$laPreviousReg = $this->loDataObject->queryGetData(false, $lcCheckPreviousRegistration) ;

			if($laPreviousReg)
			{
				$llReturn = true ;
			}

		}
		return $llReturn ;
	}

	function getUserEmailAndDisplayNameByID($pnUserID)
	{
		$lcGetUserEmail =
		"SELECT
		a.email, a.display
		FROM tbl_user a
		WHERE a.ID = $pnUserID" ;

		$laList = $this->loDataObject->queryGetData(false, $lcGetUserEmail)	;

		$lcEmail	= $laList[0]['email'] ;
		$lcDisplay	= $laList[0]['display'] ;

		$lcReturn = $lcDisplay . '&&' . $lcEmail ;

		return $lcReturn ;
	}

	function ensureUser($pcEmailAddress, $pcDesiredDisplay)
	{
		if($_SESSION['isLoggedIn'])
		{
			$this->llNewUser			= false ;
			$this->llUserNameChanged	= false ;
			$this->lnUserID				= $_SESSION['userID'] ;
			$this->lcDisplayName		= $_SESSION['display'] ;
		}
		else
		{
			if(!empty($pcEmailAddress) && ereg('^[_A-Za-z0-9-]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9-]+)*$', $pcEmailAddress))
			{
				// check for the email address in the database
				$lcEmailCheck =
				"SELECT
				a.ID, a.display, a.isRegistered
				FROM tbl_user a
				WHERE a.email = '$pcEmailAddress'" ;

				$laUserList	= $this->loDataObject->queryGetData(true, $lcEmailCheck) ;
				if($this->loDataObject->nRecentCount > 0)
				{
					$this->lnUserID			= $laUserList[0]['ID'] ;
					$this->lcDisplayName	= $laUserList[0]['display'] ;

					switch($laUserList[0]['isRegistered'])
					{
						case 0: // banned user , set userID to -1
							$this->lnUserID = -1 ;
							break ;
						case 1: // deactivated account
							$lnUserID = $this->lnUserID ;
							$lcUpdate = "UPDATE tbl_user SET isRegistered = 2, editDate = now() WHERE ID = $lnUserID" ;
							$this->loDataObject->iQuery($lcUpdate) ;
							$this->llReactivation = true;
							break;
					}

				}
				else // no match - create a user
				{
					$this->llNewUser = true ;
					// ensure uniqueness of display name; if this has to be altered simply add numbers to the end till it is unique.
					$this->loDataObject->nRecentCount = 99 ; // a dummy value;
					$lnLoopCount = 0 ;
					$this->lcDisplayName = trim($pcDesiredDisplay) ;
					$lcDisplayName = trim($pcDesiredDisplay) ;
					while($this->loDataObject->nRecentCount > 0)
					{
						if($lnLoopCount > 0)
						{
							$this->lcDisplayName		= $pcDesiredDisplay . str_pad($lnLoopCount, 3, '0', STR_PAD_LEFT) ;
							$lcDisplayName				= $this->lcDisplayName ;
							$this->llUserNameChanged	= true ;
						}
						$lcCheckDisplayName =
						"SELECT
						a.ID
						FROM tbl_user a
						WHERE a.display = '$lcDisplayName'" ;

						$laUserList = $this->loDataObject->queryGetData(true, $lcCheckDisplayName);

						$lnLoopCount++ ;

						if($lnLoopCount > 999 && $this->loDataObject->nRecentCount>0) // run out of options with the desired base name
						{
							$lnLoopCount = 0 ;
							$this->loDataObject->nRecentCount = 99 ;
							$this->lcDisplayName = $this->loDataObject->usernameGenerator($lcDisplayName, 8) ; // this enters a loop to generate a random username of 8 characters
						}

					}
						// now we have a unique display name
						$this->lcAccess = $this->loDataObject->passwordGenerator(9) ;
						$lcPassword = $this->loDataObject->encryptData($this->lcAccess);
						$lcValueList = '"' . trim($pcEmailAddress) . '", "' . $lcPassword . '", "' . trim($this->lcDisplayName) . '",1,1, "' . $this->ldDate . '", "' . $this->ldDate . '", "' .
										$this->ldDate . '"' ;
						$this->lnUserID = $this->loDataObject->queryInsert("tbl_user", "email,access,display,mailingList,loginCount,lastLogin,createDate,editdate", $lcValueList, true);
						$this->addToMailingList($pcEmailAddress) ;
				}
			}
			else // no valid email supplied post the review as guest
			{
				$this->lnUserID = 3 ; // this is the guest
				$this->lcDisplayName = "Guest" ;
			}
		}
	}

	function updateName($pnUserID, $pcName)
	{
		// updates the actual name in tbl_user
		if(!empty($pcName))
		{
			$lcUpdate = "UPDATE tbl_user SET name = '$pcName', editDate = now() WHERE ID = $pnUserID" ;
			$this->loDataObject->iQuery($lcUpdate) ;
		}
	}

	function unRegister()
	{
		$lnUserID = $_SESSION['userID'] ;
		$lcUpdate = "UPDATE tbl_user SET isRegistered = 1, editDate = now() WHERE ID = $lnUserID" ;
		$this->loDataObject->iQuery($lcUpdate) ;

		$lcUpdate = "UPDATE tbl_threaduser SET isSubscribed = 0, editDate = now() WHERE userID = $lnUserID" ;
		$this->loDataObject->iQuery($lcUpdate) ;
	}

	function endAccount($pnUserID)
	{
		$lcUpdateUser = "UPDATE tbl_user SET isRegistered=0, editDate=now()WHERE ID=$pnUserID" ;
		$this->loDataObject->iquery($lcUpdateUser) ;
	}

	function updateSession($pcEmail, $pcDisplayName, $pcName, $pnPost, $pnFood, $pnChurch, $pnBibleID)
	{
		$_SESSION['email']			= $pcEmail;
		$_SESSION['display']		= $pcDisplayName ;
		$_SESSION['name']			= $pcName ;
		$_SESSION['postOrder']		= $pnPost ;
		$_SESSION['foodID']			= $pnFood ;
		$_SESSION['churchID']		= $pnChurch ;
		$_SESSION['bibleRef']		= $pnBibleID ;
	}

/*------------------------------------------------------------
	NEWZAPP API INTEGRATION
------------------------------------------------------------*/
	function ensureNewZappCredentials()
	{
		$this->lcNZAPIPWord	= 'apin6908k' ;
		$this->lcNZUserName	= 'admin7238' ;
		$this->lcNZCID		= '7215' ;
	}

	function newZappCommunicator($pcNZServerURL)
	{
		$llReturn = false ; // default position

		// this sends the relevant message and passes back the response
		$loRequest =& new HTTP_Request($pcNZServerURL);
		//$loRequest->addHeader("Content-Type", "text/xml");
		//$loRequest->addHeader("Content-Length", strlen($this->lNZRequest));
		$loRequest->setMethod(HTTP_REQUEST_METHOD_POST);
		$loRequest->addRawPostData($this->lcNZRequest, true);
		$loRequest->sendRequest();

		$this->lcNZResponse	=	$loRequest->getResponseBody();

		if(strpos(strtolower($this->lcNZResponse), "true"))
		{
			$llReturn = true ;
		}

		return $llReturn ;
	}

	function getMailingListStatus()
	{
		$llReturn = false ;
		$this->ensureNewZappCredentials() ;

		// this works on $_SESSION['email']
		$lcEmailToCheck = $_SESSION['email'] ;
		$this->lcNZRequest	=	'<SOAP:Envelope xmlns:SOAP="urn:schemas-xmlsoap-org:soap.v1">' ;
		$this->lcNZRequest	.=	'<SOAP:Body>' ;
		$this->lcNZRequest	.=	'<Request>';
		$this->lcNZRequest	.=	'<CID>' . $this->lcNZCID . '</CID>';
		$this->lcNZRequest	.=	'<GetEmail>' . $lcEmailToCheck . '</GetEmail>';
		$this->lcNZRequest	.=	'<UserName>' . $this->lcNZUserName . '</UserName>';
		$this->lcNZRequest	.=	'<Password>' . $this->lcNZAPIPWord . '</Password>';
		$this->lcNZRequest	.=	'</Request>';
		$this->lcNZRequest	.=	'</SOAP:Body>';
		$this->lcNZRequest	.=	'</SOAP:Envelope>' ;

		$llReturn = $this->newZappCommunicator("https://system.newzapp.co.uk/SOAP/GetSubscriberWithAPI.asp") ;
		return $llReturn ;
	}

	function setMailingListStatus($pnMailing)
	{
		$this->ensureNewZappCredentials() ;
		$ldDate = date('d/m/Y') ;
		if($pnMailing == 1) // add to/edit the mailing list
		{
			$this->lcNZRequest	=	'<SOAP:Envelope xmlns:SOAP="urn:schemas-xmlsoap-org:soap.v1">' ;
			$this->lcNZRequest	.=	'<SOAP:Body>' ;
			$this->lcNZRequest	.=	'<Request>';
			$this->lcNZRequest	.=	'<CID>' . $this->lcNZCID . '</CID>';
			$this->lcNZRequest	.=	'<UserName>' . $this->lcNZUserName . '</UserName>';
			$this->lcNZRequest	.=	'<Password>' . $this->lcNZAPIPWord . '</Password>';
			$this->lcNZRequest	.=	'<Email>' . $_SESSION['email'] . '</Email>';
			$this->lcNZRequest	.=	'<EditDate>' . $ldDate . '</EditDate>';
			$this->lcNZRequest	.=	'</Request>';
			$this->lcNZRequest	.=	'</SOAP:Body>';
			$this->lcNZRequest	.=	'</SOAP:Envelope>' ;
			$lcURL				=	"https://system.newzapp.co.uk/SOAP/ThankyouSubscribeAPI.asp" ;
		}
		else // delete from the mailing list
		{
			$this->lcNZRequest	=	'<SOAP:Envelope xmlns:SOAP="urn:schemas-xmlsoap-org:soap.v1">' ;
			$this->lcNZRequest	.=	'<SOAP:Body>' ;
			$this->lcNZRequest	.=	'<Request>';
			$this->lcNZRequest	.=	'<CID>' . $this->lcNZCID . '</CID>';
			$this->lcNZRequest	.=	'<UserName>' . $this->lcNZUserName . '</UserName>';
			$this->lcNZRequest	.=	'<Password>' . $this->lcNZAPIPWord . '</Password>';
			$this->lcNZRequest	.=	'<DeleteEmail>' . $_SESSION['email'] . '</DeleteEmail>';
			$this->lcNZRequest	.=	'</Request>';
			$this->lcNZRequest	.=	'</SOAP:Body>';
			$this->lcNZRequest	.=	'</SOAP:Envelope>' ;
			$lcURL				=	"https://system.newzapp.co.uk/SOAP/ThankyouDeleteAPI.asp" ;
		}

		$llReturn = $this->newZappCommunicator($lcURL) ;

		return $llReturn ;
	}

	function addToMailingList($pcEmailAddress)
	{
		$this->ensureNewZappCredentials() ;
		$ldDate = date('d/m/Y') ;
		$this->lcNZRequest	=	'<SOAP:Envelope xmlns:SOAP="urn:schemas-xmlsoap-org:soap.v1">' ;
		$this->lcNZRequest	.=	'<SOAP:Body>' ;
		$this->lcNZRequest	.=	'<Request>';
		$this->lcNZRequest	.=	'<CID>' . $this->lcNZCID . '</CID>';
		$this->lcNZRequest	.=	'<UserName>' . $this->lcNZUserName . '</UserName>';
		$this->lcNZRequest	.=	'<Password>' . $this->lcNZAPIPWord . '</Password>';
		$this->lcNZRequest	.=	'<Email>' . $pcEmailAddress . '</Email>';
		$this->lcNZRequest	.=	'<Group>ChristianLeadership.org</Group>' ;
		$this->lcNZRequest	.=	'<EditDate>' . $ldDate . '</EditDate>';
		$this->lcNZRequest	.=	'</Request>';
		$this->lcNZRequest	.=	'</SOAP:Body>';
		$this->lcNZRequest	.=	'</SOAP:Envelope>' ;

		$llReturn = $this->newZappCommunicator("https://system.newzapp.co.uk/SOAP/ThankyouSubscribeToGroupAPI.asp") ;

		return $llReturn ;
	}

} // end of class definition
