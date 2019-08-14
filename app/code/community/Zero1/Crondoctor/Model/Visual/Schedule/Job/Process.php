<?php

/**
 * Class Zero1_Crondoctor_Model_Visual_Schedule_Job_Process
 *
 * @method int getScheduledAt()
 * @method Zero1_Crondoctor_Model_Visual_Schedule_Job getJob()
 * @method Zero1_Crondoctor_Model_Visual_Schedule_Job setScheduleId(int $scheduleId)
 * @method int getScheduledId()
 * @method Zero1_Crondoctor_Model_Visual_Schedule_Job setStatus(string $status)
 * @method string getStatus()
 * @method Zero1_Crondoctor_Model_Visual_Schedule_Job setMessage(string $msg)
 * @method string getMessage()
 * @method Zero1_Crondoctor_Model_Visual_Schedule_Job setCreatedAt(int $timestamp)
 * @method int|null getCreatedAt()
 * @method Zero1_Crondoctor_Model_Visual_Schedule_Job setExecutedAt(int $timestamp)
 * @method int|null getExecutedAt()
 * @method Zero1_Crondoctor_Model_Visual_Schedule_Job setFinishedAt(int $timestamp)
 * @method int|null getFinishedAt()
 * @method Zero1_Crondoctor_Model_Visual_Schedule_Job setUpdatedAt(int $timestamp)
 * @method int|null getUpdatedAt()
 */
class Zero1_Crondoctor_Model_Visual_Schedule_Job_Process extends Varien_Object{

	public function _construct(){
		parent::_construct();

		$this->importCronSchedule();
	}

	private function importCronSchedule(){
		/* @var $cronScheduleCollection Mage_Cron_Model_Resource_Schedule_Collection */
		$cronScheduleCollection = Mage::getModel('cron/schedule')->getCollection();

		$cronScheduleCollection->addFieldToFilter('job_code', $this->getJob()->getJobCode());
		$cronScheduleCollection->addFieldToFilter('scheduled_at', strftime('%Y-%m-%d %H:%M', $this->getScheduledAt()));

		if($cronScheduleCollection->count()){
			$this->import($cronScheduleCollection->getFirstItem());
		}
	}

	private function import(Mage_Cron_Model_Schedule $cron){

		$this->setScheduleId($cron->getScheduleId());
		$this->setScheduledAt(strtotime($cron->getScheduledAt()));
		$this->setStatus($cron->getStatus());
		$this->setMessage((($cron->getMessages() != null)? $cron->getMessages() : ''));
		$this->setCreatedAt(strtotime($cron->getCreatedAt()));
		$this->setExecutedAt((($cron->getExecutedAt() == null)? null : strtotime($cron->getExecutedAt())));
		$this->setFinishedAt((($cron->getFinishedAt() == null)? null : strtotime($cron->getFinishedAt())));
		$this->setUpdatedAt((($cron->getUpdatedAt() == null)? null : strtotime($cron->getUpdatedAt())));
	}
}