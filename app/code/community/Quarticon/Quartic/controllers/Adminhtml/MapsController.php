<?php

class Quarticon_Quartic_Adminhtml_MapsController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout();
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    protected function massSaveAction()
    {
        $data = $this->getRequest()->getParam('mapped');
        foreach ($data as $id => $value) {
            $model = Mage::getModel('quartic/maps')->load($id);
            $model->setData('magento_attribute', $value);
            $model->save();
        }
        $this->_redirect("*/*/");
    }
}
