<?php
class Zero1_Crondoctor_Adminhtml_CronDoctor_VisualController extends Mage_Adminhtml_Controller_Action
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

	public function ajaxAction(){

		$inputFrom = $this->getRequest()->getParam('from', time());
		$inputTo = $this->getRequest()->getParam('to', time());
		$visualType = $this->getRequest()->getParam('visual_type', 'stats');

		$from = min((int)$inputFrom, (int)$inputTo);
		$to = max((int)$inputTo, (int)$inputFrom);

		$from = time() + (60 * 60 * $from);
		$to = time() + (60 * 60 * $to);

		echo 'From: '.strftime('%Y-%m-%d %H:%M:%S', $from).'<br />';
		echo 'To: '.strftime('%Y-%m-%d %H:%M:%S', $to).'<br />';


		switch($visualType){
			case 'stats':
				$orderBy = $this->getOrderBy($this->getRequest()->getParam('order_by', null));
				$block = $this->getLayout()->createBlock('zero1_crondoctor/adminhtml_visual_visual');
				$block->setFrom($from);
				$block->setTo($to);
				$block->setOrderBy($orderBy);
				echo $block->toHtml();
				break;
			case 'lag':
				$block = $this->getLayout()->createBlock('zero1_crondoctor/adminhtml_visual_lag');
				$block->setFrom($from);
				$block->setTo($to);
				echo $block->toHtml();
				break;
		}

		die;
	}

	private function getOrderBy($orderBy){
		switch($orderBy){
			case 'expected_runs':
			default:
				return 'expected_runs';
		}
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/system/zero1_crondoctor/zero1_crondoctor_visual');
    }
}
