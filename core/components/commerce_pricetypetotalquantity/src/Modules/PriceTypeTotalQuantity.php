<?php
namespace modmore\Commerce_PriceTypeTotalQuantity\Modules;

use modmore\Commerce\Events\Admin\PriceTypes;
use modmore\Commerce\Modules\BaseModule;
use modmore\Commerce_PriceTypeTotalQuantity\Pricing\PriceType\TotalQuantity;
use modmore\Commerce\Dispatcher\EventDispatcher;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

class PriceTypeTotalQuantity extends BaseModule {

    public function getName()
    {
        $this->adapter->loadLexicon('commerce_pricetypetotalquantity:default');
        return $this->adapter->lexicon('commerce_pricetypetotalquantity');
    }

    public function getAuthor()
    {
        return 'modmore';
    }

    public function getDescription()
    {
        return $this->adapter->lexicon('commerce_pricetypetotalquantity.description');
    }

    public function initialize(EventDispatcher $dispatcher)
    {
        $this->adapter->loadLexicon('commerce_pricetypetotalquantity:default');

        $root = dirname(__DIR__, 2);
        $this->commerce->view()->addTemplatesPath($root . '/templates/');

        $dispatcher->addListener(\Commerce::EVENT_DASHBOARD_GET_PRICE_TYPES, [$this, 'registerPriceType']);
    }

    public function registerPriceType(PriceTypes $event)
    {
        $event->addPriceType(TotalQuantity::class);
    }
}
