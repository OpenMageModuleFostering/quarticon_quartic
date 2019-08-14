<?php

class Quarticon_Quartic_Model_Frame extends Mage_Core_Model_Abstract
{

    protected function getConfig()
    {
        return Mage::getModel('quartic/config');
    }

    public function isActive()
    {
        return true;
        //return $this->getConfig()->isActive() && $this->getConfig()->isFrameEnabled($this->getFrameName());
    }

    public function getCustomer()
    {
        return $this->getConfig()->getCustomer();
    }

    public function getUser()
    {
        return $this->getConfig()->getSession()->getCustomer()->getId();
    }

    public function getSnippetBody()
    {
        $frame = $this->getData('frame');
        if (empty($frame)) {
            $frame = $this->getPlacementFrame();
            $this->setData('frame', $frame);
        }
        return $frame['body'];
    }

    public function getSnippetId()
    {
        $frame = $this->getData('frame');
        if (empty($frame)) {
            $frame = $this->getPlacementFrame();
            $this->setData('frame', $frame);
        }
        return $frame['div_id'];
    }

    public function getPlacementFrame()
    {
        $placement = $this->getPlacement();
        $frame = Mage::getModel('quartic/placement')
            ->getCollection()
            ->addFilter('id', array('eq' => $placement['frame_id']))
            //->addFilter('parent_name', array('eq' => $placement['frame_parent']))
            ->getFirstItem()
        ;
        $check = $frame->getId();
        if (empty($check)) {
            return $this->getDefaultFrame();
        }
        return array(
            'div_id' => $frame->getDivId(),
            'body' => $frame->getSnippet()
        );
    }

    public function getDefaultFrame()
    {
        $div_id = $this->getFrameDivId();
        return array(
            'div_id' => $div_id,
            'body' => "<div id=\"" . $div_id . "\"></div>"
        );
    }
}
