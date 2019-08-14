<?php
class Zero1_Crondoctor_Adminhtml_CrondoctorController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->loadLayout()->_setActiveMenu('system/zero1_crondoctor');
	    $this->_title(Mage::helper('zero1_crondoctor')->__('Cron Doctor'));

		return $this;
	}
	
	public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }
    
    public function massDeleteAction()
    {
    	$scheduleIds = $this->getRequest()->getParam('crondoctor');    
    	$totalDeleted = 0;
    
    	try {
    		foreach($scheduleIds as $scheduleId) {
    			$job = Mage::getModel('cron/schedule')->load($scheduleId);
    			if ($job->getId()) {
    				$job->delete();
    				$totalDeleted++;
    			}
    		}
    		
    		if ($totalDeleted > 0) {
    			$this->_getSession()->addSuccess(
    					$this->__('Total of %d jobs(s) have been deleted.', $totalDeleted)
    			);
    		} else {
    			$this->_getSession()->addError($this->__('No jobs were deleted.'));
    		}
    	} catch (Exception $e) {
    		$this->_getSession()->addError($e->getMessage());
    	}
    
    	$this->_redirect('*/*/index');
    }
    
    public function massChangeAction()
    {
    	$scheduleIds = $this->getRequest()->getParam('crondoctor');    
    	$totalChanged = 0;
    	$status = $this->getRequest()->getParam('status');    	
    	try {
    		foreach($scheduleIds as $scheduleId) {
    			$job = Mage::getModel('cron/schedule')->load($scheduleId);
    			if ($job->getId()) {
    				$job->setStatus($status);
    				$job->save();
    				$totalChanged++;
    			}
    		}
    		
    		if ($totalChanged > 0) {
    			$this->_getSession()->addSuccess(
    					$this->__('Total of %d jobs(s) have been updated.', $totalChanged)
    			);
    		} else {
    			$this->_getSession()->addError($this->__('No jobs were updated.'));
    		}
    	} catch (Exception $e) {
    		$this->_getSession()->addError($e->getMessage());
    	}
    
    	$this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/system/zero1_crondoctor');
    }
}
