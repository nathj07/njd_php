<?php
/*
File: lib/formobject.php
Author: Nathan Davies
Created: 13/03/08
Purpose: Used to build forms rapidly
Ammendments: 4th December 2008 Nathan Davies Added defineTimeSelection function
Comment:	Each function returns just what it says - there are no containing divs added, this is the responsibilty of the calling code, but an function here can be used
*/

class formObject
{
	var $loDataObject ;

	public function formObject($poDB)
	{
		$this->loDataObject = $poDB ;
	} // end of formObject function

	function buildFormInput($pnFormat, $pcContainerID, $pcLabelID, $pcLabelFor, $pcLabel, $pcInputID, $pcInput)
	{
		switch($pnFormat)
		{
			case 1: // standard input with a label
				$lcReturn = '<div class="row" id="' .$pcContainerID .'" >
							<span class="label" id="' .$pcLabelID . '"><label for="' . $pcLabelFor . '">' . $pcLabel . '</label></span> ' ;
				$lcReturn .= '<span class="formw" id="' . $pcInputID . '">' . $pcInput . '</span></div>' ;
				break ;
			case 2: // a form spacer
				$lcReturn = '<div class="row"></div>';
				break ;
			case 3: // button container
				$lcReturn = '<div class="row" id="' . $pcContainerID . '" ><span class="label"></span>' ;
				$lcReturn .= '<span class="formw" id="' . $pcInputID . '">' . $pcInput . '</span></div>' ;
				break ;
			case 4: // standard input with a warning label
				$lcReturn = '<div class="row" id="' .$pcContainerID .'" >
							<span class="label" id="' .$pcLabelID . '"><span class="warninglabel"><label for="' . $pcLabelFor . '">' . $pcLabel . '</label></span></span> ' ;
				$lcReturn .= '<span class="formw" id="' . $pcInputID . '">' . $pcInput . '</span></div>' ;
				break ;
			case 5: // standard input without a label
				$lcReturn = '<div class="row" id="' .$pcContainerID .'" >' ;
				$lcReturn .= '<span class="formw" id="' . $pcInputID . '">' . $pcInput . '</span></div>' ;
				break ;
			case 6: // scrolling div to handle mutiple related items such as check boxes - alternative to the multi select via ctrl key
				$lcReturn = '<div class="row" id="' .$pcContainerID .'" >
							<span class="label" id="' .$pcLabelID . '"><label for="' . $pcLabelFor . '">' . $pcLabel . '</label></span> ' ;
				$lcReturn .= '<span class="formw" id="' . $pcInputID . '">' ;
				$lcReturn .= '<div class="multiselect" id="' . $pcLabelFor .'">'	 . $pcInput . '</div></span></div>' ;
				break ;
		}

		return $lcReturn ;
	} // end of buildFormInput

	function defineDateSelection($plSetDefault, $pcIDSuffix, $pdDate, $plIsReadOnly, $pnTabIndex)
	{
		// $plSetDefault - True = default of current date or the date supplied in the third parameter, False = default of start of current year as the event system doesn't allow you to schedule past events
		// $pcIDSuffix - The end of the ID, all elements retruned hve the same base they then have a different suffix, recommend '_ClarifyingName'
		// $pdDate - The date that neeeds to be set

		if($plSetDefault)
		{
			if(!empty($pdDate))
			{
				$lnCurrentDay	= date('j', mktime(0,0,0,substr($pdDate, 5, 2), substr($pdDate, 8, 2), substr($pdDate, 0,4))) ;
				$lnCurrentMonth = date('n', mktime(0,0,0,substr($pdDate, 5, 2), substr($pdDate, 8, 2), substr($pdDate, 0,4))) ;
				$lnCurrentYear	= date('Y', mktime(0,0,0,substr($pdDate, 5, 2), substr($pdDate, 8, 2), substr($pdDate, 0,4))) ;
			}
			else
			{
				$lnCurrentDay 	= date('j') ;
				$lnCurrentMonth = date('n') ;
				$lnCurrentYear	= date('Y') ;
			}
		}
		else
		{
			$lnCurrentDay 	= 1 ;	// first day of the month
			$lnCurrentMonth = date('n'); // the next month - so there are no past events
			$lnCurrentYear	= date('Y') ;
		}

		$lnYear = intVal(date('Y'));

		$lcDayID		= "cbo_datePickerDay" . $pcIDSuffix ;
		$lcMonthID		= "cbo_datePickerMonth" . $pcIDSuffix ;
		$lcYearID		= "cbo_datePickerYear" . $pcIDSuffix ;
		$lcDayName		= "cbo_datePickerDay" . $pcIDSuffix . "_name" ;
		$lcMonthName	= "cbo_datePickerMonth" . $pcIDSuffix . "_name" ;
		$lcYearName		= "cbo_datePickerYear" . $pcIDSuffix . "_name" ;

		$lcDefinition = '<select class="dayPicker" id="' . $lcDayID . '" name="' . $lcDayName . '" title="Select the day" tabindex="' . $pnTabIndex . '">' ;
		for($i=1; $i<=31; $i++)
		{
			$lcDefinition .= '<option value="' . $i .'"';
			if($lnCurrentDay == $i)
			{
				$lcDefinition .= ' selected="selected" ' ;
			}
			$lcDefinition .= '>' . $i . ' </option>' ;

		}

		$lcDefinition .= '</select>' ;
		$lnTabIndex = $pnTabIndex + 1 ;
		$lcDefinition .= '<select class="monthPicker" id="' . $lcMonthID . '" name="' . $lcMonthName .'" title="Select the month" tabindex="' . $lnTabIndex . '">' ;
		for($i=1; $i<=12; $i++)
		{
			$lcDefinition .= '<option value="' . $i .'"';
			if($lnCurrentMonth == $i)
			{
				$lcDefinition .= ' selected="selected" ' ;
			}

			$lcDefinition .= '>'  . date('F', mktime(0, 0, 0, $i, 1)) . '</option>' ;
		}

		$lcDefinition .= '</select>' ;

		$lnTabIndex = $pnTabIndex + 1 ;
		$lcDefinition .= '<select class="yearPicker" id="' . $lcYearID . '" name="' . $lcYearName . '" title="Select the year" tabindex="' . $lnTabIndex .'">' ;

		for($i=0; $i<=10; $i++)
		{
			$lnYearToAdd = $lnYear + $i ;
			$lcDefinition .= '<option value="' . $lnYearToAdd . '"' ;
			if($lnYearToAdd == $lnCurrentYear)
			{
				$lcDefinition .= ' selected="selected" ' ;
			}
			$lcDefinition .=  '">' . $lnYearToAdd. '</option>' ;
		}
		$lcDefinition .= '</select>' ;

		return $lcDefinition ;
	} // end of defineDateSelection

	function defineTimeSelection($plSetDefault, $pcIDSuffix, $ptTime, $plIsReadOnly, $pnTabIndex)
	{
		// $plSetDefault - True = default of current time or the date supplied in the third parameter, False = default of midnight
		// $pcIDSuffix - The end of the ID, all elements retruned hve the same base they then have a different suffix, recommend '_ClarifyingName'
		// $ptTime - The time that neeeds to be set

		if($plSetDefault)
		{
			if(!empty($ptTime))
			{

				$lnCurrentHour		= date('H', mktime(intVal(substr($ptTime, 0, 2)), 0, 0, 0, 0, 0)) ;
				$lnCurrentMinute	= date('i', mktime(0, intVal(substr($ptTime, 3, 2)), 0, 0, 0, 0)) ;

			}
			else
			{
				$lnCurrentHour		= date('H') ;
				$lnCurrentMinute	= date('i') ;
			}
		}
		else
		{
			$lnCurrentHour		= 00 ;
			$lnCurrentMinute	= 00 ; // default if nothing set is midnight
		}

		$lnYear = intVal(date('Y'));

		$lcHourID		= "cbo_datePickerHour" . $pcIDSuffix ;
		$lcMinuteID		= "cbo_datePickerMinute" . $pcIDSuffix ;
		$lcHourName		= "cbo_datePickerHour" . $pcIDSuffix . "_name" ;
		$lcMinuteName	= "cbo_datePickerMinute" . $pcIDSuffix . "_name" ;


		$lcDefinition = '<select class="dayPicker" id="' . $lcHourID . '" name="' . $lcHourName . '" title="Select the hour" tabindex="' . $pnTabIndex . '">' ;
		for($i=0; $i<=23; $i++)
		{
			$lcDefinition .= '<option value="' . $i .'"';
			if(intVal($lnCurrentHour) == $i)
			{
				$lcDefinition .= ' selected="selected" ' ;
			}
			$lcDefinition .= '>' . str_pad(strVal($i), 2, '0', STR_PAD_LEFT) . ' </option>' ;

		}

		$lcDefinition .= '</select>' ;
		$lnTabIndex = $pnTabIndex + 1 ;
		$lcDefinition .= '<select class="dayPicker" id="' . $lcMinuteID . '" name="' . $lcMinuteName .'" title="Select the minutes" tabindex="' . $lnTabIndex . '">' ;
		for($i=0; $i<=59; $i++)
		{
			$lcDefinition .= '<option value="' . $i .'"';
			if(intVal($lnCurrentMinute) == $i)
			{
				$lcDefinition .= ' selected="selected" ' ;
			}

			$lcDefinition .= '>'  . str_pad(strVal($i), 2, '0', STR_PAD_LEFT) . '</option>' ;
		}

		$lcDefinition .= '</select>' ;

		return $lcDefinition ;
	} // end of defineTimeSelection

	function createInputSelect($pnRetreivalType, $pnReturnType, $pcSelectID, $pnSelection, $plIsReadOnly, $pcExtra, $pcClass, $pnTabIndex)
	{
		switch($pnRetreivalType)
		{
			case 1: // get rating
				$lcSelectStr =
				"SELECT
				a.ID, a.description, a.isDefault
				FROM tbl_rating a
				ORDER BY a.ID ASC";
				break;
			case 2: // get food options
				$lcSelectStr =
				"SELECT
				a.ID, a.description, a.isDefault
				FROM tbl_food a
				ORDER BY a.ID ASC" ;
				break ;
			case 3: // get Church names
				$lcSelectStr =
				"SELECT
				a.ID, concat(a.name, '(' , a.town, ')') as description, 0 as isDefault
				FROM tbl_church a
				ORDER BY a.name ASC, a.town ASC" ;
				break ;
			case 4: // get a list of Forums - excluding the one a thread ia currently in
				$lcSelectStr =
				"SELECT
				a.ID, a.title as description, 0 as isDefault
				FROM tbl_forum a
				WHERE a.ID NOT IN (
				SELECT b.forumID FROM tbl_thread b WHERE b.ID = $pnSelection)
				ORDER BY a.title ASC" ;
				break ;
			case 5: // get the list of available Bible translations
				$lcSelectStr =
				"SELECT
				a.refID as ID, a.description, a.isDefault
				FROM tbl_bible a
				ORDER by a.description ASC" ; // note use REF ID as this is saved against user to aid delivery of this system
				break ;
			case 6: // get the list of sermon topics
				$lcSelectStr =
				"SELECT
				a.ID, a.title as description , 0 as isDefault
				FROM tbl_topic a
				ORDER by a.title ASC";
				break ;
			case 7: // get the list of links for admin panel so even isLive=0 are loaded
				$lcSelectStr =
				"SELECT a.ID, a.title as description, 0 as IsDefault
				FROM tbl_links a
				ORDER BY a.title ASC" ;
				break ;
			case 8: // get the lost of forums for admin panel
				$lcSelectStr =
				"SELECT a.ID, a.title as description, 0 as isDefault
				FROM tbl_forum a
				ORDER BY a.title ASC" ;
				break ;
			case 9: // get the list of resource types
				$lcSelectStr =
				"SELECT a.ID, a.title as description, 0 as isDefault
				FROM tbl_resourcetype a
				ORDER BY a.title ASC" ;
				break ;
			case 10: // get the downloads for a specifc type
				$lcSelectStr =
				"SELECT a.ID, a.title as description, 0 as isDefault
				FROM tbl_resource a
				WHERE a.resourceTypeID = $pnSelection AND a.isGeneric = 1
				ORDER BY a.title ASC";
				$pnSelection = 0 ;
				break ;
			case 11: // get the list of speakers
				$lcSelectStr =
				"SELECT a.ID, a.name as description, 0 as isDefault
				FROM tbl_speaker a
				ORDER BY a.name ASC" ;
				break ;
			case 12: // get the headline and ID for each news item - this is for the admin area
				$lcSelectStr =
				"SELECT a.ID, a.title as description, 0 as isDefault
				FROM tbl_news a
				ORDER BY a.title ASC" ;
				break ;
			case 13: // get the title and ID for each event - this is for the admin area
				$lcSelectStr =
				"SELECT a.ID, a.title as description, 0 as isDefault
				FROM tbl_event a
				ORDER BY a.title ASC" ;
				break ;
			case 14: // list of venues
				$lcSelectStr =
				"SELECT a.ID, a.name as description, 0 as isDefault
				FROM tbl_venue a
				ORDER BY a.name ASC	" ;
				break ;
		}

		// execute the select
		$laResults	= $this->loDataObject->queryGetData(false, $lcSelectStr);
		$lcItemID	= !empty($pcSelectID)? $pcSelectID : "list" . $pnRetreivalType ;
	   	switch($pnReturnType)
	   	{
			case 1: // write the results into a select input object
				$lvReturn =  "<select id=" . "'" . $lcItemID . "' " ;
				if(!empty($pcExtra))
				{
					$lvReturn .= $pcExtra . " " ;
				}

				if(!empty($pcClass))
				{
					$lvReturn .= "class='" . $pcClass ."' " ;
				}

				$lvReturn.= " tabindex='" . $pnTabIndex . "'>";

				foreach($laResults as $lcDataLine)
				{
					$lvReturn .= "<option value=" . $lcDataLine['ID'] . " ";

					if($pnSelection >= 0)
					{
						if($lcDataLine['ID'] == $pnSelection)
						{
							$lvReturn .= "selected='selected'";
						}
					}
					else
					{
						if($lcDataLine['isDefault'])
						{
							$lvReturn .= "selected='selected'";
						}
					}
					$lvReturn .= ">" . $lcDataLine['description'] ."</option>";
				}
				$lvReturn .= "</select>";
				break;
			case 2: // return the array from the query
				$lvReturn = $laResults ;
		}

		return $lvReturn ;
	} // end of createInputSelect

	function enumToInputSelect($pcTable, $pcENUMColumn, $pcListID, $pcSelection, $plIsReadOnly, $pcExtra, $pcClass, $pnTabIndex)
	{
		$lcGetENUM	= "SHOW COLUMNS FROM $pcTable LIKE '$pcENUMColumn'";
		$laENUM		= $this->loDataObject->queryGetData(false, $lcGetENUM) ;

		$lcEnumData	= $laENUM[0]['Type'] ;
		$lnStartPos	= strpos($lcEnumData,"(") + 1 ;
		$lnEndPos	= strpos($lcEnumData, ")");

		$lcEnumData	= substr($lcEnumData, $lnStartPos, $lnEndPos-$lnStartPos) ;
		$lcEnumData = str_replace("'", "", $lcEnumData) ;
		$laEnumData	= explode(",", $lcEnumData) ;

		if(empty($pcListID))
		{
			$pcListID = "list" . $pcENUMcolumn ;
		}

		$lcReturn =  "<select id='" . $pcListID . "' " ;

		if(!empty($pcExtra))
		{
			$lcReturn .= $pcExtra . " " ;
		}

		if(!empty($pcClass))
		{
			$lcReturn .= "class='" . $pcClass ."' " ;
		}

		$lcReturn	.= "tabindex='" . $pnTabIndex . "'>";
		$lnCount	= count($laEnumData) - 1 ;
		for($lnPntr = 0; $lnPntr <= $lnCount; $lnPntr++)
		{
			$lnValue = $lnPntr + 1 ;
			$lcReturn .= "<option value=" . $lnValue ;
			if($pcSelection == $laEnumData[$lnPntr])
			{
				$lcReturn .= " selected='selected'" ;
			}
			$lcReturn .= " >" . $laEnumData[$lnPntr] ."</option>";
		}
		$lcReturn .= "</select>";

		return $lcReturn ;
	} // end of enumToInputSelect

	function defineSelectFixedValues($pcElementID, $pcValueList, $pcTitle, $pcExtra, $pnSelectedItem, $plIsReadOnly, $pcClass, $pnTabIndex)
	{
		$laValueList = explode("||", $pcValueList) ;

		$lcReturn = '<select id="' . $pcElementID .'" name="' . $pcElementID . '" title="' . $pcTitle . '" '  ;
		if(!empty($pcExtra)) // extra options specified - used for code such as onchange event handlers
		{
			$lcReturn .= $pcExtra . ' ' ;
		}

		if(!empty($pcClass)) // there is a CSS class to apply to this
		{
			$lcReturn .= 'class="' . $pcClass . '" ' ;
		}

		$lcReturn	.=	'tabindex="' . $pnTabIndex . '">' ;
		$lnCount	=	count($laValueList) - 1 ;

		for($lnPntr=0; $lnPntr <= $lnCount; $lnPntr++)
		{
			$laValueDetails = explode("^^", $laValueList[$lnPntr]) ;
			$lcReturn .= '<option value="' . $laValueDetails[0] . '" ' ;

			if($pnSelectedItem === $lnPntr)
			{
				$lcReturn .= 'selected="selected" ' ;
			}
			$lcReturn .= '>' . $laValueDetails[1] . '</option>' ;
		}

		$lcReturn .= '</select>' ;

	} // end of defineSelectFixedValues

	function defineInputText($pcElementID, $pcValue, $pcTitle, $pnMaxLength, $pnSize, $pcExtra, $pcClass, $plIsReadOnly, $pnTabIndex)
	{
		$lcReturn =	'<input id="' . $pcElementID . '"
					type="text"
					title="' . $pcTitle . '"
					value="' . $pcValue . '"
					maxlength="' . $pnMaxLength . '"
					size="' . $pnSize . '"
					tabindex="' . $pnTabIndex .'" ' ;

		if(!empty($pcExtra)) // extra options specified - used for code such as onchange event handlers	or supplying a name for the control
		{
			$lcReturn .= $pcExtra . ' ' ;
		}

		if(!empty($pcClass)) // there is a CSS class to apply to this
		{
			$lcReturn .= 'class="' . $pcClass . '"'  ;
		}

		if($plIsReadOnly)
		{
			$lcReturn .= 'readonly="readonly" ' ;
		}

		$lcReturn .='/>' ;

		return $lcReturn ;
	} // end of defineInputText

	function defineInputPassword($pcElementID, $pcValue, $pcTitle, $pnMaxLength, $pnSize, $pcExtra, $pcClass, $plIsReadOnly, $pnTabIndex)
	{
		$lcReturn =	'<input id="' . $pcElementID . '"
					type="password"
					title="' . $pcTitle . '"
					value="' . $pcValue . '"
					maxlength="' . $pnMaxLength . '"
					size="' . $pnSize . '"
					tabindex="' . $pnTabIndex .'" ' ;

		if(!empty($pcExtra)) // extra options specified - used for code such as onchange event handlers
		{
			$lcReturn .= $pcExtra . ' ' ;
		}

		if(!empty($pcClass)) // there is a CSS class to apply to this
		{
			$lcReturn .= 'class="' . $pcClass . '" ' ;
		}

		if($plIsReadOnly)
		{
			$lcReturn .= 'readonly="readonly"' ;
		}

		$lcReturn .='/>' ;

		return $lcReturn ;
	} // end of defineInputText

	function defineInputFile($pcElementID, $pcValue, $pcTitle, $pnMaxSize, $pnSize, $pcExtra, $pcClass, $plIsReadOnly, $pnTabIndex, $pcSizeSuffix)
	{
		$lcReturn =		'<input type="hidden" name="MAX_FILE_SIZE' . $pcSizeSuffix . '" value="' . $pnMaxSize . '" />' ;
		$lcReturn .=	'<input id="' . $pcElementID . '"
						type="file"
						title="' . $pcTitle . '"
						value="' . $pcValue . '"
						size="' . $pnSize . '"
						tabindex="' . $pnTabIndex . '" ' ;

		if(!empty($pcExtra)) // extra options specified - used for code such as onchange event handlers
		{
			$lcReturn .= $pcExtra . ' ' ;
		}

		if(!empty($pcClass)) // there is a CSS class to apply to this
		{
			$lcReturn .= 'class="' . $pcClass . '" ' ;
		}

		if($plIsReadOnly)
		{
			$lcReturn .= 'readonly="readonly"' ;
		}

		$lcReturn .='/>' ;

		return $lcReturn ;
	} // end of defineInputFile

	function defineInputTextArea($pcElementID, $pcValue, $pcTitle, $pnRows, $pnColumns, $pcExtra, $pcClass, $plIsReadOnly, $pnTabIndex)
	{
		$lcReturn = '<textarea id="' . $pcElementID . '"
					title="' . $pcTitle . '"
					cols="' . $pnColumns . '"
					rows="' . $pnRows . '"
					tabindex="' . $pnTabIndex .'" ' ;

		if(!empty($pcExtra)) // extra options specified - used for code such as onchange event handlers
		{
			$lcReturn .= $pcExtra . ' ' ;
		}

		if(!empty($pcClass)) // there is a CSS class to apply to this
		{
			$lcReturn .= 'class="' . $pcClass . '" ' ;
		}

		if($plIsReadOnly)
		{
			$lcReturn .= 'readonly="readonly" ' ;
		}

		$lcReturn .= '>' ;

		if(!empty($pcValue))
		{
			$lcReturn .= $pcValue ;
		}

		$lcReturn .= '</textarea>' ;

		return $lcReturn ;
	} // end of defineInputText

	function defineInputCheckBox($pcElementID, $pcValue, $pcTitle, $pcExtra, $pcClass, $plIsChecked, $plIsReadOnly, $pnTabIndex)
	{
		$lcReturn = '<input type="checkbox"
					title="' . $pcTitle . '"
					id="' . $pcElementID . '"
					value="' . $pcValue . '"
					tabindex="' . $pnTabIndex . '" ' ;

		if(!empty($pcExtra)) // extra options specified - used for code such as onchange event handlers
		{
			$lcReturn .= $pcExtra . ' '   ;
		}

		if(!empty($pcClass)) // there is a CSS class to apply to this
		{
			$lcReturn .= 'class="' . $pcClass . '"'  ;
		}

		if($plIsChecked) // this is checked by default
		{
			$lcReturn .= 'checked="checked" ' ;
		}

		if($plIsReadOnly)
		{
			$lcReturn .= 'disabled="disabled" ' ;
		}

		$lcReturn .= '/>' ;

		return $lcReturn ;
	} // end of defineInputCheckBox

	function defineInputRadioButton($pcElementID, $pcValueList, $pcTitle, $pcExtra, $pnCheckedItem, $plIsReadOnly, $pnTabIndex)
	{
		$lcReturn 		= '' ;
		$laValueList	= explode("||", $pcValueList) ;
		$lnCount		= count($laValueList) - 1 ;
		for($lnPntr=0; $lnPntr <= $lnCount; $lnPntr++)
		{
			$lcReturn .=	'<input type="radio"
							id="' . $pcElementID . '"
							value="' . $laValueList[$lnPntr] . '"
							tabindex="' . $pnTabIndex + $lnPntr . '" ';

			if(!empty($pcExtra)) // extra options specified - used for code such as onchange event handlers
			{
				$lcReturn .= $pcExtra . ' ';
			}

			if(!empty($pcClass)) // there is a CSS class to apply to this
			{
				$lcReturn .= 'class="' . $pcClass . '" ' ;
			}

			if($pnCheckedItem === $lnPntr) // this item is checked by default
			{
				$lcReturn .= 'checked="checked" ' ;
			}

			if($plIsReadOnly)
			{
				$lcReturn .= 'readonly="readonly"' ;
			}

			$lcReturn .= ' />' ;
		}

		return $lcReturn ;
	} // end of defineInputRadioButton

	function defineInputButton($pnType, $pcElementID, $pcValue, $pcTitle, $pcClass, $pcExtra, $plIsReadOnly, $pnTabIndex)
	{
		// There are cases when the form doesn't use a standard submit buton but rather it uses an image with an onclick.
		// This function handles both of these cases as well as the various traditional input buttons.
		switch($pnType)
		{
			case 1: // image with an onclick
				$lcReturn = '<img id="' . $pcElementID . '" src="' . $pcValue . '" title="' . $pcTitle .'" alt="' .$pcTitle . '" onmouseover="this.style.cursor=\'pointer\';" ' ;
				break ;
			case 2: // input - submit
				$lcReturn = '<input type="submit"
							id="' . $pcElementID . '"
							title="' . $pcTitle . '"
							value="' . $pcValue . '" ' ;
				break ;
			case 3: // input - reset
				$lcReturn = '<input type="reset"
							id="' . $pcElementID . '"
							title="' . $pcTitle . '"
							value="' . $pcValue . '" ' ;
				break ;
			default: // input - button
				$lcReturn = '<input type="button"
							id="' . $pcElementID . '"
							title="' . $pcTitle . '"
							value="' . $pcValue . '" ' ;
			 break ;
		}

		$lcReturn .= 'tabindex="' . $pnTabIndex . '" ' ;

		if(!empty($lcReturn))
		{
			if(!empty($pcClass))
			{
				$lcReturn .= 'class="' . $pcClass . '" ' ;
			}

			if(!empty($pcExtra))
			{
				$lcReturn .= $pcExtra . ' ' ;
			}
			if($plIsReadOnly)
			{
				if($pnType == 1)
				{
					$lcReturn .= 'style="display:none" ' ;
				}
				else
				{
					$lcReturn .= 'disabled="disabled" ' ;
				}
			}
			$lcReturn .= ' />';
		}

		return $lcReturn ;
	} // end of defineInputText

} // end of formobject class definition

?>
