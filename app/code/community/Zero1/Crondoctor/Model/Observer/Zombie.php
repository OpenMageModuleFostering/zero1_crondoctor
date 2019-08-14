<?php
class Zero1_Crondoctor_Model_Observer_Zombie{

    const XML_PATH_ZOMBIE_EMAIL_TEMPLATE = 'zero1_crondoctor/settings/zombie_email_template';
    const XML_PATH_ZOMBIE_EMAIL_TO = 'zero1_crondoctor/settings/zombie_email';
    const XML_PATH_ZOMBIE_TIME = 'zero1_crondoctor/settings/zombie_time';

    const XML_PATH_DEVELOPER_MODE = 'zero1_crondoctor/settings/developer_mode';
    const XML_PATH_DEVELOPER_MODE_JOBS = 'zero1_crondoctor/settings/developer_mode_jobs';

    protected $_zombieEmailSubject = 'Magento Cron Doctor Zombie Report';

    public function checkForZombieJobs(Varien_Event_Observer $event){
        $storeId = Mage::app()->getStore()->getId();
        $to = Mage::getStoreConfig(self::XML_PATH_ZOMBIE_EMAIL_TO, $storeId);

        if(!$to) {
            return; // No destination address.
        }

        $cronjob_collection = Mage::getModel('cron/schedule')->getCollection();
        $cronjob_collection->addFieldToFilter('job_code', array('nin' => array(
            'enterprise_refresh_index', //Ignore always jobs, TODO <<properly
        )));
        $cronjob_collection->addFieldToFilter('status', array(
                'eq' => Mage_Cron_Model_Schedule::STATUS_RUNNING)
        );

        $job_list_content = '';
        foreach($cronjob_collection as $cronjob) {
            if($cronjob->getReportedAt()) {
                continue;   // No need to report more then once.
            }

            $running_time = ceil((time() - strtotime($cronjob->getExecutedAt())) / 60);

            if($running_time >= Mage::getStoreConfig(self::XML_PATH_ZOMBIE_TIME, $storeId)) {
                $job_list_content .= '"'.ucwords(str_replace('_', ' ', $cronjob->getJobCode()))."'";
                $job_list_content .= ' has been running for '.$running_time.' minutes.<br/>';

                $cronjob->setReportedAt(strftime('%Y-%m-%d %H:%M:%S', time()));
                $cronjob->save();
            }
        }

        if($job_list_content != '') {
            $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(false);

            Mage::getModel('core/email_template')
                ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                ->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_ZOMBIE_EMAIL_TEMPLATE, $storeId),
                    Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $storeId),
                    $to,
                    null,
                    array(
                        'subject' => $this->_zombieEmailSubject,
                        'job_list_content' => $job_list_content,
                    )
                );

            $translate->setTranslateInline(true);
        }
    }
}