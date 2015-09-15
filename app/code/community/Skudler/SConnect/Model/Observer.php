<?php

include_once (Mage::getBaseDir('code'). DS . 'community' . DS . 'Skudler' . DS . 'SConnect' . DS . 'lib' . DS. 'skudler-api' . DS. 'src' . DS .'SkudlerAPI.php');

class Skudler_SConnect_Model_Observer {

    protected $api;

    public function __construct(){
        $apiKey = Mage::getStoreConfig('skudler_section/skudler_api/api_key');
        $token  = Mage::getStoreConfig('skudler_section/skudler_api/api_token');

        $this->api = new Skudler\SkudlerAPI($apiKey, $token);
    }

    public function customerRegister($o)
    {
        if($this->isEnabled() && $this->isEnabled('skudler_register_event/event_register_enabled')) {
            $eventId = $this->getEventId('skudler_register_event/event_register');

            $customer = $o->getEvent()->getCustomer();

            $info = array(
                'email'     => $customer->getEmail(),
                'firstname' => $customer->getFirstname(),
                'lastname'  => $customer->getLastname()
            );

            @$this->api->addSubscription($eventId, $info);
        }
    }

    public function customerLogin($o)
    {
        if($this->isEnabled() && $this->isEnabled('skudler_login_event/event_login_enabled')) {

            $eventId = $this->getEventId('skudler_login_event/event_login');

            $customer = $o->getEvent()->getCustomer();

            $info = array(
                'email'     => $customer->getEmail(),
                'firstname' => $customer->getFirstname(),
                'lastname'  => $customer->getLastname()
            );

            @$this->api->addSubscription($eventId, $info);
        }
    }

    public function productToCart()
    {
        if($this->isEnabled() && $this->isEnabled('skudler_product_cart_event/event_product_cart_enabled')) {

            $eventId    = $this->getEventId('skudler_product_cart_event/event_product_cart');
            $customer   = Mage::getSingleton('customer/session')->getCustomer();
            $items      = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();

            $info = array(
                'email'     => $customer->getEmail(),
                'firstname' => $customer->getFirstname(),
                'lastname'  => $customer->getLastname(),
                'products'  => $this->getProducts($items)
            );

            @$this->api->addSubscription($eventId, $info);
        }
    }

    public function newOrder($o)
    {
        if($this->isEnabled() && $this->isEnabled('skudler_new_order_event/event_new_order_enabled')) {
            $addCartEventId = $this->getEventId('skudler_product_cart_event/event_product_cart');
            $eventId        = $this->getEventId('skudler_new_order_event/event_new_order');
            $customer       = Mage::getSingleton('customer/session')->getCustomer();
            $items          = $o->getEvent()->getOrder()->getAllVisibleItems();

            $info = array(
                'email'     => $customer->getEmail(),
                'firstname' => $customer->getFirstname(),
                'lastname'  => $customer->getLastname(),
                'products'  => $this->getProducts($items)
            );


            @$this->api->deleteSubscription($addCartEventId, $info);
            @$this->api->addSubscription($eventId, $info);
        }
    }





    protected function isEnabled($field = 'skudler_api/api_enabled')
    {
        return Mage::getStoreConfig('skudler_section/'.$field);
    }

    protected function getEventId($evt)
    {
        return Mage::getStoreConfig('skudler_section/'.$evt);
    }

    protected function getProducts($items)
    {
        $products = array();

        foreach($items as $item) {

            $product = $this->getProductDescThumb($item->getProductId());

            $name = $this->getProductFullName($item);

            $products[] = array(
                'name'          => $name,
                'description'   => $product['shortDescription'],
                'qty'           => $item->getQty(),
                'price'         => $item->getPrice(),
                'thumbnail'     => $product['thumbnail']
            );
        }

        return $products;
    }

    protected function getProductDescThumb($productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);

        return array(
            'description'   => $product->getShortDescription(),
            'thumbnail'     => $product->getImageUrl(),
        );
    }

    protected function getProductFullName($item)
    {
        $name    = $item->getName();
        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

        if($options['attributes_info']) {
            foreach ($options['attributes_info'] as $opt){
                $name .= ', ' . $opt['label'] . ': ' . $opt['value'];
            }
        }
        return $name;
    }

}