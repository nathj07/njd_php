<?php
/*
File: lib/emailObject.php
Author: Nathan Davies
Created: 03/08/2007
Purpose: To control the setting and sending of emails, it is based on the PHP core mail() function
Ammendments: ND		06/08/2007 - Made this text only email - improves accesibility.
*/

class emailObject
{
	var $lcAddressList ;// csv list of email addresses, in the format of "name&&address,name&&address,
	var $lcSendFrom ;	// the email address the message is sent from, also used for ini file (sendmail_from) if required
	var $lcReplyTo ;	// this enables a separate send from and reply to address to be specified in the headers
	var $lcHeader ;		// this contains the header information and is set by setHeaders() function and used in the sendEMail() Function
	var $llSetIniFile ;	// indicates whether the items in the ini file need to be set, note as this is differnet fo *nix and Win32 systems there are three variables involved
	var $lcBoundry ;	// the boundary string for multi part messages - set in setHeaders() based on the call from sendEMail(), mulit part ios the standard - html with a text alternative

	public function emailObject($pcAddressList, $pcSendFrom, $pcReplyAddress, $plSetIniFile, $pcIniVariable, $pcValueForIni)
	{
		// initialisation of the properties and setting of the ini file, where required
		$this->lcAddressList	= $pcAddressList ;
		$this->lcSendFrom		= !empty($pcSendFrom) ? $pcSendFrom : 'webform@christianleadership.org' ;
		$this->lcReplyTo		= !empty($pcReplyAddress) ? $pcReplyAddress : $pcSendFrom ;	// ensure that the reply to can always be set. Easier than adding headers conditionally later
		$this->llSetIniFile		= $plSetIniFile ;

		// set the ini file if required
		if ($this->llSetIniFile)
		{
			ini_set($pcIniVariable, $pcValueForIni) ; // use of variables enables one line to be used regardless of *nix or Win32 - the caller needs to be aware of their system to make the relvant call
			ini_set('sendmail_from', $this->lcSendFrom) ;
		}
	}

	function setHeaders($pcRecipient, $pcSubject)
	{
		$this->lcHeader .= "From: " . $this->lcSendFrom . "\r\n" ;
		$this->lcHeader .= "Reply-To: " . $this->lcReplyTo . "\r\n" ;
		$this->lcHeader .= "X-Mailer: PHP " . phpversion() . "\r\n" ;
	}

	function sendEMail($pcSubject, $pcMessage)
	{
		if (!empty($pcMessage))
		{
			$laMessage		= explode('||', $pcMessage); // $pcMessage is formatted so that || means a new paragraph
			$lnLineCount	= count($laMessage) - 1;
			$lnSentItems	= 0;
			if (empty($pcSubject))
			{
				$pcSubject = "General Information" ; // set a default catch all subject - advisable not to use this
			}

			// loop through the address list
			$laEmailAddressList = explode(',', $this->lcAddressList) ;
			$lnArrayCount = count($laEmailAddressList) -1 ; // subtract 1 to make it useful in the loop below

			for ($lnArrayPntr = 0; $lnArrayPntr <= $lnArrayCount; $lnArrayPntr++)
			{
				if (!empty($laEmailAddressList[$lnArrayPntr]))
				{
					$lcRawAddress	= $laEmailAddressList[$lnArrayPntr];
					$lcRecipient	= str_replace('&&', '&#60;', $lcRawAddress)  . '&#62' ; // should be in the format of name&&email address,name&&email address, becomes name<email adrress>, ...
					$lcEmailAddress	= substr($lcRawAddress, strpos($lcRawAddress, "&&") + 2) ;
					$lcEmailName	= substr($lcRawAddress, 0, strpos($lcRawAddress, "&&"));
					$this->setHeaders($lcRecipient, $pcSubject) ;

					$lcMessage		= "" ; // provide an initial start point so that the loop is easier
					for($lnLinePntr = 0; $lnLinePntr <= $lnLineCount; $lnLinePntr++)
					{
						$lcMessage .= $laMessage[$lnLinePntr] . "\n\n" ;
					}

					// send the message and test for the result - was it sent successfully or not - unfortunatley this does not allow us to check the delivery status.
					if(mail($lcEmailAddress, $pcSubject, $lcMessage, $this->lcHeader))
					{
						$lnSentItems++;	// the most likely options are that this will equal 0 or the number of email addresses supplied.
					}
				}
			}

			$lnReturn = $lnArrayCount - $lnSentItems ; // this means that a return of 0 is the desired result
		}
		else
		{
			$lnReturn = -1 ; // this only happens if no mesage was supplied
		}
	}

} // end of class definition

?>