<?php   
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Netsol
 * @package     Netsol_Searchhistory
 * @copyright   Copyright (c) 2016 Netsolutions India (http://www.netsolutions.in)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netsol_Searchhistory_Block_Searchhistory extends Mage_Catalog_Block_Product_Abstract
{
	/**
	 * General setting instance
	 *
	 * @var  Netsol_Searchhistory 
	 */
    protected $getsetting = null;
    
    /**
	 * orderHistory setting instance
	 *
	 * @var  Netsol_Searchhistory_Helper_Data 
	 */
    protected $predictiveAnalysisBlock = null;
    
    /**
	 * numberOfProduct todisplay setting instance
	 *
	 * @var  Netsol_Searchhistory_Helper_Data 
	 */
    protected $noOfproducts = null;
    
     /**
	 * initiate instance
	 *
	 * @var  Netsol_Searchhistory_Helper_Data 
	 */
    public function __construct() {
		  
        $this->getsetting = Mage::helper('searchhistory/data');
        $this->noOfproductsSearch = $this->getsetting->getSearchMaxProductCount();
    }    
	 /**
     * @description Retrieve collection based on setting
     *
     * @param		
     * @param		
     * @return	productCollection
     */
    public function paSearchBlock()
    {
		if ($this->getsetting->isEnabled ()) {
				$this->paSearchBlock = $this->_paSearchBlock();
				return $this->paSearchBlock;
		}
	}
	
	/**
	 * @description: Get all banners
	 * according to seasons
	 * 
	 * @param order item
	 * @return  $productCollection
	 * */
	 protected function _paSearchBlock()
	 {
		 if(Mage::getSingleton('customer/session')->isLoggedIn())
		 {
			$searchProductCollection = array();	
			$searchProductIds = array();
			$orderHisSeasonProductIds = array();
			
			$searchProductIds = Mage::helper('searchhistory/pasearch')->getSearchCollections();
			
			return $searchProductIds;
		}
	 }
}
