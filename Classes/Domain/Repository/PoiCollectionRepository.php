<?php
namespace JWeiland\Maps2\Domain\Repository;

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

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PoiCollectionRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * The TYPO3 page repository. Used for language and workspace overlay
	 *
	 * @var \TYPO3\CMS\Frontend\Page\PageRepository
	 */
	protected $pageRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Service\EnvironmentService
	 * @inject
	 */
	protected $environmentService;

	/**
	 * @return \TYPO3\CMS\Frontend\Page\PageRepository
	 */
	protected function getPageRepository() {
		if (!$this->pageRepository instanceof \TYPO3\CMS\Frontend\Page\PageRepository) {
			if ($this->environmentService->isEnvironmentInFrontendMode() && is_object($GLOBALS['TSFE'])) {
				$this->pageRepository = $GLOBALS['TSFE']->sys_page;
			} else {
				$this->pageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
			}
		}
		return $this->pageRepository;
	}

	/**
	 * search for poi collections within a given radius
	 *
	 * @param float $latitude the users position
	 * @param float $longitude the users position
	 * @param integer $radius The range to search for poi collections (km)
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function searchWithinRadius($latitude, $longitude, $radius) {
		$radiusOfEarth = 6380;
		$query = $this->createQuery();
		$query->statement('
			SELECT *, ACOS(SIN(RADIANS(?)) * SIN(RADIANS(latitude)) + COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(?) - RADIANS(longitude))) * ? AS distance
			FROM tx_maps2_domain_model_poicollection
			WHERE collection_type = "Point"
			AND tx_maps2_domain_model_poicollection.pid IN (' . implode(',', $query->getQuerySettings()->getStoragePageIds()) . ')' .
			$this->getPageRepository()->enableFields('tx_maps2_domain_model_poicollection') . '
			HAVING distance < ?
			ORDER BY distance;
		', array($latitude, $latitude, $longitude, $radiusOfEarth, $radius));
		return $query->execute();
	}

	/**
	 * find all pois selected by categories
	 *
	 * @param string $categories comma separated list of category uids
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findPoisByCategories($categories) {
		$query = $this->createQuery();
		return $query->statement('
			SELECT DISTINCT tx_maps2_domain_model_poicollection.*
			FROM tx_maps2_domain_model_poicollection
			LEFT JOIN sys_category_record_mm ON tx_maps2_domain_model_poicollection.uid=sys_category_record_mm.uid_foreign
			WHERE sys_category_record_mm.uid_local IN (' . $categories . ')
			AND sys_category_record_mm.tablenames = ?
			AND tx_maps2_domain_model_poicollection.pid IN (' . implode(',', $query->getQuerySettings()->getStoragePageIds()) . ')' .
			$this->getPageRepository()->enableFields('tx_maps2_domain_model_poicollection')
		, array('tx_maps2_domain_model_poicollection'))->execute();
	}

}