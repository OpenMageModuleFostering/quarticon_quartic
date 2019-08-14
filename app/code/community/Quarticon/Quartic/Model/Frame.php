<?php

class Quarticon_Quartic_Model_Frame extends Mage_Core_Model_Abstract
{

    protected function getConfig()
    {
        return Mage::getModel('quartic/config');
    }

    public function isActive()
    {
        return $this->getConfig()->isActive();
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
        $div_id = '_qON_'.$placement['frame_parent'].'_'.$placement['frame_id'].'_'.$placement['block_position'];


        return array(
            'div_id' => $div_id,
            'body' => "<div id=\"" . $div_id . "\" class=\"qON_placeholder\"></div>"
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
