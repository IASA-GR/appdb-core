<?php
class Repository_Model_MetaProductReleasesMapper extends Repository_Model_MetaProductReleasesMapperBase
{
	public function joins(&$select, $filter) {
		if ( is_array($filter->joins) ) {
			if ( in_array("meta_product_repo_area", $filter->joins) ) {
				$select->joinLeft('meta_product_repo_area', 'meta_product_repo_area.id = meta_product_release.repoAreaId', array());
			}
		}
	}

	public function fetchAll($filter = null, $format = '')
	{
		$executor = null;
		$select = $this->getDbTable()->select();
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select->where($filter->expr());
			if ( $filter !== null ) {
				if ($filter->expr() != '') {
					$this->joins($select, $filter);
					$select->where($filter->expr());
					$executor = $this->getDbTable()->getAdapter();
					$executor->setFetchMode(Zend_Db::FETCH_OBJ);
				}
			}
		}		
		if ($filter !== null) $select->limit($filter->limit, $filter->offset);
		if ($filter !== null) $select->order($filter->orderBy);
		if (is_null($executor)) {
			$resultSet = $this->getDbTable()->fetchAll($select);
		} else {
			$resultSet = $executor->fetchAll($select);
		}
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Repository_Model_MetaProductRelease();
			$this->populate($entry,$row);
			if ($format === 'xml') $entry = $entry->toXML(true);
			$entries[] = $entry;
		}		return $entries;
	}
}

