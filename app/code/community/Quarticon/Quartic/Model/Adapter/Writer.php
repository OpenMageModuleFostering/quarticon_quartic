<?php

class Quarticon_Quartic_Model_Adapter_Writer extends XMLWriter
{

    /**
     *
     * Prepares raw element to be written
     *
     * @param    string  $content
     * @return   true or false
     *
     */
    function WriteRaw($content)
    {
        $content = str_replace('&', '&amp;', $content);
        parent::WriteRaw($content);
    }
}
