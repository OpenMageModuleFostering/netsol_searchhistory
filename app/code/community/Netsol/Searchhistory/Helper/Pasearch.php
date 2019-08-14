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
class Netsol_Searchhistory_Helper_Pasearch extends Varien_Data_Form_Element_Image
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
	 * @description: Resource iterator technique allows 
	 * get product from pasearch
	 * 
	 * @param 
	 * @return  
	 * */
	 public function getSearchCollections()
	 {
		try {
				$searchText = "";		
				$productsFromEachSearchTerm	= "";
				$searchProductIds = array();
				$productInOrderHistory = array();
				$searchIdsCollection = array();
				$searchProductIdsMerge = array();
				$ordersearchTerm = array();
				$searchTerm = array();
				$searchProductCollection = array();
				$firstCaseProductCollection = array();
				$secondCaseProductCollection = array();
				
				/** Get collection by serach keyword of specific user **/ 
				$searchCollection = Mage::getResourceModel('searchhistory/pasearch_collection')
									->addFieldToSelect('*')
									->addFieldToFilter('customer_id' , Mage::getSingleton('customer/session')->getCustomer()->getId())
									->setOrder('updated_at', 'DESC');
				
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
						$searchProductIds = array_column($collection->getData(), 'entity_id');
						if(count($searchProductIds)){
							$todayDate  = NOW();
							$orders = Mage::getResourceModel('sales/order_item_collection')
										  ->addAttributeToSelect('parent_item_id')
										  ->addAttributeToSelect('product_id')
										  ->addFieldToFilter('product_id', array('in' =>$searchProductIds));
							$orders->getSelect()->join( array('orders'=> sales_flat_order),
							'orders.entity_id=main_table.order_id',array('orders.created_at','orders.customer_id','orders.status')); 
							$customer = Mage::getSingleton('customer/session')->getCustomer();
							$orders->addFieldToFilter('customer_id',$customer->getId())
									->addFieldToFilter('orders.created_at', array('gt' =>$value['updated_at']));
										
							//$productInOrderHistory = array_column($orders->getData(), 'product_id');
							foreach($orders as $orderIds){
								$productInOrderHistory[] = $orderIds->product_id;
							}
							
							if(!empty($orders->getData())) {
								$ordersearchTerm[] = $value['search_term'];
							}else{
								$searchTerm[] = $value['search_term'];
							}
							/* Ends Check  search results exist in user order history
							 * delete that search term  */
						}
					}
					$productInOrderHistory = array_unique($productInOrderHistory);
					/**Insert search term at end which are in order history**/ 
					$searchTermCollections = array_merge($searchTerm,$ordersearchTerm);
					 /** Divide each search iteration results 
					  *  Divide the 
					  *  to get max number count of search products collection
					  *  from each stored search term of specific user.
					  *  To display the search products collection of stored search term 
					  *  of user.
					  *   **/ 
					/** if search term of user collection exist **/		
					$productsFromEachSearchTerm = ceil($this->getsetting->getSearchMaxProductCount() / count($searchTermCollections)) ;
					/**Loop each search term of user to get search results **/
					$count = 0;
					//echo "Case one";
					foreach($searchTermCollections as $searchText) {
						$firstCasesearchTermRecord = Mage::getResourceModel('searchhistory/pasearch_collection')
											->addFieldToSelect('updated_at')
											->addFieldToFilter('search_term' ,$searchText);
						$firstCasesearchTermRecord =	$firstCasesearchTermRecord->getData();
						$query = Mage::getModel('catalogsearch/query')->setQueryText($searchText)->prepare();
						$fulltextResource = Mage::getResourceModel('catalogsearch/fulltext')->prepareResult(
							Mage::getModel('catalogsearch/fulltext'),
							$searchText,
							$query
						);
						/** search Query to get catalag products **/
						$collection = Mage::getResourceModel('catalog/product_collection')
									->addAttributeToFilter('created_at', array('gt' =>$firstCasesearchTermRecord[0]['updated_at']))
									->setPageSize($productsFromEachSearchTerm);
						$collection->getSelect()->joinInner(
							array('search_result' => $collection->getTable('catalogsearch/result')),
						$collection->getConnection()->quoteInto(
							'search_result.product_id=e.entity_id AND search_result.query_id=?',
							$query->getId()
						),
						array('relevance' => 'relevance')
						);
						$collection->getSelect()->order('relevance DESC');
						$firstCaseProductIds[$count] = array_column($collection->getData(), 'entity_id');
						/** Ends search Query to get catalag products **/

						if(count($firstCaseProductIds[$count])) {
							/**Take $valueFromSearchTerm products from each iteration **/
							if($count > 0) {
								$firstCaseProductCollection = array_merge($firstCaseProductCollection,$firstCaseProductIds[$count]);
							}else{
								$firstCaseProductCollection = $firstCaseProductIds[$count];
							}
						}
						$count++;
					}
					$searchProductCollections = array_unique($firstCaseProductCollection);

					/**Loop each search term of user to get search results ends **/
					
					/** If first case has less than config no. of products **/	
					if(count($searchProductCollections) < $this->getsetting->getSearchMaxProductCount() || empty($searchProductCollections)){
						//echo "Case Two";
						$count = 0;
						/**Loop each search term of user to get search results **/
						foreach($searchTermCollections as $searchText) {
							
								$secondCasesearchTermRecord = Mage::getResourceModel('searchhistory/pasearch_collection')
									->addFieldToSelect('updated_at')
									->addFieldToFilter('search_term' ,$searchText);
								$secondCasesearchTermRecord = $secondCasesearchTermRecord->getData();
								$query = Mage::getModel('catalogsearch/query')->setQueryText($searchText)->prepare();
								$fulltextResource = Mage::getResourceModel('catalogsearch/fulltext')->prepareResult(
									Mage::getModel('catalogsearch/fulltext'),
									$searchText,
									$query
								);
								/** search Query to get catalag products **/
								
								$collection = Mage::getResourceModel('catalog/product_collection')
											->addAttributeToFilter('created_at', array('lt' =>$secondCasesearchTermRecord[0]['updated_at']))
											->setPageSize($productsFromEachSearchTerm);
								$collection->getSelect()->joinInner(
									array('search_result' => $collection->getTable('catalogsearch/result')),
								$collection->getConnection()->quoteInto(
									'search_result.product_id=e.entity_id AND search_result.query_id=?',
									$query->getId()
								),
								array('relevance' => 'relevance')
								);
								
								$collection->getSelect()->order('relevance DESC');
								$secondCaseProductIds[$count] =  array_column($collection->getData(), 'entity_id');
								
								if($count > 0) {
									$secondCaseProductCollection = array_merge($secondCaseProductCollection,$secondCaseProductIds[$count]);	
								} else {
									$secondCaseProductCollection = $secondCaseProductIds[$count];
								}
								
								$count++;
							}

							$secondCaseProductCollection = array_unique($secondCaseProductCollection);
							//$reqIdsToFillFirstArray = $this->getsetting->getSearchMaxProductCount() - count($firstCaseMergedArray);
							//$secondCaseMergedArray = array_slice($secondIdsMergedArray, 0, $reqIdsToFillFirstArray, true); 

						if(count($searchProductCollections) > 0){ 
							if(count($secondCaseProductCollection)) {
								$searchProductCollections = array_merge($searchProductCollections,$secondCaseProductCollection);
								$searchProductCollections = array_unique($searchProductCollections);
							}
						} else {		
							$searchProductCollections = $secondCaseProductCollection;
						}
					}
					$searchProductCollections = array_diff($searchProductCollections,$productInOrderHistory);
					return $searchProductCollections;
				}
				/** if search term of user collection exist Ends**/		
		}catch(Exception $e) {
			echo $e->getMessage(); die;
		}
	 }
	 
}
