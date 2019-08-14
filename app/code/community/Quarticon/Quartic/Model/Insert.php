<?php

/**
 * Frame placements - common
 *
 * For example data:
 * @see Quarticon_Quartic_Model_Insert_Home
 * For insertion code:
 * @see Quarticon_Quartic_Model_Observer_Frontend
 */
class Quarticon_Quartic_Model_Insert extends Mage_Core_Model_Abstract
{

    protected $_options;

    /**
     * Return options for select field in configuration
     *
     * @param bool $isMultiselect
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        if (!$this->_options) {
            $places = $this->getData('places');
            foreach ($places as $key => $data) {
                $this->_options[] = array(
                    'value' => $key,
                    'label' => $data['label'],
                );
            }
        }

        $options = $this->_options;
        if (!$isMultiselect) {
            array_unshift($options, array('value' => '', 'label' => Mage::helper('quartic')->__('--No Frame--')));
        }

        return $options;
    }

    /**
     * Return array with configuration for custom block
     *
     * @param string $config Config path with placement id
     * @param string $model_insert Name of insert model - extending this class
     * @return array block_name => data
     */
    public function loadPlace($config, $model_insert)
    {
        $place_id = Mage::getStoreConfig($config, Mage::app()->getStore());
        if (!empty($place_id)) {
            $place = Mage::getModel($model_insert)->getData('places/' . $place_id);
            if (!empty($place['block_name'])) {
                return array($place['block_name'] => $place);
            }
        }
        return array();
    }

    /**
     * Return array with configuration for custom block
     *
     * @param string $config Config path with placement id
     * @param string $model_insert Name of insert model - extending this class
     * @return array block_name => data
     */
    public function loadPlaces($config, $model_insert, $places = array())
    {
        $allPlaces = Mage::getModel($model_insert)->getData('places');
        $parent = Mage::getModel($model_insert)->getData('frame_parent');
        foreach($allPlaces as $frameId => $place) {
            $place['frame_id'] = $frameId;
            $place['frame_parent'] = $parent;
            $places[$place['block_name']][] = $place;
        }
        return $places;
    }
}
