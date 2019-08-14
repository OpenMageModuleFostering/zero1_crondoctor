<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sitemap edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Zero1_Crondoctor_Block_Adminhtml_Visual_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('redirection_form');
        $this->setTitle(Mage::helper('adminhtml')->__('Redirection Information'));
    }


    protected function _prepareForm()
    {
        $model = new Varien_Object();

        $form = new Varien_Data_Form(array(
            'id'        => 'visual_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset(
			'crondoctor_visual_form',
			array('legend' => Mage::helper('adminhtml')->__('Options')));


        $fieldset->addField('from', 'text', array(
            'label' => Mage::helper('adminhtml')->__('From'),
            'name'  => 'from',
            'required' => true,
            'note'  => Mage::helper('adminhtml')->__('number of hours from now (accepts -ve and +ve'),
        ));

		$fieldset->addField('to', 'text', array(
			'label' => Mage::helper('adminhtml')->__('To'),
			'name'  => 'to',
			'required' => true,
			'note'  => Mage::helper('adminhtml')->__('number of hours from now (accepts -ve and +ve'),
		));

		$fieldset->addField('visual_type', 'select', array(
			'label' => Mage::helper('adminhtml')->__('Visual Type'),
			'name'  => 'visual_type',
			'required' => true,
			'values' => array(
				'stats' => Mage::helper('adminhtml')->__('Stats'),
				'lag' => Mage::helper('adminhtml')->__('Lag'),
			),
		));

		$fieldset->addField('order_by', 'select', array(
			'label' => Mage::helper('adminhtml')->__('Order By'),
			'name'  => 'order_by',
			'required' => true,
			'values' => array(
				'expected_runs' => Mage::helper('adminhtml')->__('Expected Runs'),
			),
		));

		$fieldset->addField('submit', 'button', array(
			'label' => Mage::helper('adminhtml')->__(''),
			'name' => Mage::helper('adminhtml')->__('aaa'),
			'class' => 'save',
			'onclick' => '
				new Ajax.Request(\''.Mage::helper('adminhtml')->getUrl('adminhtml/crondoctor_visual/ajax').'\',
					{
					method: \'get\',
					parameters: Form.serialize(\'visual_form\'),
					onSuccess: function(transport){
						$(\'visual-container\').update(transport.responseText);
					}
				});'
		));

		$form->setValues(array('submit' => 'Submit'));
        $form->setUseContainer(true);
        $this->setForm($form);

		$this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
			->addFieldMap('visual_type', 'visual_type')
			->addFieldMap('order_by', 'order_by')
			->addFieldDependence('order_by', 'visual_type', 'stats')
		);


        return parent::_prepareForm();
    }

}
