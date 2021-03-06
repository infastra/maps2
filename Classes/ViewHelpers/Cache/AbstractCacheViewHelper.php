<?php
namespace JWeiland\Maps2\ViewHelpers\Cache;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <sfroemken@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use \TYPO3\CMS\Core\Cache\Cache;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AbstractCacheViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend
	 */
	protected $cache;


	/**
	 * initializes the caching framework for this view helper
	 */
	public function initialize() {
		Cache::initializeCachingFramework();
		try {
			$this->cache = $GLOBALS['typo3CacheManager']->getCache('maps2_cachedHtml');
		} catch (\TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException $e) {
			$this->cache = $GLOBALS['typo3CacheFactory']->create(
				'maps2_cachedHtml',
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['maps2_cachedHtml']['frontend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['maps2_cachedHtml']['backend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['maps2_cachedHtml']['options']
			);
		}
	}

}