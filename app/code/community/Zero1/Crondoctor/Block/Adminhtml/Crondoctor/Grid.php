<?php
class Zero1_Crondoctor_Block_Adminhtml_Crondoctor_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('crondoctor');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        //$this->setUseAjax(true);
    }

    /**
     * Prepare related item collection
     *
     * @return Zero1_Crondoctor_Block_Adminhtml_Crondoctor_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cron/schedule')->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Enterprise_GiftWrapping_Block_Adminhtml_Giftwrapping_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('schedule_id', array(
            'header' => Mage::helper('zero1_crondoctor')->__('ID'),
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'schedule_id'
        ));

        $job_code_options = array();
        $job_code_collection = Mage::getModel('cron/schedule')->getCollection();
        $job_code_collection->getSelect()->group('job_code');
        foreach($job_code_collection as $job_code)
        	$job_code_options[$job_code->getJobCode()] = ucwords(str_replace('_', ' ', $job_code->getJobCode()));
        
        $this->addColumn('job_code', array(
            'header' => Mage::helper('zero1_crondoctor')->__('Job Code'),
            'index'  => 'job_code',
        	'type'    => 'options',
            'options' => $job_code_options
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('zero1_crondoctor')->__('Status'),
            'index'  => 'status',
            'type'    => 'options',
            'options' => array(
                Mage_Cron_Model_Schedule::STATUS_PENDING => Mage::helper('zero1_crondoctor')->__('Pending'),
                Mage_Cron_Model_Schedule::STATUS_RUNNING => Mage::helper('zero1_crondoctor')->__('Running'),
                Mage_Cron_Model_Schedule::STATUS_SUCCESS => Mage::helper('zero1_crondoctor')->__('Success'),
                Mage_Cron_Model_Schedule::STATUS_MISSED => Mage::helper('zero1_crondoctor')->__('Missed'),
                Mage_Cron_Model_Schedule::STATUS_ERROR => Mage::helper('zero1_crondoctor')->__('Error'),
            )
        ));

        $this->addColumn('messages', array(
            'header' => Mage::helper('zero1_crondoctor')->__('Messages'),
            'index'  => 'messages'
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('zero1_crondoctor')->__('Created At'),
            'type'   => 'datetime',
            'index'  => 'created_at'
        ));

        $this->addColumn('scheduled_at', array(
            'header' => Mage::helper('zero1_crondoctor')->__('Scheduled At'),
            'type'   => 'datetime',
            'index'  => 'scheduled_at'
        ));

        $this->addColumn('executed_at', array(
            'header' => Mage::helper('zero1_crondoctor')->__('Executed At'),
            'type'   => 'datetime',
            'index'  => 'executed_at'
        ));

        $this->addColumn('finished_at', array(
            'header' => Mage::helper('zero1_crondoctor')->__('Finished At'),
            'type'   => 'datetime',
            'index'  => 'finished_at'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve row url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
    	return null;
    	
        return $this->getUrl('*/*/edit', array(
            'id' => $row->getId()
        ));
    }
    
    protected function _prepareMassaction()
    {
    	$this->setMassactionIdField('schedule_id');
    	$this->getMassactionBlock()->setFormFieldName('crondoctor');
    	$this->setNoFilterMassactionColumn(true);
    
    	$this->getMassactionBlock()->addItem('delete', array(
    			'label'    => $this->__('Delete'),
    			'url'      => $this->getUrl('*/*/massDelete', array('_current' => true)),
    			'confirm'  => $this->__('Are you sure?')
    	));
    
    	$this->getMassactionBlock()->addItem('change_status', array(
    			'label'    => $this->__('Change Status'),
    			'url'      => $this->getUrl('*/*/massChange', array('_current' => true)),
    			'confirm'  => $this->__('Are you sure?'),
    			'additional'    => array(
    					'mode'      => array(
    							'name'      => 'status',
    							'type'      => 'select',
    							'class'     => 'required-entry',
    							'label'     => Mage::helper('index')->__('Status'),
    							'values'    => array(
					                Mage_Cron_Model_Schedule::STATUS_PENDING => Mage::helper('zero1_crondoctor')->__('Pending'),
					                Mage_Cron_Model_Schedule::STATUS_RUNNING => Mage::helper('zero1_crondoctor')->__('Running'),
					                Mage_Cron_Model_Schedule::STATUS_SUCCESS => Mage::helper('zero1_crondoctor')->__('Success'),
					                Mage_Cron_Model_Schedule::STATUS_MISSED => Mage::helper('zero1_crondoctor')->__('Missed'),
					                Mage_Cron_Model_Schedule::STATUS_ERROR => Mage::helper('zero1_crondoctor')->__('Error'),
					            )
    					)
    			)
    	));
    	
    	return $this;
    }
}
