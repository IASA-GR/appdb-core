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

class MailController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
		$this->_helper->layout->disableLayout();
    }

    public function indexAction()
    {
        // action body
    }

    public function sendAction()
    {
        include('Mail.php');

        $data = $_POST;

        $headers['From']    = $data['username']; 
        $headers['Subject'] = $data['subject'];
		$headers['Date'] = date("r");
        $recipients = array();
        if ( is_array( $data['to'] ) ) {
            foreach( $data['to'] as $to ) {
                $recipients[] = $to;
            }
        } else $recipients[] = $data['to'];

        if ( array_key_exists('body', $data) ) $body = $data['body']; else $body = "";

        $params['host'] = EmailConfiguration::getSmtpHost();
        $params['port'] = EmailConfiguration::getSmtpPort();
        $params['auth'] = EmailConfiguration::getSmtpAuth();
        $params['username'] = $data['username'];
        $params['password'] = $data['password'];

        // Create the mail object using the Mail::factory method
        $mail_object =& Mail::factory('smtp', $params);

        $mail_object->send($recipients, $headers, $body);
    }
}

