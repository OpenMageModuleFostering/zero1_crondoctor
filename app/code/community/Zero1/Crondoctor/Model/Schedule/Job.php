<?php

/**
 * Class Zero1_Crondoctor_Model_Schedule_Job
 *
 * @method string getJobCode()
 * @method Zero1_CronDebug_Model_Schedule_Job setJobConfig(Mage_Core_Model_Config_Element $config)
 * @method Mage_Core_Model_Config_Element getJobConfig()
 * @method Zero1_CronDebug_Model_Schedule_Job setScheduleFrom(int $timestamp)
 * @method int getScheduleFrom()
 * @method Zero1_CronDebug_Model_Schedule_Job setScheduleUntil(int $timestamp)
 * @method int getScheduleUntil()
 * @method Zero1_CronDebug_Model_Schedule_Job setJobType(string $type)
 * @method string getJobType()
 */
class Zero1_Crondoctor_Model_Schedule_Job extends Mage_Cron_Model_Schedule{

	private $messages = array();
	private $processes = array();
	private $stats = null;

	const TYPE_DEFAULT = 'default';
	const TYPE_ALWAYS = 'always';

	public function __construct(){

		$args = func_get_args();
		if (empty($args[0])) {
			$args[0] = array();
		}

		if(!isset($args[0]['job_code'], $args[0]['job_config'], $args[0]['from'], $args[0]['until'])){
			 throw new InvalidArgumentException(
				 'Missing an argument, expecting job_code, job_config, from, until. Got: '.json_encode(array_keys($args[0]))
			 );
		}

		$this->setJobCode($args[0]['job_code']);
		$this->setJobConfig($args[0]['job_config']);
		$this->setScheduleFrom($args[0]['from']);
		$this->setScheduleUntil($args[0]['until']);
		$this->setJobType(($this->getCronExpr() == 'always')? self::TYPE_ALWAYS : self::TYPE_DEFAULT);

		$this->generateSchedule($this->getScheduleFrom(), $this->getScheduleUntil());
	}

	/** @return String */
	public function getCronExpr(){

		$cronExpr = $this->getData('cron_expr');
		if($cronExpr === null){

			$cronExpr = '';

			if($this->getJobConfig()->schedule->config_path){
				$cronExpr = Mage::getStoreConfig((string)$this->getJobConfig()->schedule->config_path);
			}

			if(empty($cronExpr) && $this->getJobConfig()->schedule->cron_expr){
				$cronExpr = (string)$this->getJobConfig()->schedule->cron_expr;
			}

			$this->setData('cron_expr', $cronExpr);
		}

		return $this->getData('cron_expr');
	}

	public function getCronExprArr(){

		if($this->getData('cron_expr_arr') === null){
			$cronExpr = $this->getCronExpr();
			if($cronExpr == '' || $this->getJobType() == self::TYPE_ALWAYS){
				$this->setData('cron_expr_arr', array());
			}else{
				$e = preg_split('#\s+#', $cronExpr, null, PREG_SPLIT_NO_EMPTY);
				if (sizeof($e)<5 || sizeof($e)>6) {
					$this->addScheduleMessage('Cron Expr is invalid: '.$cronExpr);
					$this->setData('cron_expr_arr', array());
				}else{
					$this->setData('cron_expr_arr', $e);
				}
			}
		}
		return $this->getData('cron_expr_arr');
	}

	private function generateSchedule($from, $until){

		$cronExprArr = $this->getCronExprArr();

		if(empty($cronExprArr)){
			return;
		}

		$from -= ($from % 60); //make sure its on an exact min


		while($from < $until){

			$d = getdate(Mage::getSingleton('core/date')->timestamp($from));

			$match = $this->matchCronExpression($cronExprArr[0], $d['minutes'])
				&& $this->matchCronExpression($cronExprArr[1], $d['hours'])
				&& $this->matchCronExpression($cronExprArr[2], $d['mday'])
				&& $this->matchCronExpression($cronExprArr[3], $d['mon'])
				&& $this->matchCronExpression($cronExprArr[4], $d['wday']);

			if($match){

				$this->addProcess(
					Mage::getModel('zero1_crondoctor/schedule_job_process', array(
						'scheduled_at' => $from,
						'job' => $this,
					))
				);
			}

			$from += 60;
		}
	}

	private function addProcess(Zero1_Crondoctor_Model_Schedule_Job_Process $process){
		$this->processes[$process->getScheduledAt()] = $process;
	}
	public function getProcesses(){
		return $this->processes;
	}


	private function addScheduleMessage($msg){
		$this->messages[] = $msg;
	}
	private function getScheduleMessages($lineBreak = '<br />'){
		return implode($lineBreak, $this->messages);
	}

	public function getStatValue($key){
		$stats = $this->getStats();
		if(isset($stats[$key], $stats[$key]['value'])){
			return $stats[$key]['value'];
		}else{
			return 0;
		}
	}

	public function getStats(){
		if(!$this->stats){
			$this->stats = array(
				'scheduled_misses' => $this->getScheduledMisses(),
				'expected_runs' => $this->calculateExpectedRuns(),
			);

		}
		return $this->stats;
	}

	private function getScheduledMisses(){
		$misses = 0;
		/* @var $process Zero1_Crondoctor_Model_Schedule_Job_Process */
		foreach($this->processes as $timestamp => $process){
			if($process->getStatus() == Mage_Cron_Model_Schedule::STATUS_MISSED){
				$misses++;
			}
		}
		return array(
			'name' => 'Scheduled Misses',
			'value' => $misses,
			'description' => 'Number of times the job has been schedule, but then missed (due to being run too late)',
		);
	}

	private function calculateExpectedRuns(){
		return array(
			'name' => 'Expected Runs',
			'value' => count($this->getProcesses()),
			'description' => 'Number of times the job should run in a perfect world',
		);
	}


}