<?php
class Zero1_Crondoctor_Block_Adminhtml_Visual_Visual extends Mage_Core_Block_Template{


	protected $_template = 'crondoctor/visual/visual.phtml';
	private $schedule = null;

	public function _construct(){
		$this->setMinumMinDisplayWidth(12);
	}

	/* @return Zero1_Crondoctor_Model_Schedule */
	public function getSchedule(){
		if($this->schedule === null){
			$schedule = Mage::getModel('zero1_crondoctor/visual_schedule', array(
				'until' => $this->getTo(),
				'from' => $this->getFrom(),
			));

			$schedule->orderBy($this->getOrderBy());
			$this->schedule = $schedule;
		}
		return $this->schedule;
	}

	public function getJobHtml(Zero1_Crondoctor_Model_Schedule_Job $job, $from, $width){

		$jobDepthInfo = array();
		$jobHeight = 10;
		$jobProccessHtml = '';
		$html = '';

		foreach($job->getProcesses() as $timestamp => $process){

			if(!empty($jobDepthInfo)){
				$depthFound = false;

				$minValue = $this->getLowestSetValue($process);

				foreach($jobDepthInfo as $depth => $maxValue){
					if($minValue > $maxValue){
						$depthFound = true;
						break;
					}
				}

				if(!$depthFound){
					$jobDepth = count($jobDepthInfo);
				}else{
					$jobDepth = $depth;
				}
			}else{
				$jobDepth = 0;
			}

			$jobProccessHtml .= $this->getJobProcessHtml($process, $jobDepth, $jobHeight, $from);

			$jobDepthInfo[$jobDepth] = max(
				$process->getExecutedAt(),
				$process->getScheduledAt(),
				$process->getFinishedAt(),
				$process->getCreatedAt()
			);

		}

		$html .= '<div style="width: '.$width.'px; height: '.(max(count($jobDepthInfo), 4) * $jobHeight).'px; ';
		$html .= ' border-bottom: solid #000000 2px; background-color: #cccccc; position: relative;">';
		$html .= $jobProccessHtml;
		$html .= '<div style="clear: both;"></div></div>';

		return array($html, max(count($jobDepthInfo), 4));
	}

	public function getLowestSetValue(Zero1_Crondoctor_Model_Schedule_Job_Process $process){
		$values = array();
		if($process->getCreatedAt()){
			$values[] = $process->getCreatedAt();
		}
		if($process->getUpdatedAt()){
			$values[] = $process->getUpdatedAt();
		}
		if($process->getScheduledAt()){
			$values[] = $process->getScheduledAt();
		}
		if($process->getExecutedAt()){
			$values[] = $process->getExecutedAt();
		}
		if($process->getFinishedAt()){
			$values[] = $process->getFinishedAt();
		}
		return min($values);
	}

	public function getJobProcessHtml(Zero1_Crondoctor_Model_Visual_Schedule_Job_Process $process, $jobDepth, $jobHeight, $from){

		$html = '<div id="schedule-id-'.$process->getScheduleId().'">';

		if($process->getCreatedAt()){
			//time from when it was created, to the time it is scheduled to start
			$html .= $this->createBlock($jobDepth, $jobHeight, $from, $process->getCreatedAt(), $process->getScheduledAt(), 'yellow');
			if($process->getExecutedAt()){
				//time from when it should have started to the time it actually did start
				$html .= $this->createBlock($jobDepth, $jobHeight, $from, $process->getScheduledAt(), $process->getExecutedAt(), 'orange');
				if($process->getFinishedAt()){
					//job ran good, show execution time
					$html .= $this->createBlock($jobDepth, $jobHeight, $from, $process->getExecutedAt(), $process->getFinishedAt(), 'green');
				}else{
					//job error'd
					$html .= $this->createBlock($jobDepth, $jobHeight, $from, $process->getExecutedAt(), $process->getFinishedAt(), 'pink');
				}
			}else{
				//job missed
				$html .= $this->createBlock($jobDepth, $jobHeight, $from, $process->getScheduledAt(), $process->getUpdatedAt(), 'orange');

				if($process->getStatus() == Mage_Cron_Model_Schedule::STATUS_MISSED){
					$html .= $this->createBlock($jobDepth, $jobHeight, $from, $process->getUpdatedAt(), $process->getUpdatedAt(), 'red');
				}
			}
		}else{
			//job never got scheduled
			$html .= $this->createBlock($jobDepth, $jobHeight, $from, $process->getScheduledAt(), $process->getExecutedAt(), 'black');
		}

		//add in data for later
		$html .= '<div style="display: none;">';
		$html .= 'status:'.$process->getStatus().'<br />';
		$html .= 'schedule id: '.$process->getScheduledId().'<br />';
		$html .= 'created at: '.strftime('%Y-%m-%d %H:%M', $process->getCreatedAt()).'<br />';
		$html .= 'scheduled at: '.strftime('%Y-%m-%d %H:%M', $process->getScheduledAt()).'<br />';
		$html .= 'executed at: '.strftime('%Y-%m-%d %H:%M:%S', $process->getExecutedAt()).'<br />';
		$html .= 'finished at: '.strftime('%Y-%m-%d %H:%M:%S', $process->getFinishedAt()).'<br />';
		$html .= 'updated at: '.strftime('%Y-%m-%d %H:%M:%S', $process->getUpdatedAt()).'<br />';
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

	public function createBlock($jobDepth, $jobHeight, $from, $startTime, $endTime, $colour){

		$minMinuteDisplayWith = $this->getMinumMinDisplayWidth();
		if(!$startTime){
			return '';
		}

		$top = ($jobDepth * $jobHeight);

		if($from > $startTime){
			$left = 0;
		}else{
			$left =(((($startTime - $from)/60)*$minMinuteDisplayWith) + 20);
		}

		if(!$endTime){
			$width = 2;
		}else{
			if($from > $startTime){
				$width = max((((($endTime - $from)/60)*$minMinuteDisplayWith)+20), 2);
			}else{
				$width = max(((($endTime - $startTime)/60)*$minMinuteDisplayWith), 2);
			}
		}

		$html = '<div ';
		$html .= 'style="position: absolute; ';
		$html .= 'height: '.$jobHeight.'px; ';
		$html .= 'top: '.$top.'px; ';
		$html .= 'left: '.$left.'px; ';
		$html .= 'background-color: '.$colour.'; ';
		$html .= 'width: '.$width.'px; ';
		$html .= '"></div>';

		return $html;
	}


}