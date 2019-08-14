<?php

/**
 * Frontend events observer
 * Used to insert our custom blocks anywhere
 */
class Quarticon_Quartic_Model_Observer_Frontend
{

    /**
     * Places loaded in constructor
     * @var array
     */
    protected $places = array();

    /**
     * Constructor method.
     * Load configured insertion places and get places data from models
     *
     * TODO: Add some caching there and drop the cache on backend action
     */
    public function __construct()
    {
        /* @var $insert_model Quarticon_Quartic_Model_Insert */
        $insert_model = Mage::getModel('quartic/insert');

        $this->places = array();
        $this->places = $insert_model->loadPlaces('quartic/frames_homepage', 'quartic/insert_home', $this->places);
        $this->places = $insert_model->loadPlaces('quartic/frames_product', 'quartic/insert_product', $this->places);
        $this->places = $insert_model->loadPlaces('quartic/frames_cart', 'quartic/insert_cart', $this->places);
        $this->places = $insert_model->loadPlaces('quartic/frames_category', 'quartic/insert_category', $this->places);
        //var_dump($this->places);
    }

    /**
     * Insert quartic blocks where necessary
     *
     * @param type $observer
     */
    public function blockAbstractToHtmlAfter($observer)
    {
        $event = $observer->getEvent();
        /*
         * Observed block
         */
        $block_name = $event->getBlock()->getNameInLayout();
        /*
         * Check if it was selected in config
         */
        if (isset($this->places[$block_name])) {
            foreach ($this->places[$block_name] as $placement) {
                /**
                 * Optional layout handle requirement
                 */
                if (isset($placement['block_layout'])) {
                    $handles = $event->getBlock()->getLayout()->getUpdate()->getHandles();
                    if (!in_array($placement['block_layout'], $handles)) {
                        return;
                    }
                }
                /*
                 * Get block html
                 */
                $html = $event->getTransport()->getHtml();
                /*
                 * Get QON frame html
                 */
                //var_dump($placement);
                $frame = $event->getBlock()->getLayout()
                    ->createBlock($placement['frame_block'])
                    ->setTemplate($placement['frame_template'])
                    ->setPlacement($placement)
                    ->toHtml()
                ;
                /*
                 * Combine htmls and insert them
                 */
                if ($placement['block_position'] == 'before') {
                    $event->getTransport()->setHtml($frame . $html);
                } else {
                    $event->getTransport()->setHtml($html . $frame);
                }
            }
        }
    }

    public function controllerFrontSendResponseBefore($observer)
    {
        $front = $observer->getFront();
        $front->getResponse()->setHeader('Access-Control-Allow-Origin','api.quarticon.com,api.quartic.pl');
    }
}
