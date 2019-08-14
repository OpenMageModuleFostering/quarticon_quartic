<?php

/**
 * Frame placements - product view
 */
class Quarticon_Quartic_Model_Insert_Product extends Quarticon_Quartic_Model_Insert
{

    /**
     * Places for possible custom block insertion
     *
     * Place selected in configuration will be modified on page's render
     * Our custom block's html will be put before of after the original/parent block's output
     *
     * For commented example
     * @see Quarticon_Quartic_Model_Insert_Home
     */
    public function __construct()
    {
        $this->setData('frame_parent', 'ProductPage');
        $this->setData('places', array(
            'top' => array(//'top' - key used in config
                'label' => Mage::helper('quartic')->__('Top'), //Label used in configuration form
                'block_name' => 'product.info', //Original block's name in layout
                'block_position' => 'before', //'before' - prepend custom block's html; 'after' - append it
                'frame_block' => 'quartic/frame_product', //Class of our custom block
                'frame_template' => 'quartic/frame/product.phtml', //Template of our custom block
            ),
            'bottom' => array(
                'label' => Mage::helper('quartic')->__('Bottom'),
                'block_name' => 'product.info.upsell',
                'block_position' => 'before',
                'frame_block' => 'quartic/frame_product',
                'frame_template' => 'quartic/frame/product.phtml',
            ),
            'right' => array(
                'label' => Mage::helper('quartic')->__('Right'),
                'block_name' => 'product.info.addtocart',
                'block_position' => 'after',
                'frame_block' => 'quartic/frame_product',
                'frame_template' => 'quartic/frame/product.phtml',
            ),
            'middle' => array(
                'label' => Mage::helper('quartic')->__('Middle'),
                'block_name' => 'product.description',
                'block_position' => 'after',
                'frame_block' => 'quartic/frame_product',
                'frame_template' => 'quartic/frame/product.phtml',
            ),
        ));
    }
}
