<?php
require_once(__DIR__ . '/../support/zcListingBoxTestCase.php');

class testListingBuild extends zcListingBoxTestCase
{

    public function testSimpleListingBoxInstantiation()
    {
        $di = $this->simpleInstantiation();
        $listingBox = $this->mockListingBox(array('formatter'=>array('class' => 'ListStandard')), array());
        $box = new ZenCart\ListingBox\Build ($di, $listingBox);
        $this->assertInstanceOf('ZenCart\\ListingBox\\Build', $box);
    }
    public function testSimpleListingBoxInstantiationException()
    {
        $this->setExpectedException('Exception');
        $di = $this->simpleInstantiation();
        $listingBox = $this->mockListingBox(array(), array());
        $box = new ZenCart\ListingBox\Build ($di, $listingBox);
        $this->assertInstanceOf('ZenCart\\ListingBox\\Build', $box);
    }

    public function testSimpleListingBoxInit()
    {
        $di = $this->simpleInstantiation();
        $listingBox = $this->mockListingBox(array('formatter'=>array('class' => 'ListStandard')), array());
        $box = new ZenCart\ListingBox\Build ($di, $listingBox);
        $box->init();
        $this->assertInstanceOf('ZenCart\\ListingBox\\Build', $box);
    }
    public function testListingBoxInitWithFilters()
    {
        $di = $this->simpleInstantiation();
        $productQuery = array('filters'=>array(array('name'=>'DisplayOrderSorter', 'parameters'=>array('defaultSortOrder' => 1))));
        $listingBox = $this->mockListingBox(array('formatter'=>array('class' => 'ListStandard')), $productQuery);
        $box = new ZenCart\ListingBox\Build ($di, $listingBox);
        $box->init();
        $box->getTemplateVariables();
        $box->getFormattedItemsCount();
        $box->getItemCount();
        $this->assertInstanceOf('ZenCart\\ListingBox\\Build', $box);
    }
}
