<?php
namespace Application\Model\DbTable;




class VMISupportedContextFormatsBase extends AROTable
{
	protected $_name = 'vmi_supported_context_fmt';
	protected $_primary = array('fmtid', 'vmiinstanceid');
	protected $_sequence = false;
}
