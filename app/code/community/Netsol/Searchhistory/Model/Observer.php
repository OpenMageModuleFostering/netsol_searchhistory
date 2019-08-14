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
 
class Netsol_Searchhistory_Model_Observer
{
	/**
	 * General setting instance
	 *
	 * @var  Netsol_Searchhistory_Helper_Data 
	 */
    protected $getsetting = null;
    
    /**
	 * initiate instance
	 */
    public function __construct() {   
        $this->getsetting = Mage::helper('searchhistory/data');
    }    

	/**
	 * @description Event after layout block generated
	 * Display block according to page selected at admin config
	 * Display block according to position selected at admin config 
	 * if left postion is enable, we'll unset right and content
	 * @param		$observer
	 * @return		no
	 */
	public function unsetlayoutblocks($observer)
	{	
		/**If admin is not logged in**/
		if(!Mage::app()->getStore()->isAdmin()) {
			$observerData         = $observer->getAction()->getLayout();
			$template	           = $observerData->getBlock('root')->getTemplate();
			$currentPageHandle     = Mage::app()->getFrontController()->getAction()->getFullActionName();
			/**if module is enabled**/
			if($this->getsetting->isEnabled()) {
				if($currentPageHandle == 'cms_index_index') { //if homepage
					
				}
				

			}else{ // if module is disabled then unset whole blocks

				$blockContent = $observerData->getBlock('content');  
				if($blockContent) {
					$blockContent->unsetChild('pasearch');	
				}
				if($currentPageHandle == 'cms_index_index')
				{ 
					$observerData->getBlock('head')->removeItem('skin_js', 'js/netsol/searchhistory/jquery-1.10.2.min.js');

				}
			}
			/**if module is enabled end**/
		}
		/**If admin is not logged in end**/
		
	}
	
    
    public function compareStrings($searchTextOne, $searchTextTwo) {
		//one is empty, so no result
		if (strlen($searchTextOne)==0 || strlen($searchTextTwo)==0) {
			return 0;
		}
		$s1clean = trim(strtolower($searchTextOne));
		$s2clean = trim(strtolower($searchTextTwo));
		
		//create arrays
		$searchArrayOne = explode(" ",$s1clean);
		$searchArrayTwo = explode(" ",$s2clean);

		$arrayOneLength = count($searchArrayOne);
		$arrayTwoLength = count($searchArrayTwo);

		if($arrayOneLength!=$arrayTwoLength) {
			return 0;	
		} else {
			//flip array 2, to make the words the keys
			$searchArrayTwo = array_flip($searchArrayTwo);

			$maxwords = max($arrayOneLength, $arrayTwoLength);
			$matches = 0;

			//find matching words
			foreach($searchArrayOne as $word) {
				if (array_key_exists($word, $searchArrayTwo))
					$matches++;
			}
			$matches;
			return ($matches / $maxwords);    
			
		}
	}
    
	/**
	 * @description Event after Keyword search
	 * @param		$observer
	 * @return		insert keyword to custom table
	 */
	public function catalogSearchUpdate($observer) {
		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$searchTerm = trim(Mage::app()->getRequest()->getParam('q')); 
			$searchTermLength = strlen($searchTerm);
			$searchCollection = Mage::getResourceModel('searchhistory/pasearch_collection')
										->addFieldToSelect('*')
										->addFieldToFilter('customer_id' , Mage::getSingleton('customer/session')->getCustomer()->getId());
			$searchId = "";	
			$recordExitsId = "";
			$sameResultsTerm ="";
			$matchSearchTerm = array();
			foreach($searchCollection->getData() as $records) {
				$matchSearchTerm[$records['searchid']] = $this->compareStrings($records['search_term'],$searchTerm);
			}
			$recordExitsId = array_search(1,$matchSearchTerm);	 

			/** Check Keyword already exits **/
			if(count($searchCollection->getData())) {
				foreach($searchCollection->getData() as $value){
						$searchText = $value['search_term']; 
						$query = Mage::getModel('catalogsearch/query')->setQueryText($searchText)->prepare();
						$fulltextResource = Mage::getResourceModel('catalogsearch/fulltext')->prepareResult(
							Mage::getModel('catalogsearch/fulltext'),
							$searchText,
							$query
						);
						
						/** search Query to get catalag products **/
						$collection = Mage::getResourceModel('catalog/product_collection');
						$collection->getSelect()->joinInner(
							array('search_result' => $collection->getTable('catalogsearch/result')),
						$collection->getConnection()->quoteInto(
							'search_result.product_id=e.entity_id AND search_result.query_id=?',
							$query->getId()
						),
						array('relevance' => 'relevance')
						);
						$searchProductIdsDatabse[$searchText] = array_column($collection->getData(), 'entity_id');

				}
				foreach($searchCollection->getData() as $value){
					$query = Mage::getModel('catalogsearch/query')->setQueryText($searchTerm)->prepare();
					$fulltextResource = Mage::getResourceModel('catalogsearch/fulltext')->prepareResult(
						Mage::getModel('catalogsearch/fulltext'),
						$searchTerm,
						$query
					);
					/** search Query to get catalag products **/
					$collection = Mage::getResourceModel('catalog/product_collection');
					$collection->getSelect()->joinInner(
						array('search_result' => $collection->getTable('catalogsearch/result')),
					$collection->getConnection()->quoteInto(
						'search_result.product_id=e.entity_id AND search_result.query_id=?',
						$query->getId()
					),
					array('relevance' => 'relevance')
					);
					$searchProductIds[$searchTerm] = array_column($collection->getData(), 'entity_id');	
				}
				foreach($searchProductIdsDatabse as $key => $productId){
					if ($searchProductIdsDatabse[$key] == $searchProductIds[$searchTerm])
					{
						if(count($productId) > 0 ){
							$sameResultsTerm = $key;
						// output $arr1[$i]['imagepath']
						}
					}
				}

				$recordExitsCollections = Mage::getResourceModel('searchhistory/pasearch_collection')
										->addFieldToSelect('*')
										->addFieldToFilter('customer_id' , Mage::getSingleton('customer/session')->getCustomer()->getId())
										->addFieldToFilter('search_term' , $searchTerm);
				if($recordExitsCollections->getData()) {
					foreach($recordExitsCollections as $exitsRecord){
							 $recordExitsId = $exitsRecord['searchid'];
					}
				}
				if(!empty($sameResultsTerm)){
					$sameResultsTermCollections = Mage::getResourceModel('searchhistory/pasearch_collection')
						->addFieldToSelect('*')
						->addFieldToFilter('customer_id' , Mage::getSingleton('customer/session')->getCustomer()->getId())
						->addFieldToFilter('search_term' , $sameResultsTerm);
					if($sameResultsTermCollections->getData()) {
						foreach($sameResultsTermCollections as $sameResultRecord){
								 $sameResultRecordId = $sameResultRecord['searchid'];
						}
					}
				}
			}
			/**End Check Keyword already exits **/
			
			if(!empty($recordExitsId)) {
				/** Update keyword incase user already did search **/
				try {
					$searchModel = Mage::getModel('searchhistory/pasearch')->load($recordExitsId);
					$searchModel->setUpdatedAt(NOW());
					$searchModel->save();
				} catch (Exception $e){
					 echo $e->getMessage(); die;
				}
				/** End Update keyword incase user already did search **/
			}elseif(!empty($sameResultRecordId)) {
				/** Update keyword incase user already did search **/
				try {
					$searchModel = Mage::getModel('searchhistory/pasearch')->load($sameResultRecordId);
					$searchModel->setUpdatedAt(NOW());
					$searchModel->save();
				} catch (Exception $e){
					 echo $e->getMessage(); die;
				}
				/** End Update keyword incase user already did search **/
			} else {
						/** Insert search box keyword to custom table **/
						try {
								if(count($searchCollection->getData()) >= $this->getsetting->getSearchTermCount()) {
									$extraRowsCount = count($searchCollection->getData()) - $this->getsetting->getSearchTermCount();
									$extraRowsCount = $extraRowsCount + 1;
									$minRecordCollection = Mage::getResourceModel('searchhistory/pasearch_collection')
														->addFieldToSelect('*')
														->addFieldToFilter('customer_id' , Mage::getSingleton('customer/session')->getCustomer()->getId())
														->setOrder('updated_at', 'ASC');
									$minRecordCollection->getSelect()->limit($extraRowsCount);
									if($searchTermLength > 3){
										foreach($minRecordCollection as $oldRecords){
											 $oldRecords->delete();
										}
									}
								/**End update min record if search of uuser is grater than five **/
								}
								//Insert
								if($searchTermLength > 3){
									$searchModel = Mage::getModel('searchhistory/pasearch');
									$searchModel->setCustomerId(Mage::getSingleton('customer/session')->getCustomer()->getId());
									$searchModel->setSearchTerm($searchTerm);
									$searchModel->setCreatedAt(NOW());
									$searchModel->setUpdatedAt(NOW());
									$searchModel->save();	
								}
						} catch (Exception $e){
							 echo $e->getMessage(); die;
						}
						
			}
		}
	}
	/**
	 * @description Event fire on adminhtml layout  
	 * @param		$observer
	 * @return		custom layout for our admin module
	 */
	public function addAdminCustomLayoutHandle($observer) {
		$controllerAction = $observer->getEvent()->getAction();
		$layout = $observer->getEvent()->getLayout();
		if ($controllerAction && $layout && $controllerAction instanceof Mage_Adminhtml_System_ConfigController) { // Can be checked in other ways of course
			
			if ($controllerAction->getRequest()->getParam('section') == 'pa_searchsetting') { 
				$layout->getUpdate()->addHandle('searchhistory_adminhtml_system_config_edit');
			}
		}
		return $this;
	}
}
