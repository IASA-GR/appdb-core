#!/bin/bash
for i in DbTable/*.php; do if [[ "$i" != "DbTable/ZendDBTableBase.php" ]]; then sed -i $i -e 's/Zend_Db_Table_Abstract/Repository_Model_DbTable_ZendDBTable/g'; fi; done
sed -i MetaProductReleasesMapperBase.php -e 's/Repository_Model_MetaProductReleas /Repository_Model_MetaProductRelease /'

