<?php
class Zero1_Crondoctor_Block_Adminhtml_Crondoctor extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		parent::__construct();
		$this->_removeButton('add');
	}
	
    public function _construct()
    {
        $this->_controller = 'adminhtml_crondoctor';
        $this->_blockGroup = 'zero1_crondoctor';
        $this->_headerText = Mage::helper('zero1_crondoctor')->__('Cron Doctor');
        //$this->_addButtonLabel = Mage::helper('zero1_crondoctor')->__('test');
        parent::_construct();
    }
}
