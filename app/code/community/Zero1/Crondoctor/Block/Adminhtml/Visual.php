<?php
class Zero1_Crondoctor_Block_Adminhtml_Visual extends Mage_Adminhtml_Block_Widget_Container
{
	protected $_template = 'crondoctor/visual/container.phtml';
	protected $_headerText = 'Cron Doctor Jobs Visual';

	public function getFormHtml(){
		return $this->getLayout()->createBlock('zero1_crondoctor/adminhtml_visual_form')->toHtml();
	}

}
