<?php
/*
File: lib/fileObject.php
Author: Nathan Davies
Created: 19th September 2008
Purpose: Used for uploading files.
Ammendments:
*/

class fileObject
{
	// General Properties
	var $loDataObject ;
	var $lcFileName ;
	var $lcTmpName ;
	var $lnFileSize ;
	var $lcFileExt ;
	var $lnMaxSize ;
	var $lcFileLocation ;
	var $lnArrayType ;
	var $lcTargetDirectory ;
	var $lnStatus ;

	// FTP properties
	var $ftp_connection_ID ;
	var $ftp_server ;
	var $ftp_username ;
	var $ftp_password ;
	var $ftp_isLogedIn ;
	var $ftp_change ;
	var $ftp_maxSize ;

	public function fileObject($poDB, $pnMaxSize, $pcTargetDirectory, $pnArrayType)
	{
		$this->lnMaxSize			= $pnMaxSize ;
		$this->lcTargetDirectory	= $pcTargetDirectory ;
		$this->lnStatus				= 0 ;
		$this->lnArrayType			= $pnArrayType ;
	} // end of fileObject function

	function setMainProperties($pnResourceArray, $pnMaxSize, $pcResourceTarget)
	{
		$this->lnArrayType			= $pnResourceArray ;
		$this->lnMaxSize			= $pnMaxSize ;
		$this->lcTargetDirectory	= $pcResourceTarget ;
	} // end of setMainProperties function

/*-----------------------------------------------------------------------
	FILE LOADING FUNCTIONS
-----------------------------------------------------------------------*/

	function fileUpload($pcFileName, $pcTmpName, $pnFileSize, $pcFileExt)
	{
		$this->lcFileName 			= $pcFileName ;
		$this->lcTmpName 			= $pcTmpName ;
		$this->lnFileSize 			= $pnFileSize ;
		$this->lcFileExt 			= $pcFileExt ;
		// check the type
		$llContinue = $this->checkType() ;
		// check the size
		$llContinue = $this->checkSize() ;

		if($llContinue == true)
		{
			$lcTarget_path = $this->lcTargetDirectory . $this->lcFileName;
			if(is_uploaded_file($this->lcTmpName))
		  	{
				if(move_uploaded_file($this->lcTmpName, $lcTarget_path))
				{
					$llFileLoaded	= true ;
					$this->setFileLocation($lcTarget_path) ;
				}
				else
				{
					$llFileLoaded = false ;
					$this->lnStatus = $this->lnStatus + 8 ;
				}
			}
			else
			{
				$llFileLoaded = false ;
				$this->lnStatus = $this->lnStatus + 4 ;
			}
		}

		return $llFileLoaded ;
	} // end of fileUpload function


/*-----------------------------------------------------------------------
	FTP LOADING FUNCTIONS
-----------------------------------------------------------------------*/

	function initialiseFTP($pcFTPServer, $pcFTPUser, $pcFTPPass, $pnMaxSize)
	{
		// set up the properties relating to FTP usage
		$this->ftp_server		= $pcFTPServer ;
		$this->ftp_username		= $pcFTPUser ;
		$this->ftp_password		= $pcFTPPass ;
		$this->ftp_isLoggedID	= false ;
		$this->ftp_change		= false ;
		$this->lnMaxSize		= $pnMaxSize ;
	} // end of initialiseFTP function

	function ensureFTPConnection()
	{
		$llReturn = false ;

		if(!$this->ftp_connection_ID)
		{
			$this->ftp_connection_ID = ftp_connect($this->ftp_server) ;
			if($this->ftp_connection_ID)
			{
				$llReturn = false ;
			}
		}

		return $llReturn ;
	} // end of ensureFTPConnection function

	function FTPClose()
	{
		if($this->ftp_connection_ID)
		{
			ftp_close($this->ftp_connection_ID) ;
		}
	} // end of FTPClose function()

	function FTPLogin()
	{
		$llLogin = false ;
		// this should ensure there is a connection first
		if(	$this->ensureFTPConnection())
		{
			$llReturn = @ftp_login($this->ftp_connection_ID, $this->ftp_username, $this->ftp_password);
			$this->ftp_isLoggedIn = $llReturn ;
		}

		return $llReturn ;
	} // end of FTPLogin function

	function FTPUpload($pcDdestination_file, $pcSource_file, $pnFileSize, $pcFileExt)
	{
		$llUpload = false ;

		if(!$this->ftp_isLoggedIn)
		{
			$this->FTPLogin() ;
		}
		$this->lnFileSize 			= $pnFileSize ;
		$this->lcFileExt 			= $pcFileExt ;

		// check the type
		$llContinue = $this->checkType() ;
		// check the size
		$llContinue = $this->checkSize() ;
		if($llContinue)
		{
			$llUpload = ftp_put($this->ftp_connection_ID, $pcDdestination_file, $pcSource_file, FTP_BINARY);
			if($llUpload)
			{
				$this->ftp_change = ftp_site($this->ftp_connection_ID, "chmod777".$this->file_destination_file ) ;
				$this->setFileLocation($pcDdestination_file) ;
			}
		}
		return $llUpload ;
	} // end of FTPUpload function


/*-----------------------------------------------------------------------
	ANCILLIARY FUNCTIONS
-----------------------------------------------------------------------*/

	function checkType()
	{
		$llTypeValid = true ;
		switch($this->lnArrayType)
		{
			case 1:
				$laExtList = array (
				'zip'	=> 'application/zip',
				'pdf'	=> 'application/pdf',
				'doc'	=> 'application/msword',
				'xls' 	=> 'application/vnd.ms-excel',
				'ppt' 	=> 'application/vnd.ms-powerpoint',
				'exe' 	=> 'application/octet-stream',
				) ;
				break ;
			case 2:
				$laExtList = array (
				'gif'	=> 'image/gif',
				'png' 	=> 'image/png',
				'jpg' 	=> 'image/jpeg',
				'jpeg'	=> 'image/jpeg',
				'pjpeg'	=> 'image/pjpeg',
				) ;
				break ;
			case 3:
				$laExtList = array (
				'mp3'	=> 'audio/mpeg',
				'm3u'	=> 'audio/x-mpegurl',
				'wav'	=> 'audio/x-wav',
				) ;
				break ;
			case 4:
				$laExtList = array (
				'mpeg'	=> 'video/mpeg',
				'mpg'	=> 'video/mpeg',
				'mpe'	=> 'video/mpeg',
				'mov'	=> 'video/quicktime',
				'avi'	=> 'video/x-msvideo'
				) ;
				break ;
			case 5:
				$laExtList = array (
				'txt'	=> 'text/plain'
				) ;
				break ;
			default:
				$laExtList = array (
				'zip'	=> 'application/zip',
				'pdf'	=> 'application/pdf',
				'doc'	=> 'application/msword',
				'xls' 	=> 'application/vnd.ms-excel',
				'ppt' 	=> 'application/vnd.ms-powerpoint',
				'exe' 	=> 'application/octet-stream',
				'gif'	=> 'image/gif',
				'png' 	=> 'image/png',
				'jpg' 	=> 'image/jpeg',
				'jpeg'	=> 'image/jpeg',
				'pjpeg'	=> 'image/pjpeg',
				'mp3'	=> 'audio/mpeg',
				'm3u'	=> 'audio/x-mpegurl',
				'wav'	=> 'audio/x-wav',
				'mpeg'	=> 'video/mpeg',
				'mpg'	=> 'video/mpeg',
				'mpe'	=> 'video/mpeg',
				'mov'	=> 'video/quicktime',
				'avi'	=> 'video/x-msvideo',
				'txt'	=> 'text/plain'
				) ;
				break ;
		}
		// check the file type is valid
		if(!in_array($this->lcFileExt, $laExtList))
		{
			$this->lnStatus = $this->lnStatus + 1 ;
			$llTypeValid = false ;
		}

		return $llTypeValid ;
	} // end of checkType function

	function checkSize()
	{
		// check the size of the file is within the preset limit
		$llSizeValid = true ;
		if($this->lnFileSize > $this->lnMaxSize)
		{
			$this->lnStatus = $this->lnStauts + 2 ;
			$llSizeValid = false ;
		}

		return $llSizeValid ;
	} // end of checkSize function

	function checkURL($pcURL, $pcReturnKey)
	{
		// check the supplied URL and return the value associated with the specified key
		$laHeaders = @get_headers($pcURL, 1) ; // return the array with keys set

		$lvReturn = $laHeaders[$pcReturnKey] ;
		return $lvReturn ;
	} // end of checkURL function

	function parseFileError($pnErrorNumber)
	{
		switch($pnErrorNumber)
		{
			case 1: // bad type
				$lcReturn = 'Selected file is not of the correct type' ;
				break ;
			case 2: // bad size
				$lcReturn = 'Selected file exceeds the maximum file size of ' . ($this->lnMaxSize/1024/1024) . 'Mb' ;
				break ;
			case 3: // bad size and bad type
				$lcReturn = $this->parseFileError(1) . '<br />' . $this->parseFileError(2) ;
				break ;
			case 4: // upload failed
				$lcReturn = 'The file is fine but for some reason it was not possible to upload it at this time' ;
				break ;
			case 5:
				$lcReturn = $this->parseFileError(1) . '<br />' . $this->parseFileError(4) ;
				break ;
			case 6:
				$lcReturn = $this->parseFileError(2) . '<br />' . $this->parseFileError(4) ;
				break ;
			case 7:
				$lcReturn = $this->parseFileError(3) . '<br />' . $this->parseFileError(4) ;
				break ;
			case 8: // move failed
				$lcReturn = 'The file was uploaded but it could not be moved to the target location' ;
				break ;
		}
		return $lcReturn ;
	} // end of parseFileError function

	function setFileLocation($pcDdestination_file)
	{
		$lnEndPos		= strpos($pcDdestination_file, '/htdocs/') ; // this is the start of the string
		$lnEndPos		= $lnEndPos + 7;
		$lcFilePath		= "http://" . $_SERVER['HTTP_HOST'] . substr($pcDdestination_file, $lnEndPos) ;

		$this->lcFileLocation = $lcFilePath ;
	} // end of setFileLocation function

	function imageResize($pcImagePath, $pnTargetSize, $pcTitle, $pcAlt, $pcClass, $pcType)
	{
		$laImage	= getimagesize($_SERVER['DOCUMENT_ROOT'] . '/' . $pcImagePath) ;
		$lnWidth	= $laImage[0] ;
		$lnHeight	= $laImage[1] ;

		//takes the larger size of the width and height and applies the formula accordingly...this is so this script will work dynamically with any size image
		switch($pcType)
		{
			case "w" : // reset the width
				$lnPercentage	=	($pnTargetSize / $lnWidth);
				$lnTestSize		=	$lnWidth ;
				break ;
			case "h" : // reset the height
				$lnPercentage	=	($pnTargetSize / $lnHeight);
				$lnTestSize		=	$lnHeight ;
				break ;
			case "r" : // reset the biggest dimension retaining aspect ratio
				if ($lnWidth > $lnHeight)
				{
					$lnPercentage	=	($pnTargetSize / $lnWidth);
					$lnTestSize		=	$lnWidth ;
				}
				else
				{
					$lnPercentage	=	($pnTargetSize / $lnHeight);
					$lnTestSize		=	$lnHeight ;
				}
				break ;
		}


		//gets the new value and applies the percentage, then rounds the value
		if($lnTestSize > $pnTargetSize) // only resize down
		{
			$lnWidth	= round($lnWidth * $lnPercentage);
			$lnHeight	= round($lnHeight * $lnPercentage);
		}

		//returns the complete image tag
		$lcImageTag	=	'<img src="' . $pcImagePath . '" title="' . $pcTitle . '" alt="' . $pcAlt	.'" ' ;
		if(!empty($pcClass))
		{
			$lcImageTag	.=	'class="' . $pcClass . '" ' ;
		}

		$lcImageTag .=	'height="' . strVal($lnHeight) . '" width="' . strVal($lnWidth) .'" />' ;

		return $lcImageTag ;

	} // end of imageResizeFunction

} // end of class definition - fileObject
?>
