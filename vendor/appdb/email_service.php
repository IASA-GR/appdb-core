<?php
/**
 * Copyright (C) 2015 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and 
 * limitations under the License.
 */

/**
 * Provides email service functionality for the application.
 */
class EmailService
{
	private static $_canUse;
	
	/**
	 * Helper function to log formatted messages to log. In case of
	 * DEBUG or INFO type the logging occurs only for development
	 * instances of the application.
	 * 
	 * @param string $message	The message to log
	 * @param string $type		Type of logging. EG DEBUG, INFO, ERROR, WARN
	 */
	private static function log($message, $type='ERROR')
	{
		$txt = '[EmailService::' . strtoupper($type) . ']: ' . $message;
		
		if( $type === 'DEBUG' || $type === 'INFO') 
		{
			debug_log($txt);
		}
		else
		{
			error_log('[EmailService::' . strtoupper($type) . ']: ' . $message);
		}
	}
	
	/**
	 * Checks configuration to conclude if EmailService is usable.
	 */
	public static function canUse()
	{
		if( isset(self::$_canUse) === false && (self::$_canUse === true || self::$_canUse === false) )
		{
			return self::$_canUse;
		}
		
		$canuse = EmailConfiguration::canUse();
		
		if(strtolower( trim($canuse) ) === 'production' )
		{
			$canuse = ApplicationConfiguration::isProductionInstance();
		}
		
		if( $canuse !== true && $canuse !== false )
		{
			$canuse = false;
		}
		
		self::$_canUse = $canuse;
		
		return self::$_canUse;
	}
	
	/**
	 * Get the report account credentials
	 * 
	 * @return mixed A hash array with username and password.False if missing.
	 */
	private static function getReportCredentials()
	{
		$username = EmailConfiguration::getReportAddress();
		$password = EmailConfiguration::getReportPassword();
		
		if( !$username || !$password )
		{
			return false;
		}
		
		return array(
			"username" => $username,
			"password" => $password
		);
	}
	
	/**
	 * Check if report account credentials are configured properly
	 * 
	 * @return boolean
	 */
	public static function canSendReport()
	{
		$smtp_host = EmailConfiguration::getSmtpHost();
		$report_creds = EmailService::getReportCredentials();
		
		if( !$report_creds || trim($smtp_host) === '' )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * @internal Actual email dispatcher.
	 * 
	 * @param type $subject		Email subject value
	 * @param type $to			Email recipient value
	 * @param type $txtbody		Email text body value
	 * @param type $htmlbody	Email html body value
	 * @param type $username	Smtp username value
	 * @param type $password	Smtp username value
	 * @param type $replyto		Email replyto value
	 * @param type $attachment	Email attachment data
	 * @param type $cc			Email carbon copies
	 * @param type $ext			Email extended header data
	 */
	private static function sendEmail($subject, $to, $txtbody='', $htmlbody='', $username=null, $password=null, $replyto = false, $attachment = null, $cc=false, $ext = null)
	{
		if( EmailService::canUse() )
		{
			sendMultipartMail($subject, $to, $txtbody, $htmlbody, $username, $password, $replyto, $attachment, $cc, $ext);
		}
		else
		{
			EmailUtils::emailToLog($subject, $to, $txtbody, $htmlbody, $replyto, $cc, $ext);
		}
	}
	
	/**
	 * Send email using the report account. Report account credentials 
	 * must be configured in application.ini or enviroment in order
	 * function. 
	 * 
	 * @param string $subject		Email subject text
	 * @param array $to			Array of recipients' email addresses 
	 * @param string $textbody	Text representation of email body
	 * @param string $htmlbody	Html representation of email body
	 * @param string $replyto		Recipient's email address which replyto fucntion will point to.
	 * @param array $attachment	Attachement array as provided from the EmailService::createAttachment function
	 * @param array $cc			Array of recipient email addresses where the email carbon copied 
	 * @param array $ext			Array of key/value pairs for the email header.Eg Precedence=bulk
	 * @return boolean
	 */
	public static function sendReport($subject, $to, $textbody = '', $htmlbody = '', $replyto = false, $attachment = null, $cc = false , $ext = null)
	{
		$credentials = self::getReportCredentials();
		if( $credentials === FALSE )
		{
			EmailService::log('No credentials registered for report email account', 'ERROR');
			return false;
		}
		
		if( $attachment !== NULL && !is_array($attachment) )
		{
			$attachment = EmailService::createAttachment($attachment);
		}
		
		EmailService::sendEmail($subject, $to, $textbody, $htmlbody, $credentials['username'], $credentials['password'], $replyto, $attachment, $cc, $ext);
		
		return true;
	}
	
	/**
	 * Send bulk email using the report account. Report account credentials 
	 * must be configured in application.ini or enviroment in order
	 * function. This function does not allow attachements, carbon 
	 * copies and impersonation of email (From header).
	 * 
	 * @param string $subject	Email subject text
	 * @param array $to			Array of recipients' email addresses 
	 * @param string $textbody	Text representation of email body
	 * @param string $htmlbody	Html representation of email body
	 * @param string $replyto	Email address used to reply to
	 * @return boolean
	 */
	public static function sendBulkReport($subject, $to, $textbody = '', $htmlbody = '', $replyto = false, $atttachment = null)
	{
		$ext = array(
			"Precedence" => "bulk"
		);
		
		return EmailService::sendReport($subject, $to, $textbody, $htmlbody, $replyto, $atttachment, false, $ext);
	}
	
	/**
	 * Send email as user email address using the report account. Report account 
	 * credentials must be configured in application.ini or enviroment in order 
	 * to function. The replyto field of the email will be the impersonation email.
	 * 
	 * @parma string	$from		Impersonation email address
	 * @param string	$subject		Email subject text
	 * @param array		$to			Array of recipients' email addresses 
	 * @param string	$textbody	Text representation of email body
	 * @param string	$htmlbody	Html representation of email body
	 * @param array		$attachment	Attachement array as provided from the EmailService::createAttachment function
	 * @param array		$cc			Array of recipient email addresses where the email carbon copied
	 * @return boolean
	 */
	public static function sendReportAsUser($from, $subject, $to, $textbody = '', $htmlbody = '', $attachment = null, $cc = false)
	{
		$ext = null;
		
		if( trim($from) !== '' )
		{
			$ext = array(
				"From" => $from
			);
		}
		else
		{
			$from = false;
		}
		
		return EmailService::sendReport($subject, $to, $textbody, $htmlbody, $from, $attachment, $cc, $ext);
	}
	
	
	/**
	 * Creates a hash array describing an email attachment
	 * 
	 * @param type $data	The attached data
	 * @param string $name	The name of the attachement
	 * @param string $type	The mime type of the attachment
	 * @return mixed		NULL if null data is given.
	 */
	public static function createAttachment($data, $name = 'attachment.dat', $type = 'application/octet-stream' )
	{
		if( $data === NULL )
		{
			return null;
		}
		
		if( trim($name) === '' )
		{
			$name = 'attachment.dat';
		}
		
		if( trim($type) === '' )
		{
			$type = 'application/octet-stream';
		}
		
		return array(
			"data" => $data,
			"type" => $type,
			"name" => $name
		);
	}
}

/**
 * Genric utilities used by emailing functions.
 */
class EmailUtils
{
	
	/**
	 * Logs email information in error log. For developement purposes.
	 */
	public static function emailToLog($subject='', $to = array(), $text='', $body='', $replyto = null, $cc = null)
	{
		$msg = "\n[=====================================  SEND EMAIL  =====================================]";
		
		if( is_array($to) )
		{
			$to = implode(';', $to);
		}
		
		if( trim($to) !== '' )
		{
			$msg .= "\n[TO]: " . $to;	
		}
		
		if( is_array($cc) )
		{
			$cc = implode(';', $cc);
		}
		
		if( trim($cc) !== '' )
		{
			$msg .= "\n[CC]: " . $cc;
		}
		
		if( trim($replyto) !== '' )
		{
			$msg .= "\n[REPYTO]: " . $replyto;
		}
		
		
		
		if( trim($subject) !== '' )
		{
			$msg .= "\n[SUBJECT]: " . $subject;
		}
		
		if( trim($text) !== '' )
		{
			$msg .= "\n[TEXT]:____________________________________________________________________________";
			$msg .= "\n" . $text . "\n";
		}
		
		if( trim($body) !== "" )
		{
			$msg .= "\n[HTML]:_____________________________________________________________________________";
			$msg .= "\n" . $body . "\n";
			
		}
		
		$msg .= "\n[========================================================================================]";
		
		error_log($msg);
	}
	
	/**
	 * Outputs outgoing email as HTML.For logging/development purposes only.
	 */
	public static function emailToHTML($subject='', $to=array(), $text='', $body='', $replyto = '', $cc = array(), $ext = null)
	{
		$style = "style='background-color:lightgrey;border:1px solid black'";
		if( is_array($to) )
		{
			$to = implode(';', $to);
		}
		
		if( is_array($cc) )
		{
			$cc = implode(';', $cc);
		}
		
		$html  = "<div " . $style . "><b>subject: </b>" . $subject . "</div>";
		$html .= "<div " . $style . "><b>TO: </b>" . $to . "</div>";
		
		if( trim($replyto) !== '' )
		{
			$html .= "<div " . $style . "><b>REPLYTO: </b>" . $replyto . "</div>";
		}
		
		if( trim($cc) !== '' )
		{
			$html .= "<div " . $style . "><b>CC: </b>" . $cc . "</div>";
		}
		
		if( is_array($ext) && count($ext) > 0 )
		{
			$ext = json_encode($ext);
			$html .= "<div " . $style . "><b>EXT: </b>" . $ext  . "</div>";
		}
		
		$html .= "<div " . $style . ">" . $body . "</div>";
		$html .= "<div style='background-color:#99DBFF;margin-bottom:10px;border:1px solid black'><pre>" . $text . "</pre></div>";
		
		return $html;
	}
}
