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
        /**
         * Load config from magento
         */
        $places_config_raw = Mage::getStoreConfig($config, Mage::app()->getStore());
        /**
         * Convert it
         */
        $places_config = array();
        if(is_array($places_config_raw)) foreach ($places_config_raw as $k => $v) {
            if (strpos($k, '_enabled') !== false) {
                $k = str_replace('_enabled', '', $k);
                $nk = 'enabled';
            } else {
                $nk = 'frame';
            }
            if (!isset($places_config[$k])) {
                $places_config[$k] = array(//$k - where the placement will be used
                    'frame' => $k, //which placement will be used
                    'enabled' => 0, //should we display it
                );
            }
            $places_config[$k][$nk] = $v;
        }
        foreach ($places_config as $key => $place_config) {
            if (!$place_config['enabled']) {
                //ignore disabled places
                continue;
            }
            if (empty($place_config['frame'])) {
                //ignore places where template was not selected
                continue;
            }
            //where the placement will be used
            $place = Mage::getModel($model_insert)->getData('places/' . $key);
            if (!empty($place['block_name'])) {
                //which frame will be used (from select field)
                $place['frame_id'] = $place_config['frame'];
                //layout data
                $place['frame_parent'] = Mage::getModel($model_insert)->getData('frame_parent');
                if (!isset($places[$place['block_name']])) {
                    $places[$place['block_name']] = array();
                }
                $places[$place['block_name']][] = $place;
            }
        }
        return $places;
    }
}
