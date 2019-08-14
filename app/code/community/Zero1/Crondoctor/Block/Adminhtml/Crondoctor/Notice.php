<?php
class Zero1_Crondoctor_Block_Adminhtml_Crondoctor_Notice extends Mage_Adminhtml_Block_Template
{
	protected $_errors;
	
	public function _construct()
	{
		$this->_errors = array();
		
		$cronjob_collection = Mage::getModel('cron/schedule')->getCollection();
		$cronjob_collection->addFieldToFilter('status', array(
            'eq' => Mage_Cron_Model_Schedule::STATUS_ERROR)
        );
		$cronjob_collection->getSelect()->group('job_code');
		
		foreach($cronjob_collection as $cronjob) {
			$this->_errors[] = ucwords(str_replace('_', ' ', $cronjob->getJobCode()));
		}
		
		parent::_construct();
	}
	
	public function getErrors()
	{
		return $this->_errors;
	}
}
