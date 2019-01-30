#!/usr/bin/python2
import sys
if len(sys.argv) != 3:
    print 'args error'
    sys.exit(1);
import xml.etree.ElementTree

preamble = '''<?php
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
// PLEASE DO NOT EDIT THIS FILE
// IT IS AUTOMATICALLY GENERATED BY THE MODELLER
// AND ANY CHANGES WILL BE OVERWRITTEN
namespace Application\Model;\n
'''

def makeItem(e):
    for table in e.findall('table'):
        if table.get('oname') != sys.argv[2]: 
            continue
        sel = table.get('selected')
        if sel == '1':
            code = preamble
            code += "class " + table.get('singular') + "Base extends AROItem {\n"
            code += "\tpublic function __construct() {\n"
            code += "\t\t$this->_basename = '" + table.get('oname') + "';\n"
            code += "\t\t$this->_baseitemname = '" + table.get('singular') + "';\n"
            code += "\t\tparent::__construct();\n"
            for field in table.findall('field'):
                fsel = field.get('selected')
                if fsel == '1':
                    code += "\t\t$this->_properties[] = new \\Application\\Model\\AROProperty($this, '" + field.get('name') + "', '" + field.get('oname') + "');\n"
                    if not field.get('fkoname') is None: 
                        code += "\t\t$this->_properties[] = new \\Application\\Model\\AROProperty($this, '" + field.get('name') + "', '" + field.get('fkoname') + "', '" + field.get('fkotype') + "');\n"
            code += "\t}\n"
            code += "}\n"
            code += "?>"
            return code, table.get('singular') + "Base.php"

def makeCollection(e):
    for table in e.findall('table'):
        if table.get('oname') != sys.argv[2]: 
            continue
        sel = table.get('selected')
        if sel == '1':
            code = preamble
            code += "class " + table.get('oname') + "Base extends AROCollection {\n"
            code += "\tpublic function __construct($filter = null) {\n"
            code += "\t\t$this->_basename = '" + table.get('oname') + "';\n"
            code += "\t\t$this->_baseitemname = '" + table.get('singular') + "';\n"
            code += "\t\tparent::__construct($filter);\n"
            code += "\t}\n"
            code += "}\n"
            code += "?>"
            return code, table.get('oname') + "Base.php"

def makeMapper(e):
    for table in e.findall('table'):
        if table.get('oname') != sys.argv[2]: 
            continue
        sel = table.get('selected')
        if sel == '1':
            code = preamble
            code += "class " + table.get('oname') + "MapperBase extends AROMapper {\n"
            code += "\tpublic function __construct() {\n"
            code += "\t\t$this->_basename = '" + table.get('oname') + "';\n"
            code += "\t\t$this->_baseitemname = '" + table.get('singular') + "';\n"
            code += "\t}\n\n"
            code += "\tpublic function save(\\Application\\Model\\AROItem $value) {\n"
            code += "\t\tparent::save($value);\n"
            code += "\t}\n\n"
            code += "\tprotected function _presave(\\Application\\Model\\AROItem $value) {\n"
            code += "\t\tparent::_presave($value);\n"
            code += "\t}\n"
            code += "}\n"
            code += "?>"
            return code, table.get('oname') + "MapperBase.php"

print sys.argv[2]
e = xml.etree.ElementTree.parse(sys.argv[1]).getroot()
mapper, fmapper = makeMapper(e)
collection, fcollection = makeCollection(e)
item, fitem = makeItem(e)

f1 = open("/tmp/m/" + fmapper, "w+")
f1.write(mapper)
f1.close()

f2 = open("/tmp/m/" + fcollection, "w+")
f2.write(collection)
f2.close()

f3 = open("/tmp/m/" + fitem, "w+")
f3.write(item)
f3.close()
#if sys.argv[3] == "mapper":
#    print makeMapper(e)
#elif sys.argv[3] == "item":
#    print makeItem(e)
#elif sys.argv[3] == "collection":
#    print makeCollection(e)
#else:
#    sys.exit(2)
