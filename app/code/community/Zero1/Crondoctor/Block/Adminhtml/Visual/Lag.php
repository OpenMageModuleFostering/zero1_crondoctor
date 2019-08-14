<?php
class Zero1_Crondoctor_Block_Adminhtml_Visual_Lag extends Mage_Core_Block_Template{

	protected $_template = 'crondoctor/visual/lag.phtml';

	public function getLagData(){
		$cronCollection = Mage::getModel('cron/schedule')->getCollection();
		$cronCollection->addFieldToFilter('executed_at', array('gteq' => strftime('%Y-%m-%d %H:%M:%S', $this->getFrom())));
		$cronCollection->addFieldToFilter('executed_at', array('lteq' => strftime('%Y-%m-%d %H:%M:%S', $this->getTo())));
		$cronCollection->addFieldToFilter('job_code', array('nin' => array('enterprise_refresh_index')));


//		return $cronCollection->getSelectSql(true);
		if(!$cronCollection->count()){
			return array();
		}

		$data = array();
		$values = array(); //used to calc rolling average

		/* @var $cronJob Mage_Cron_Model_Schedule */
		foreach($cronCollection as $cronJob){

			$hour = date('H', strtotime($cronJob->getExecutedAt()));
			$dayOfYear = date('z', strtotime($cronJob->getExecutedAt()));
			$lag = (strtotime($cronJob->getExecutedAt()) - strtotime($cronJob->getScheduledAt()));

			if(!isset($data[$dayOfYear.'-'.$hour])){
				$data[$dayOfYear.'-'.$hour] = array();
			}
			$values[] = $lag;
			if(count($values) > 10){
				array_shift($values);
			}

			$avg = array_sum($values) / count($values);
			$executionTime = strtotime($cronJob->getFinishedAt()) - strtotime($cronJob->getExecutedAt());

			$data[$dayOfYear.'-'.$hour][] =
				array(
					'code' => $cronJob->getJobCode(),
					'lag' => $lag,
					'avg' => $avg,
					'details' => array(
						'job code' => $cronJob->getJobCode(),
						'lag' => floor($lag/ 3600).':'. floor($lag%3600 / 60),
						'execution time' => floor($executionTime/ 3600).':'. floor($executionTime%3600 / 60),
					),
				);
		}

		return $data;
	}
}