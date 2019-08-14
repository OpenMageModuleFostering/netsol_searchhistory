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
 * @copyright   Copyright (c) 2015 Netsolutions India (http://www.netsolutions.in)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netsol_Searchhistory_Helper_Data extends Mage_Core_Helper_Abstract
{
		/**
	 * Path to store config front-end output is enabled or disabled is stored
	 *
	 * @var  string 
	 */
    const XML_PATH_STATUS = 'pa_searchsetting/pa_searchhistory/enable';
  
     /**
     * Path to store time period to display product on frontend in days
	 *
	 * @var  string 
	 */
    const XML_PATH_ENABLE_JQUERY = 'pa_searchsetting/pa_searchhistory/enabled_jquery';
    
     /**
     * To Enable search Block
	 * @var  string 
	 */
    const XML_PATH_SEARCH_BLOCK_HEADING = 'pa_searchsetting/pa_searchhistory/search_block_heading';
    
     /**
     * Search Max product count
	 * @var  string 
	 */
    const XML_PATH_SEARCH_MAX_PRODUCT_COUNT = 'pa_searchsetting/pa_searchhistory/search_max_product_count';
    
     /**
     * Search search Term count
	 * @var  string 
	 */
    const XML_PATH_SEARCH_TERM_COUNT = 'pa_searchsetting/pa_searchhistory/search_term_count';
   	 /**
	 * enable/disable 
	 *
	 * @var  boolean 
	 */
    protected $isEnabled = null;

	 /**
     * Enable Jquery
     *
     * @var  number 
     */
    protected $enableJquery = null;
    
	 /**
     * Heading
     *
     * @var  number 
     */
    protected $heading = null;

	 /**
     * Search Block heading
     *
     * @var  number 
     */
    protected $searchBlockHeading = null;
    
	 /**
     * Search max product count
     *
     * @var  number 
     */
    protected $searchMaxProductCount = null;
    
	 /**
     * Search term count
     *
     * @var  number 
     */
    protected $searchTermCount = null;
    
    public function __construct()
    {
        if(($this->isEnabled = $this->_isEnabled())) {
			$this->enableJquery = $this->_getEnableJquery();
			$this->searchBlockHeading = $this->_getSearchBlockHeading();
			$this->searchMaxProductCount = $this->_getSearchMaxProductCount();
			$this->searchTermCount = $this->_getSearchTermCount();
         }
    }

    /**
     * @description Check whether is enable or not
     *
     * @param		no
     * @return		boolean
     */
    public function isEnabled()
    {
        return (bool) $this->isEnabled;
    }
 
    /**
     * @description Enable Jquery
     *
     * @param		no
     * @return		string
     */
    public function getEnableJquery()
    {
        return $this->enableJquery;
    }
    
	/**
     * @description Search heading Block
     *
     * @param		no
     * @return		string
     */
    public function getSearchBlockHeading()
    {
        return $this->searchBlockHeading;
    }
    
	/**
     * @description Search max product count
     *
     * @param		no
     * @return		string
     */
    public function getSearchMaxProductCount()
    {
        return $this->searchMaxProductCount;
    }
    
	/**
     * @description Search term count
     *
     * @param		no
     * @return		string
     */
    public function getSearchTermCount()
    {
        return $this->searchTermCount;
    }
    
    /**
     * @description retrieve options
     *
     * @param		no
     * @return		string
     */
     
    protected function _isEnabled()
    {
        return $this->_getStoreConfig(self::XML_PATH_STATUS);
    }

	protected function _getEnableJquery()
	{
		return $this->_getStoreConfig(self::XML_PATH_ENABLE_JQUERY);
	}

	protected function _getSearchBlockHeading()
    {
        return $this->_getStoreConfig(self::XML_PATH_SEARCH_BLOCK_HEADING);
    }
	protected function _getSearchMaxProductCount()
    {
        return $this->_getStoreConfig(self::XML_PATH_SEARCH_MAX_PRODUCT_COUNT);
    }
	protected function _getSearchTermCount()
    {
        return $this->_getStoreConfig(self::XML_PATH_SEARCH_TERM_COUNT);
    }
    protected function _getStoreConfig($xmlPath)
    {
        return Mage::getStoreConfig($xmlPath, Mage::app()->getStore()->getId());
    }
}
	 
