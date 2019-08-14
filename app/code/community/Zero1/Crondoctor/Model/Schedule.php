<?php
class Zero1_Crondoctor_Model_Schedule extends Mage_Cron_Model_Schedule
{
    protected function _beforeSave(){
        parent::_beforeSave();
        $this->setUpdatedAt(strftime('%Y-%m-%d %H:%M:%S', time()));
    }

}