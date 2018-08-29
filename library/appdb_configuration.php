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

require_once('Zend/Config/Ini.php');

class ApplicationConfigurationIni
{
	private static $_conf;
	private static $_cache;
	public static $_inited;
	
	public static function _init($options = NULL)
	{
		if( $options === NULL )
		{
			self::$_conf = new Zend_Config_Ini(__DIR__ . '/../application/configs/application.ini', $_SERVER['APPLICATION_ENV']);
		}
		else 
		{
			self::$_conf = json_decode( json_encode($options) );
		}
		
		self::$_cache = array();
		$_inited = true;
	}
	
	/**
	 * Basic data coercion for empty or boolean values
	 * 
	 * @param string $value - Value to parse and coerse
	 * @return boolean|null|string - Coerced data value
	 */
	private static function coerce_value($value)
	{
		
		if( is_object($value) )
		{
			return $value;
		}
		
		$v = strtolower( trim($value) );
		
		switch( $v )
		{
			case '':
				return NULL;
			case 'false':
				return false;
			case 'true':
				return true;
			default:
				return $value;
		}
	}
	
	/**
	 * Get configuration data by the given key path.
	 * 
	 * 
	 * @param string $path - Dot seperated path value
	 * @param type $default - Default value when path is not found
	 * 
	 * @return string - Configuration valie
	 */
	public static function getConfig($path, $default = NULL)
	{
		$val = NULL;
		$path = trim($path);
		
		if( isset(self::$_cache[$path]) ) 
		{
			$val = self::$_cache[$path];
		}
		else
		{
			$keys = explode('.', $path);
			foreach($keys as $key)
			{
				if( $val  )
				{
					if( isset($val->{$key}) === FALSE )
					{
						$val = NULL;
						break;
					}
					$val = $val->{$key};
				} 
				else
				{
					if( isset(self::$_conf->{$key}) === FALSE )
					{
						$val = NULL;
						break;
					}
					$val = self::$_conf->{$key};
				}

				if( $val === NULL )
				{
					break;
				}			
			}
			
			$val = self::coerce_value( $val );
			
			if( $val )
			{
				 self::$_cache[$path] = $val;
			}
		}
		
		
		//If not in application.ini file 
		//check enviroment variables.
		if( $val === NULL )
		{
			$val = getenv( 'APPDB_' . str_replace('.', '_', strtoupper($path) ) );
			
			if( $val === false )
			{
				$val = NULL;
			}
			else
			{
				$val = self::coerce_value($val);
			}
		}
		
		if( $val === NULL )
		{
			return $default;	
		}
		
		return $val;
	}
	
	/**
	 * Retrieve values for the given namespace by the given key
	 * rom the application ini file. Eg app.wiki='https://...'
	 * 
	 * @param String $namespace - Namespace to query
	 * @param String $key - The key to search app namespace
	 * @param Any $default - Default value when key does not exist
	 * @return String
	 */	
	public static function getConfigNS($namespace, $key = '', $default = NULL)
	{
		$path = trim($namespace);
		
		if( trim($key) !== '' )
		{
			$path .= '.' . trim($key);
		}
		
		$val = self::getConfig($path, $default);
				
		return $val;
	}
	
	/**
	 * Returns configuration by namespace.
	 * 
	 * @param String $namespace - Namespace to retrieve
	 * @return object - Zend_Config object instance
	 */	
	public static function getNamespaceConfiguration($namespace)
	{
		return self::$_conf->{$namespace};
	}
}

/**
 * Check if configuration is loaded from bootstrap 
 * process or application.ini should be loaded.
 */
if( ApplicationConfigurationIni::$_inited !== true )
{
	//In case of zend bootstrap global $this is set
	if( isset($this) )
	{
		ApplicationConfigurationIni::_init($this->_options);
	}
	else
	{
		ApplicationConfigurationIni::_init();
	}
	
}

/**
 * Query and retrieve values from application ini file
 * 
 * @author nakos
 */
class ApplicationConfiguration
{
	/**
	 * Retrieve values by key under the 'app' namespace. 
	 * Eg app.wiki='https://....'
	 * 
	 * @param String $key - The key to search app namespace
	 * @param Any $default - Default value when key does not exist
	 * @return String
	 */
	public static function app($key, $default = NULL)
	{
		return ApplicationConfigurationIni::getConfigNS('app', $key, $default);		
	}
	
	/**
	 * Retrieve values by key under the 'api' namespace. 
	 * Eg api.latestVersion='1.0'
	 * 
	 * @param String $key - The key to search the api namespace
	 * @param Any $default - Default value when key does not exist
	 * @return String
	 */
	public static function api($key, $default = NULL)
	{
		return ApplicationConfigurationIni::getConfigNS('api', $key, $default);
	}

	/**
	 * Retrieve values by key under the 'support' namespace. 
	 * Eg support.singlevmipolicy='true'
	 * 
	 * @param String $key - The key to search the support namespace
	 * @param Any $default - Default value when key does not exist
	 * @return String
	 */
	public static function support($key, $default = NULL)
	{
		return ApplicationConfigurationIni::getConfigNS('support', $key, $default);
	}
	
	/**
	 * Retrieve values by key under the 'email' namespace. 
	 * Eg email.support.username=...
	 * 
	 * @param String $key - The key to search the support namespace
	 * @param Any $default - Default value when key does not exist
	 * @return String
	 */
	public static function email($key, $default = NULL)
	{
		return ApplicationConfigurationIni::getConfigNS('email', $key, $default);
	}
	
	/**
	 * Retrieve values by key under the 'service' namespace. 
	 * Eg service.egi.ldap.username=...
	 * 
	 * @param String $key - The key to search the support namespace
	 * @param Any $default - Default value when key does not exist
	 * @return String
	 */
	public static function service($key, $default = NULL)
	{
		return ApplicationConfigurationIni::getConfigNS('service', $key, $default);
	}
	
	/**
	 * Retrieve values by key under the 'deploy' namespace. 
	 * Eg deploy.instance='production'
	 * 
	 * @param String $key - The key to search the support namespace
	 * @param Any $default - Default value when key does not exist
	 * @return String
	 */
	public static function deploy($key, $default = NULL)
	{
		return ApplicationConfigurationIni::getConfigNS('deploy', $key, $default);
	}
	
	/**
	 * Retrieve values by key under the 'saml' namespace. 
	 * Eg saml.profile.allow='domain1;domain2;domain3'
	 * 
	 * @param String $key - The key to search the support namespace
	 * @param Any $default - Default value when key does not exist
	 * @return String
	 */
	public static function saml($key, $default = NULL)
	{
		return ApplicationConfigurationIni::getConfigNS('saml', $key, $default);
	}
	
	/**
	 * Returns the current version of AppDB
	 * 
	 * @return string - The version of AppDB
	 */
	public static function version()
	{
		$v = exec("cat " . self::applicationPath() . "/../VERSION"); 
		
		if ( self::isEnviroment("production") )
		{
			$rev = @exec("svn info 2>&1 | grep Revision | awk '{print $2}'");
			
			if ( $rev != '' )
			{
				$v = "$v-r$rev";
			}
		}
		
		return $v;
	}
	
	/**
	 * Returns the current application path
	 * 
	 * @return string - Application path
	 */
	public static function applicationPath()
	{
		return realpath(dirname(__FILE__) . '/../application');
	}
	
	/**
	 * Retrieves the AppDB enviroment value
	 * 
	 * @return string - production or development
	 */
	public static function enviroment()
	{
		return trim($_SERVER['APPLICATION_ENV']);
	}
	
	/**
	 * Checks if current AppDB enviroment is the given one
	 * 
	 * @param string - production or development
	 * @return bool
	 */
	public static function isEnviroment($env)
	{
		if( strtolower( trim($env) ) === strtolower( trim(self::enviroment()) ) )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Checks if current AppDB instance is deployed in production server
	 * It checks the deploy.instance value of the application configuration
	 * 
	 * @return bool
	 */
	public static function isProductionInstance()
	{
		$instance = strtolower( trim( ApplicationConfiguration::deploy('instance') ) );
		if( $instance ===  'production' )
		{
			return true;
		}
		else if( $instance === '' && strtolower($_SERVER["SERVER_NAME"]) === 'appdb.egi.eu' )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Checks if protocol is secure HTTP
	 * 
	 * @return boolean
	 */
	public static function isHTTPS()
	{
		if (array_key_exists('HTTPS',$_SERVER) && $_SERVER['HTTPS'] != '' )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the application's current protocol
	 * 
	 * @return string - http or https
	 */
	public static function protocol()
	{
		$http = 'http';
		
		if( self::isHTTPS() )
		{
			$http .= 's';
		}
		
		return $http;
	}
	
	/**
	 * Get AppDB frontend url
	 * 
	 * @return string - AppDB frontend url
	 */
	public static function uiUrl()
	{
		$protocol = self::protocol();
		$domain = $_SERVER['APPLICATION_UI_HOSTNAME'];
		
		return $protocol . '://' . $domain . '/';
	}
	
	/**
	 * Get AppDB API url
	 * 
	 * @return string - AppDB API url
	 */
	public static function apiUrl($fullpath = false)
	{
		$protocol = self::protocol();
		$domain = $_SERVER['APPLICATION_API_HOSTNAME'];
		
		$url = $protocol . '://' . $domain . '/';
		
		if( $fullpath === true )
		{
			$url .= 'rest/' . self::api('latestVersion', '1.0') . '/';
		}
		
		return $url;
	}
	
	/**
	 * Builds url for the given service and path
	 * 
	 * @param string $path - The relative path of the url
	 * @param string $service - UI for frontend and API for api backend. Defaults to UI
	 * @retrun string - Full url
	 */
	public static function url($path, $service = 'UI')
	{
		$serv = strtolower(trim($service));
		$domain = "";
		$sep = "/";
		
		switch( $serv )
		{
			case 'api':
				$domain = trim(self::apiUrl());
				break;
			default:
				$domain = trim(self::uiUrl());
				break;
		}
		
		$path = trim($path);
		if( substr($path, 0, 1) === '/')
		{
			$path = substr($path, 1);
		}
		
		$dlen = strlen($domain); 
		if( $dlen > 0 && substr($domain, $dlen-1, 1) === '/')
		{
			$domain = substr($domain, 0, $dlen-1);
		}
		
		return $domain . $sep . $path;
	}
}
