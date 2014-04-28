<?php
/*
File: debugObject.php
Created: 06/03/2008
Author: Nathan Davies
Purpose: To write text file output to a user specified file using the PHP file functions.
This is really designed for trackiong variable information when the code isn't functioning correctly but 'errors' are not happening.
*/

class debugObject
{

	var $lnFileHandle ;
	var $lcOutputFrom ;
	var $ltTimeInstantiated ;
	var $lcLineBreak ;
	var $lcLineDivision ;
	var $lcOutputAuthor ;
	var $lcOutPutFileName ;
	var $lcWriteType ;

	public function debugObject($pcFromFile, $pcDivision, $pcOutputAuthor)
	{
		$this->ltTimeInstantiated	= date('Y-m-d H:i:s') ;
		$this->lcOutputFrom			= !empty($pcFromFile)?$pcFromFile:'Source not specified' ;
		$this->lcLineBreak			= "\r\n" ;
		$this->lcLineDivision		= !empty($pcDivision)?$pcDivision:'-----' ; // set a default if not user defined.
		$this->lcOutputAuthor		= !empty($pcOutputAuthor)?$pcOutputAuthor:"Unknown User" ; // enables a log history to be more accurately kept

	} // end of dataObject instantiation function

	function debugOutput($pcOutputFilePath, $pcWriteType, $pcInfoToWrite, $plNewHeader)
	{
		$llContinue = $this->createHandle($pcOutputFilePath, $pcWriteType) ;

		if($llContinue)
		{
			$lvWritten = $this->writeTofile($pcInfoToWrite, $plNewHeader) ;

			if($lvWritten === FALSE)
			{
				// not possible to write to the file
				$lnReturn = -2 ;
			}
			else
			{
				$lnReturn = $lvWritten ;
			}
		}
		else
		{
			// not possible to create the desired file
			$lnReturn = -1 ;
		}

		return $lnReturn ;

	} // end of debugOutput

	function createHandle($pcOutputFilePath, $pcWriteType)
	{
		$this->lcOutPutFileName	= !empty($pcOutputFilePath)?$pcOutputFilePath:"../debugOutput.txt" ; // default file in the directory above this
		$this->lcWriteType		= !empty($pcWriteType)?$pcWriteType:"a+" ; // default to append to the file

		$this->lnFileHandle = fopen($this->lcOutPutFileName, $this->lcWriteType) ;
		$llReturn = $this->lnFileHandle === false?false:true ; // make the return variable for easier testing in the calling code

		return $llReturn ;
	} // end of createHandle

	function ensureFileHandle()
	{
		if(!$this->lnFileHandle)
		{
			$this->createHandle($this->lcOutPutFileName, $this->lcWriteType) ;
		}

	} // end of ensureFileHandle

	function writeToFile($pcInfoToWrite, $plNewHeader)
	{
		// $pcInfoToWrite is a '||' delimited string - '||' denotes a new line is required
		$this->ensureFileHandle() ;

		// check for file contentst
		$llContainsData = $this->checkFileContents() ;

		if($llContainsData)
		{
			$lcOutputString = $this->lcLineBreak . $this->lcLineDivision . $this->lcLineBreak ;
		}

		if($plNewHeader)
		{
		 	$lcOutputString .= "Object Started: " . $this->ltTimeInstantiated . $this->lcLineBreak ;
			$lcOutputString	.= "Started By: " . $this->lcOutputAuthor . $this->lcLineBreak ;
			$lcOutputString	.= "Started In: " . $this->lcOutputFrom . $this->lcLineBreak ;
		}

		if(!empty($pcInfoToWrite))
		{
			$laInfo			= explode("||", $pcInfoToWrite) ;
			$lnArrayCount	= count($laInfo) - 1 ; // make it useful in the loop below

			for($lnLoopCount = 0 ; $lnLoopCount <= $lnArrayCount; $lnLoopCount++)
			{
				$lcOutputString .= $laInfo[$lnLoopCount] . $this->lcLineBreak ;
			}
		}
		else
		{	$lcOutputString .= 'No output information specified' . $this->lcLineBreak ;
		}

		$lvReturn = fwrite($this->lnFileHandle, $lcOutputString) ;

		return $lvReturn ;

	} // end of writeToFile

	function closeFile()
	{
		fclose($this->lnFileHandle) ;
	} // end of closeFile

	function checkFileContents()
	{
		$llReturn	 = false ;
		if(strtolower(substr($this->lcWriteType, 0,1)) == "a") // need to append, anything else needs to start the file again
		{
			if(filesize($this->lcOutputFileName > 0))
			{
				$lvContents	 = fread($this->lnFileHandle, filesize($this->lcOutputFileName)) ;
				if($lvContents)
				{
					$llReturn = true ;
				}
			}
		}
		return $llReturn ;
	} // end of checkFileContents


} // end of class definition

?>
