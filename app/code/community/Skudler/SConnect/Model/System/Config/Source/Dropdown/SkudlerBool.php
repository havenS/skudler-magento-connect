<?php

class Skudler_SConnect_Model_System_Config_Source_Dropdown_SkudlerBool {

    public function toOptionArray()
    {
        return array(
            1 => 'Enabled',
            0 => 'Disabled',
        );
    }

}