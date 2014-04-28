<?php
/*
File: rssObject.php
Created: 24th October 2008
Author: Nathan Davies
Purpose: To generate RSS feeds
*/

class rssObject
{
	// general properties
	var $loDataObject ;
	var $lcOutputFile ; // should include the directory
	var $lnOutputLimit ; // 0 = no limit clause is required
	var $lcWriteType ; // default is "w+" - overwrite the file contents
	var $lnFileHandle ;

	// feed properties
	var $lnSourceType ; // 1 = news, 2 = event
	var $lcAuthor ;
	var $ldPublishDate ; // 12 Aug 2008 13:30:56 GMT
	var $lcFeedTitle ;
	var $lcFeedDescription ;
	var $lcWebSiteURL ; // main page of the site
	var $lcCopyright ;
	var $lcManagingEditor ;
	var $lcLanguage ;
	var $lcWebMaster ;
	var $lcRSSVersion ;
	var $lcFeedCategory ;

	// item properties
	var $lcItemTitle ;
	var $lcItemDescription ;
	var $ldItemPublishDate ;
	var $lcItemURL ; // link to the exact page the item represents, also used as guid permalink="true"

/*---------------------------------------------------------------
	INSTANTIATION AND SET UP FUNCTIONS
---------------------------------------------------------------*/

	public function rssObject($poDB, $pcOutputFile, $pnOutputLimit, $pcWriteType)
	{
		$this->loDataObject		= $poDB ;
		$this->lcOutputFile		= $pcOutputFile ;
		$this->lnOutputLimit	= $pnOutputLimit ;
		$this->lcWriteType		= !empty($pcWriteType)?$pcWriteType:"w+" ;
	} // end of rssObject function

	function setFeedProperties($pnSourceType, $pcAuthor, $pdPublishDate, $pcFeedTitle, $pcFeedDescription, $pcWebSiteURL, $pcCopyright, $pcManagingEditor, $pcLanguage, $pcWebMaster, $pcRSSVersion, $pcFeedCategory)
	{
		$this->lnSourceType			= $pnSourceType ;
		$this->lcAuthor				= !empty($pcAuthor)?$pcAuthor:'Nathan Davies' ;
		$this->ldPublishDate		= $pdPublishDate; // 12 aug 2008 13:30:56 GMT
		$this->lcFeedTitle			= $pcFeedTitle ;
		$this->lcFeedDescription	= $pcFeedDescription ;
		$this->lcWebSiteURL			= !empty($pcWebSiteURL)?$pcWebSiteURL:"http://www.christianleadership.org" ; // main page of the site
		$this->lcCopyright			= $pcCopyright ;
		$this->lcManagingEditor		= !empty($pcManagingEditor)?$pcManagingEditor:'Nathan.Davies@christianleadership.org (Nathan Davies)' ;
		$this->lcLanguage			= !empty($pcLanguage)?$pcLanguage:'en' ;
		$this->lcWebMaster			= !empty($pcWebMaster)?$pcWebMaster:'Nathan.Davies@christianleadership.org (Nathan Davies)' ;
		$this->lcRSSVersion			= !empty($pcRSSVersion)?$pcRSSVersion:"2.0" ;
		$this->lcFeedCategory		= $pcFeedCategory ;
	} // end of setFeedProperties function

	function setItemProperties($pcItemTitle, $pcItemDescription, $pdItemPublishDate, $pcItemURL)
	{
		$this->lcItemTitle			= $pcItemTitle ;
		$this->lcItemDescription	= $pcItemDescription ;
		$this->ldItemPublishDate	= $pdItemPublishDate ;
		$this->lcItemURL			= $pcItemURL ;
	} // end of setItemProperties function

/*---------------------------------------------------------------
	FILE PREPARATION FUNCTIONS
---------------------------------------------------------------*/

	function createHandle()
	{
		$this->lnFileHandle = fopen($this->lcOutputFile, $this->lcWriteType) ;
		$llReturn = $this->lnFileHandle === false?false:true ; // make the return variable for easier testing in the calling code

		return $llReturn ;
	} // end of createHandle function

	function ensureFileHandle()
	{
		$llReturn = true ;
		if(!$this->lnFileHandle)
		{
			$llReturn = $this->createHandle($this->lcOutputFile, $this->lcWriteType) ;
		}

		return $llReturn ;

	} // end of ensureFileHandle function

	function closeFile()
	{
		fclose($this->lnFileHandle) ;
	} // end of closeFile function

	function writeToFile($pcInfoToWrite)
	{
		if($this->ensureFileHandle())
		{
			if(!empty($pcInfoToWrite))
			{
				$lvReturn = fwrite($this->lnFileHandle, $pcInfoToWrite) ;
			}
			else
			{
				$lvReturn = false ;
			}
		}
		return $lvReturn ;

	} // end of writeToFile function

/*---------------------------------------------------------------
	CONTENT GENERATION FUNCTIONS
---------------------------------------------------------------*/

	function generateFeed()
	{
		// define the SQL based on the $lnSourceType property
		switch($this->lnSourceType)
		{
			case 1: // news feed
				$lcFeedSelect =
				"SELECT
				a.ID, a.title, a.summary, 3 as para1,
				CONCAT(DATE_FORMAT(a.createDate, '%d %b %Y' ), ' 12:00:00 GMT' ) AS itemPublishDate
				FROM tbl_news a
				WHERE a.expiryDate >= now() and a.publishDate <= now() and isLive = 1" ;
				break ;
			case 2: // events feed
				$lcFeedSelect =
				"SELECT
				a.ID, a.title, a.summary, 8 as para1,
				CONCAT(DATE_FORMAT(a.createDate, '%d %b %Y' ), ' 12:00:00 GMT' ) AS itemPublishDate
				FROM tbl_event a
				WHERE a.expiryDate >= now() and a.publishDate <= now() and isLive = 1 AND a.startDate > now()" ;
				break ;
		}

		if($this->lnOutputLimit > 0)
		{
			$lnLimit = $this->lnOutputLimit ;
			$lcFeedSelect .= " ORDER BY a.publishDate DESC LIMIT 0, $lnOutputLimit" ; // limit the output to the most recent x entries
		}

		// get the data
		$laFeedData = $this->loDataObject->queryGetData(true, $lcFeedSelect) ;

		if($laFeedData)
		{
			// write the header
			$lcFeed = $this->writeFeedHeader() ;
			foreach($laFeedData as $laFeedRow)
			{
				$lcURL = "http://www.christianleadership.org/#para1=" . $laFeedRow['para1'] . "|para2=" . $laFeedRow['ID'] ;
				$this->setItemProperties($laFeedRow['title'], $laFeedRow['summary'], $laFeedRow['itemPublishDate'], $lcURL) ;
				$lcFeed .= $this->writeFeedItem() ;
			}

			$lcFeed .= $this->writeFeedFooter() ;

			$lvReturn = $this->writeToFile($lcFeed) ;
		}
		else
		{
			$lvReturn = false ;
		}

		$this->closeFile($this->lnFileHandle) ;

		return $lvReturn ;
	} // end of generateFeed function

	function writeFeedHeader()
	{
		$lcFeedHeader = '<?xml version="1.0" encoding="UTF-8"?>
						<rss version="' . $this->lcRSSVersion . '">
						<channel>
						<generator>rssObject</generator>' ;

		$lcFeedHeader .= '<pubDate>' . $this->ldPublishDate . '</pubDate>' ;
		$lcFeedHeader .= '<title>' . $this->lcFeedTitle . '</title>' ;
		$lcFeedHeader .= '<description>' . $this->lcFeedDescription .'</description>' ;
		$lcFeedHeader .= '<link>' . $this->lcWebSiteURL . '</link>' ;
		$lcFeedHeader .= '<copyright>' . $this->lcCopyright . '</copyright>' ;
		$lcFeedHeader .= '<managingEditor>' . $this->lcManagingEditor . '</managingEditor>';
		$lcFeedHeader .= '<category>' . $this->lcFeedCategory . '</category>' ;
		$lcFeedHeader .= '<language>' . $this->lcLanguage . '</language>' ;
		$lcFeedHeader .= '<webMaster>' . $this->lcWebMaster . '</webMaster>' ;

		return $lcFeedHeader ;
	} // end of setFeedHeader function

	function writeFeedItem()
	{
		$lcFeedItem = '<item>' ;
		$lcFeedItem .= '<title>' . $this->lcItemTitle . '</title>' ;
		$lcFeedItem .= '<description>' . $this->lcItemDescription . '</description>' ;
		$lcFeedItem .= '<pubDate>' . $this->ldItemPublishDate . '</pubDate>' ;
		$lcFeedItem .= '<link>' . $this->lcItemURL . '</link>' ;
		$lcFeedItem .= '<guid isPermaLink="true">' . $this->lcItemURL . '</guid>' ;
		$lcFeedItem .= '</item>' ;

		return $lcFeedItem ;
	} // end of writeFeedItem function

	function writeFeedFooter()
	{
		$lcFeedFooter =	'</channel></rss>' ;

		return $lcFeedFooter ;
	} // end of writeFeedFooter function

} // end of class definition

?>