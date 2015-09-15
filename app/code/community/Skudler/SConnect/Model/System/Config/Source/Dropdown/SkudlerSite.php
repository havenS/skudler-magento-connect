<?php

include_once (Mage::getBaseDir('code'). DS . 'community' . DS . 'Skudler' . DS . 'SConnect' . DS . 'lib' . DS. 'skudler-api' . DS. 'src' . DS .'SkudlerAPI.php');

class Skudler_SConnect_Model_System_Config_Source_Dropdown_SkudlerSite {

    protected $skudler;
    protected $siteId;

    public function __construct()
    {
        $apiKey         = Mage::getStoreConfig('skudler_section/skudler_api/api_key');
        $token          = Mage::getStoreConfig('skudler_section/skudler_api/api_token');

        $this->skudler = new Skudler\SkudlerAPI($apiKey, $token);
    }

    protected function getSites()
    {
        $call = $this->skudler->getSites();

        return $call;
    }

    public function toOptionArray()
    {
        $sitesToOptions = array();
        $sites = $this->getSites();

        foreach($sites as $site){
            $sitesToOptions[] = array(
                'value' => $site->_id,
                'label' => $site->name,
            );
        }

        return $sitesToOptions;
    }

}