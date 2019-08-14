<?php
class Zero1_Crondoctor_Model_Observer extends Mage_Cron_Model_Observer
{
    const XML_PATH_DEVELOPER_MODE = 'zero1_crondoctor/settings/developer_mode';
    const XML_PATH_DEVELOPER_MODE_JOBS = 'zero1_crondoctor/settings/developer_mode_jobs';
    const STOP_FLAG_NAME = 'cron_stop.flag';

    private $emulateRunTimes = array(
        'core/observer::cleanCache' => 20,
        'directory/observer::scheduledUpdateCurrencyRates' => 20,
        'catalog/observer::reindexProductPrices' => 20,
        'catalogrule/observer::dailyCatalogUpdate' => 20,
        'sales/observer::cleanExpiredQuotes' => 20,
        'sales/observer::aggregateSalesReportOrderData' => 20,
        'sales/observer::aggregateSalesReportShipmentData' => 20,
        'sales/observer::aggregateSalesReportInvoicedData' => 20,
        'sales/observer::aggregateSalesReportRefundedData' => 20,
        'sales/observer::aggregateSalesReportBestsellersData' => 20,
        'salesrule/observer::aggregateSalesReportCouponsData' => 20,
        'backup/observer::scheduledBackup' => 20,
        'paypal/observer::fetchReports' => 20,
        'log/cron::logClean' => 20,
        'tax/observer::aggregateSalesReportTaxData' => 20,
        'productalert/observer::process' => 20,
        'captcha/observer::deleteOldAttempts' => 20,
        'captcha/observer::deleteExpiredImages' => 20,
        'newsletter/observer::scheduledSend' => 20,
        'persistent/observer::clearExpiredCronJob' => 20,
        'xmlconnect/observer::scheduledSend' => 20,
        'awcore/logger::exorcise' => 20,
        'followupemail/cron::cronJobs' => 20,
        'followupemail/crondaily::cronJobs' => 20,
        'amazonpayments/observer::rotateLogfiles' => 20,
        'amazonpayments/observer::pollObjectsData' => 20,
        'enterprise_index/observer::refreshIndex' => 20,
        'enterprise_index/cron::scheduledCleanup' => 20,
        'enterprise_giftcardaccount/cron::updateStates' => 20,
        'enterprise_giftcardaccount/pool::applyCodesGeneration' => 20,
        'enterprise_logging/observer::rotateLogs' => 20,
        'seoredirects/observer::updateRedirectionsCronJob' => 20,
        'enterprise_reminder/observer::scheduledNotification' => 20,
        'enterprise_reward/observer::scheduledBalanceExpireNotification' => 20,
        'enterprise_reward/observer::scheduledPointsExpiration' => 20,
        'enterprise_salesarchive/observer::archiveOrdersByCron' => 20,
        'enterprise_search/indexer_indexer::reindexAll' => 20,
        'enterprise_targetrule/index::cron' => 20,
        'enterprise_catalog/index_observer_price::refreshSpecialPrices' => 20,
        'enterprise_staging/observer::automates' => 20,
        'M2ePro/Cron_Type_Magento::process' => 1200,
        'xsitemap/observer::scheduledGenerateSitemaps' => 20,
        'ops/observer::cleanUpOldPaymentData' => 20,
        'TrueShopping_UltraNotificationImport/observer::import' => 20,
        'wsalogger/log::truncate' => 20,
        'bibit/observer::checkNotify' => 180,
        'channeladvisor/observer::pullOrders' => 20,
        'channeladvisor/observer::syncStock' => 120,
        'channeladvisor/observer::syncProducts' => 20,
        'channeladvisorcse/observer::scheduledGenerateChanneladvisorCSE' => 20,
        'zero1_crondoctor/observer::checkForZombieJobs' => 1,
        'zero1_feeds/observer_temp::mail' => 20,
        'solvitt/observer::receive' => 90,
        'rmareport/observer::aggregateRmareportReportRmasData' => 20,
        'enterprise_pagecache/crawler::crawl' => 20,
        'enterprise_importexport/observer::scheduledLogClean' => 20,
        'sitemap/observer::scheduledGenerateSitemaps' => 20,
        'orderspro/observer::scheduledArchiveOrders' => 20,
        'enterprise_importexport/observer::processScheduledOperation' => 20,
    );

    /**
     * Process cron queue
     * Generate tasks schedule
     * Cleanup tasks schedule
     *
     * @param Varien_Event_Observer $observer
     */
    public function dispatch($observer)
    {
        /* @var $schedules Mage_Cron_Model_Resource_Schedule_Collection */
        $jobsRoot = Mage::getConfig()->getNode('crontab/jobs');
        $defaultJobsRoot = Mage::getConfig()->getNode('default/crontab/jobs');

        $nextJob = $this->getNextJob();

        $maxIterationCounter = 0;
        while($nextJob->count() == 1 && !$this->shouldStop() && $maxIterationCounter < 1000){
            $maxIterationCounter++;
            /** @var $schedule Mage_Cron_Model_Schedule */
            $schedule = $nextJob->getFirstItem();

            $jobConfig = $jobsRoot->{$schedule->getJobCode()};
            if (!$jobConfig || !$jobConfig->run) {
                $jobConfig = $defaultJobsRoot->{$schedule->getJobCode()};
                if (!$jobConfig || !$jobConfig->run) {
                    continue;
                }
            }
            $this->_processJob($schedule, $jobConfig);

            $this->cleanUpJob($schedule);
            $nextJob = $this->getNextJob();
        }
    }

    /* @return Mage_Cron_Model_Resource_Schedule_Collection */
    private function getNextJob(){
        /* @var $collection Mage_Cron_Model_Resource_Schedule_Collection */
        $collection = Mage::getModel('cron/schedule')->getCollection()
            ->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_PENDING)
            ->addFieldToFilter('scheduled_at', array('lteq' => strftime('%Y-%m-%d %H:%M:00', time())))
            ->addOrder('scheduled_at', 'ASC');
        $collection->getSelect()->limit(1);
        $collection->load();
        return $collection;
    }

    private function cleanUpJob(Mage_Cron_Model_Schedule $schedule){
        /* @var $collection Mage_Cron_Model_Resource_Schedule_Collection */
        $collection = Mage::getModel('cron/schedule')->getCollection()
            ->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_PENDING)
            ->addFieldToFilter('job_code', $schedule->getJobCode())
            ->addFieldToFilter('scheduled_at', array('lt' => strftime('%Y-%m-%d %H:%M:00', time())));

        /* @var $oldSchedule Mage_Cron_Model_Schedule */
        foreach($collection as $oldSchedule){
            $oldSchedule->setStatus(Mage_Cron_Model_Schedule::STATUS_MISSED)->save();
        }
    }

    public static function shouldStop(){
        if(file_exists(self::STOP_FLAG_NAME)){
            //Mage::log('stop flag found', Zend_Log::DEBUG, 'cron.log', true);
            return true;
        }else{
            return false;
        }
    }

    public function schedule(Varien_Event_Observer $observer){
        $this->generate();
        $this->cleanup();
    }

    protected function _generateJobs($jobs, $exists)
    {
        $devMode = Mage::getStoreConfig(self::XML_PATH_DEVELOPER_MODE);
        $devModeJobs = Mage::getStoreConfig(self::XML_PATH_DEVELOPER_MODE_JOBS);
        $devModeJobs = preg_replace('/[^A-Za-z0-9_,]*/', '', $devModeJobs);
        $devModeJobs = explode(',', $devModeJobs);

        if ($devMode) {
            $devJobs = array();     // By default, don't run anything if in dev mode.

            if (is_array($devModeJobs) && count($devModeJobs) > 0) {
                foreach ($jobs as $jobCode => $jobConfig) {
                    if (in_array($jobCode, $devModeJobs)) {
                        $devJobs[$jobCode] = $jobConfig;
                    }
                }
            }
            return parent::_generateJobs($devJobs, $exists);
        }
        return parent::_generateJobs($jobs, $exists);
    }

    /**
     * Process cron task
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @param $jobConfig
     * @param bool $isAlways
     * @return Mage_Cron_Model_Observer
     */
    protected function _processJob($schedule, $jobConfig, $isAlways = false)
    {
        $runConfig = $jobConfig->run;
        if (!$isAlways) {
            $scheduleLifetime = Mage::getStoreConfig(self::XML_PATH_SCHEDULE_LIFETIME) * 60;
            $now = time();
            $time = strtotime($schedule->getScheduledAt());
            if ($time > $now) {
                return;
            }
        }

        $errorStatus = Mage_Cron_Model_Schedule::STATUS_ERROR;
        try {
            if (!$isAlways) {
                if (($scheduleLifetime != 0) && ($time < $now - $scheduleLifetime)) {
                    $errorStatus = Mage_Cron_Model_Schedule::STATUS_MISSED;
                    Mage::throwException(Mage::helper('cron')->__('Too late for the schedule.'));
                }
            }
            if ($runConfig->model) {
                if (!preg_match(self::REGEX_RUN_MODEL, (string)$runConfig->model, $run)) {
                    Mage::throwException(Mage::helper('cron')->__('Invalid model/method definition, expecting "model/class::method".'));
                }
                if (!($model = Mage::getModel($run[1])) || !method_exists($model, $run[2])) {
                    Mage::throwException(Mage::helper('cron')->__('Invalid callback: %s::%s does not exist', $run[1], $run[2]));
                }
                $callback = array($model, $run[2]);
                $arguments = array($schedule);
            }
            if (empty($callback)) {
                Mage::throwException(Mage::helper('cron')->__('No callbacks found'));
            }

            if (!$isAlways) {
                if (!$schedule->tryLockJob()) {
                    // another cron started this job intermittently, so skip it
                    return;
                }
                /**
                though running status is set in tryLockJob we must set it here because the object
                was loaded with a pending status and will set it back to pending if we don't set it here
                 */
            }

            $schedule
                ->setExecutedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                ->setStatus(Mage_Cron_Model_Schedule::STATUS_RUNNING)
                ->save();

            call_user_func_array($callback, $arguments);

            /* Emulation Code
             *
            $runModel = (string)$jobConfig->run->model;
            if(isset($this->emulateRunTimes[$runModel])){
                Mage::log($schedule->getJobCode().' '.$runModel.'::'.$this->emulateRunTimes[$runModel], Zend_Log::DEBUG, 'cron.log', true);

                $lateness = time() - strtotime($schedule->getScheduledAt());
                $latenessFactor = 0.01; //for every 100 seconds late it increase the run time by 1
                $runtime = ($lateness * $latenessFactor) + $this->emulateRunTimes[$runModel];

                if($schedule->getJobCode() == 'enterprise_refresh_index'){
                    $runtime = 45;
                }

                Mage::log('runtime: '.$runtime, Zend_Log::DEBUG, 'cron.log', true);
                sleep($runtime);
            }else{
                Mage::log($runModel.'::no sleep time', Zend_Log::DEBUG, 'cron.log', true);
            }
            */

            //need to reload so we don't overwrite anything that has changed since running
            $schedule->load($schedule->getId());
            $schedule
                ->setStatus(Mage_Cron_Model_Schedule::STATUS_SUCCESS)
                ->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()));

        } catch (Exception $e) {
            $schedule->setStatus($errorStatus)
                ->setMessages($e->__toString());
        }
        $schedule->save();

        return $this;
    }
}
