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
$installer = $this;
$installer->startSetup();
$installer->run("
	DROP TABLE IF EXISTS {$this->getTable('netsol_pa_search')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('netsol_pa_search')} (
	`searchid`int(11) NOT NULL auto_increment,
	`customer_id` int(11) NOT NULL,
	`search_term` varchar(255) NOT NULL,
	`created_at` timestamp NULL,
	`updated_at` timestamp NULL,
	 PRIMARY KEY  (`searchid`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"); 
$installer->endSetup();
	 
