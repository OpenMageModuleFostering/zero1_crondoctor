<?php
class Zero1_Crondoctor_Model_Visual_Schedule{

	private $schedule = array();
	private $jobNodes = array(
		'crontab/jobs',
		'default/crontab/jobs',
	);
	private $orderingBy = null;

	public function __construct($args){
		if(isset($args['until'])){
			$this->generateScheduleForDateRange(
				$args['until'],
				(isset($args['from'])? $args['from'] : null)
			);
		}
	}

	public function generateScheduleForDateRange($until, $from = null){
		$this->cleanUpOldScheduling();
		$from = $this->getFromTime($from);

		$c = 0;

		foreach($this->jobNodes as $jobNode){
			$jobNodeConfig = Mage::getConfig()->getNode($jobNode);
			if($jobNodeConfig instanceof Mage_Core_Model_Config_Element){
				foreach($jobNodeConfig->children() as $jobCode => $jobConfig){

					$validCodes = array(
						//'zero1_cron_debug_5min',
						//'zero1_cron_debug_2min',
						//'enterprise_staging_automates',
					);
					if(array_search($jobCode, $validCodes) === false){
						//continue;
					}
					$this->addToSchedule($this->getScheduleJob(
						$jobCode, $jobConfig, $from, $until
					));
					if($c++ == 5){
						//break;
					}
				}
			}
		}
	}

	private function getFromTime($from){
		if($from === null){
			return time();
		}else{
			return $from;
		}
	}

	private function cleanUpOldScheduling(){
		$this->schedule = array();
	}

	private function addToSchedule(Zero1_Crondoctor_Model_Visual_Schedule_Job $job){
		$this->schedule[$job->getJobCode()] = $job;
	}

	/**
	 * @param $jobCode
	 * @param $jobConfig
	 * @param $from
	 * @param $until
	 * @return Zero1_Crondoctor_Model_Visual_Schedule_Job
	 */
	public function getScheduleJob($jobCode, $jobConfig, $from, $until){

		$scheduleJob = Mage::getModel('zero1_crondoctor/visual_schedule_job', array(
			'job_code' => $jobCode,
			'job_config' => $jobConfig,
			'from' => $from,
			'until' => $until,
		));

		return $scheduleJob;
	}

	public function getSchedule(){
		return $this->schedule;
	}

	public function orderBy($orderBy){
		$this->orderingBy = $orderBy;
		uasort($this->schedule, array($this, 'uasort'));
	}

	private function uasort($a, $b){
		/* @var $a Zero1_Crondoctor_Model_Visual_Schedule_Job */
		/* @var $b Zero1_Crondoctor_Model_Visual_Schedule_Job */
		if ($a->getStatValue($this->orderingBy) == $b->getStatValue($this->orderingBy)) {
			return 0;
		}
		return ($a->getStatValue($this->orderingBy) < $b->getStatValue($this->orderingBy)) ? 1 : -1;
	}
}