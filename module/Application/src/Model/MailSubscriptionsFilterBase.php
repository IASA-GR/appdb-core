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
?>
<?php
namespace Application\Model;

class MailSubscriptionsFilterBase extends Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'subjecttype';
		$this->_fields[] = 'events';
		$this->_fields[] = 'researcherid';
		$this->_fields[] = 'delivery';
		$this->_fields[] = 'flt';
		$this->_fields[] = 'unsubscribe_pwd';
		$this->_fields[] = 'flthash';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['name'] = 'string';
		$this->_fieldTypes['subjecttype'] = 'string';
		$this->_fieldTypes['events'] = 'integer';
		$this->_fieldTypes['researcherid'] = 'integer';
		$this->_fieldTypes['delivery'] = 'integer';
		$this->_fieldTypes['flt'] = 'string';
		$this->_fieldTypes['unsubscribe_pwd'] = 'string';
		$this->_fieldTypes['flthash'] = 'float';
		$this->_table = 'mail_subscriptions';
	}
}