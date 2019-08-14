<?php

/**
 * Frame placements - product view
 */
class Quarticon_Quartic_Model_Insert_Category extends Quarticon_Quartic_Model_Insert
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
        $this->setData('frame_parent', 'CategoryPage');
        $this->setData('places', array(
            'top' => array(
                'label' => Mage::helper('quartic')->__('Top'),
                'block_name' => 'category.products',
                'block_position' => 'before',
                'frame_block' => 'quartic/frame_category',
                'frame_template' => 'quartic/frame/category.phtml',
            ),
            'bottom' => array(
                'label' => Mage::helper('quartic')->__('Bottom'),
                'block_name' => 'category.products',
                'block_position' => 'after',
                'frame_block' => 'quartic/frame_category',
                'frame_template' => 'quartic/frame/category.phtml',
            ),
            'left' => array(
                'label' => Mage::helper('quartic')->__('Left'),
                'block_name' => 'left',
                'block_layout' => 'catalog_category_view',
                'block_position' => 'after',
                'frame_block' => 'quartic/frame_category',
                'frame_template' => 'quartic/frame/category.phtml',
            ),
            'right' => array(
                'label' => Mage::helper('quartic')->__('Right'),
                'block_name' => 'right',
                'block_layout' => 'catalog_category_view',
                'block_position' => 'after',
                'frame_block' => 'quartic/frame_category',
                'frame_template' => 'quartic/frame/category.phtml',
            ),
        ));
    }
}
