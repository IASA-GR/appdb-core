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
 * Query and retrieve email configuration
 *
 * @author nakos
 */
class EmailConfiguration {
	
	/**
	 * Query application's email.use value
	 * 
	 * @param type $default		Default return value
	 * @return type
	 */
	public static function canUse($default = false)
	{
		return ApplicationConfiguration::email('enable', $default);
	}
	
	/**
	 * Query application's smtp information by given key
	 * 
	 * @param string $key		Specific smtp field to query
	 * @param type $default		Default return value
	 * @return type
	 */
	public static function getSmtp($key, $default = NULL)
	{
		return ApplicationConfiguration::email('smtp.' . $key, $default);
	}
	
	/**
	 * Get application's smtp host value
	 * 
	 * @return string
	 */
	public static function getSmtpHost()
	{
		return EmailConfiguration::getSmtp('host', NULL);
	}
	
	/**
	 * Get application's smtp port value
	 * 
	 * @return int
	 */
	public static function getSmtpPort()
	{
		return intval( EmailConfiguration::getSmtp('port', 465) );
	}
	
	/**
	 * Get application's smtp auth value
	 * 
	 * @return string
	 */
	public static function getSmtpAuth()
	{
		return EmailConfiguration::getSmtp('auth', 'PLAIN');
	}
	
	/**
	 * Get email information for support account configuration
	 * 
	 * @param string $key		Field to retrieve value. E.g. address, password
	 * @param string $default	Default return value
	 * @return string
	 */
	public static function getSupport($key, $default = NULL)
	{
		return ApplicationConfiguration::email( 'accounts.support.' . $key, $default );
	}
	
	/**
	 * Get the email address of the report account
	 * 
	 * @param type $default	Default return value
	 * @return string		Report email address
	 */
	public static function getSupportAddress($default = NULL)
	{
		return ApplicationConfiguration::email( 'accounts.support.address', $default );
	}
	
	/**
	 * Get the email password of the support account
	 * 
	 * @param type $default	Default return value
	 * @return string		Support email password
	 */
	public static function getSupportPassword($default = NULL)
	{
		return ApplicationConfiguration::email( 'accounts.support.password', $default );
	}
	
	/**
	 * Get email information for report account configuration
	 * 
	 * @param string $key		Field to retrieve value. E.g. address, password
	 * @param string $default	Default return value
	 * @return string
	 */
	public static function getReport($key, $default = NULL)
	{
		return ApplicationConfiguration::email( 'accounts.support.' . $key, $default );
	}
	
	/**
	 * Get the email address of the report account
	 * 
	 * @param type $default		Default return value
	 * @return string			Report email address
	 */
	public static function getReportAddress($default = NULL)
	{
		return ApplicationConfiguration::email( 'accounts.report.address', $default );
	}
	
	/**
	 * Get the email password of the report account
	 * 
	 * @param type $default	Default return value
	 * @return string		Report email password
	 */
	public static function getReportPassword($default = NULL)
	{
		return ApplicationConfiguration::email( 'accounts.report.password', $default );
	}
	
	/**
	 * Get a list of development email adresses. To be used by development 
	 * application instances.
	 * 
	 * @return array	An array of email addresses
	 */
	public static function getDevelopmentRecepients()
	{
		$recipients = ApplicationConfiguration::email( 'developers', '' );
		
		if( trim($recipients) === '' )
		{
			return array();
		}
		
		if( strpos(';',$recipients) !== FALSE )
		{
			$recipients = explode(';', $recipients);
		}
		else if( strpos(',', $recipients) !== FALSE )
		{
			$recipients = explode(',', $recipients);
		}
		else
		{
			$recipients = array($recipients);
		}
		
		return array_map('trim', $recipients);
	}
	
	/**
	 * Get configured recipient list by given key
	 * 
	 * @param string $list	Name of list
	 * @return array		Array of email addresses of the list
	 */
	public static function getList($list)
	{
		$recipients = ApplicationConfiguration::email('list.' . $list, '');
		
		if( trim($recipients) === '' )
		{
			return array();
		}
		
		if( strpos(';',$recipients) !== FALSE )
		{
			$recipients = explode(';', $recipients);
		}
		else if( strpos(',', $recipients) !== FALSE )
		{
			$recipients = explode(',', $recipients);
		}
		else
		{
			$recipients = array($recipients);
		}
		
		return array_map('trim', $recipients);
	}
}
