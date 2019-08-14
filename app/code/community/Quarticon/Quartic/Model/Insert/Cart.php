<?php

/**
 * Frame placements - product view
 */
class Quarticon_Quartic_Model_Insert_Cart extends Quarticon_Quartic_Model_Insert
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
        $this->setData('frame_parent', 'CartPage');
        $this->setData('places', array(
            'top' => array(
                'label' => Mage::helper('quartic')->__('Top'),
                'block_name' => 'checkout.cart.form.before',
                'block_position' => 'before',
                'frame_block' => 'quartic/frame_cart',
                'frame_template' => 'quartic/frame/cart.phtml',
            ),
            'bottom' => array(
                'label' => Mage::helper('quartic')->__('Bottom'),
                'block_name' => 'checkout.cart',
                'block_position' => 'after',
                'frame_block' => 'quartic/frame_cart',
                'frame_template' => 'quartic/frame/cart.phtml',
            ),
            'right' => array(
                'label' => Mage::helper('quartic')->__('Right'),
                'block_name' => 'checkout.cart.shipping',
                'block_position' => 'before',
                'frame_block' => 'quartic/frame_cart',
                'frame_template' => 'quartic/frame/cart.phtml',
            ),
            'middle' => array(
                'label' => Mage::helper('quartic')->__('Middle'),
                'block_name' => 'checkout.cart.crosssell',
                'block_position' => 'after',
                'frame_block' => 'quartic/frame_cart',
                'frame_template' => 'quartic/frame/cart.phtml',
            ),
        ));
    }
}
