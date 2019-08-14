<?php

/**
 * Frame placements - product view
 */
class Quarticon_Quartic_Model_Insert_Home extends Quarticon_Quartic_Model_Insert
{

    /**
     * Places for possible custom block insertion
     *
     * Place selected in configuration will be modified on page's render
     * Our custom block's html will be put before of after the original/parent block's output
     */
    public function __construct()
    {
        $this->setData('frame_parent', 'HomePage');
        $this->setData('places', array(
            'top' => array(//'top' - key used in config
                'label' => Mage::helper('quartic')->__('Top'), //Label used in configuration form
                'block_name' => 'content', //Original block's name in layout
                'block_layout' => 'cms_index_index', //[optional] If set, our block will be inserted only on pages with this layout hadle
                'block_position' => 'before', //'before' - prepend custom block's html; 'after' - append it
                'frame_block' => 'quartic/frame_home', //Class of our custom block
                'frame_template' => 'quartic/frame/home.phtml', //Template of our custom block
            ),
            'bottom' => array(
                'label' => Mage::helper('quartic')->__('Bottom'),
                'block_name' => 'content',
                'block_layout' => 'cms_index_index',
                'block_position' => 'after',
                'frame_block' => 'quartic/frame_home',
                'frame_template' => 'quartic/frame/home.phtml',
            ),
        ));
    }
}
