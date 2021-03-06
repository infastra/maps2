<?php
namespace JWeiland\Maps2\Ajax;

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
class InsertRoute extends \JWeiland\Maps2\Ajax\AbstractAjaxRequest {

	/**
	 * @var \JWeiland\Maps2\Domain\Repository\PoiCollectionRepository
	 * @inject
	 */
	protected $poiCollectionRepository;





	/**
	 * process ajax request
	 *
	 * @param array $arguments Arguments to process
	 * @param string $hash A generated hash value to verify that there are no modifications in the uri
	 * @return string
	 */
	public function processAjaxRequest(array $arguments, $hash) {
		// cast arguments
		$uid = (int)$arguments['uid'];
		$route = (array)$arguments['route'];

		$poiCollection = $this->poiCollectionRepository->findByUid($uid);

		if ($poiCollection instanceof \JWeiland\Maps2\Domain\Model\PoiCollection) {
			// validate uri arguments
			if (!$this->validateArguments($poiCollection, $hash)) {
				return 'arguments are not valid';
			}

			$poiCollection = $this->getUpdatedPositionRecords($poiCollection, $route);

			$this->poiCollectionRepository->update($poiCollection);
			$this->persistenceManager->persistAll();
			return '1';
		} else return '0';
	}

	/**
	 * get updated position records
	 * this method loops through all route positions and insert or updates the expected record in db
	 *
	 * @param \JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection The parent object for pois
	 * @param array $routes Array containing all positions of the route
	 * @return \JWeiland\Maps2\Domain\Model\PoiCollection A collection of position records
	 */
	public function getUpdatedPositionRecords(\JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection, array $routes) {
		if (count($routes)) {
			foreach($routes as $posIndex => $route) {
				// get latitude and longitude from current route
				$latLng = explode(',', $route);
				$lat = (float)$latLng[0];
				$lng = (float)$latLng[1];

				// check if we have such a record already
				$poi = $this->getPoiFromPoiArray($poiCollection, $posIndex);

				if ($poi instanceof \JWeiland\Maps2\Domain\Model\Poi) {
					// update poi if lat or lng differs
					if ($poi->getLatitude() != $lat || $poi->getLongitude() != $lng) {
						$poiCollection->getPois()->detach($poi);
						$poi->setLatitude($lat);
						$poi->setLongitude($lng);
						$poiCollection->getPois()->attach($poi);
					}
				} else {
					// create a new poi
					/** @var $poi \JWeiland\Maps2\Domain\Model\Poi */
					$poi = $this->objectManager->get('\JWeiland\Maps2\Domain\Model\Poi');

					// TODO set cruser_id
					$poi->setPid($poiCollection->getPid());
					$poi->setLatitude($lat);
					$poi->setLongitude($lng);
					$poi->setPosIndex($posIndex);

					$poiCollection->getPois()->attach($poi);
				}
			}
			// check if points were removed
			$amountOfRoutes = count($routes);
			if ($amountOfRoutes < count($poiCollection->getPois())) {
				$poi = $this->getPoiFromPoiArray($poiCollection, $amountOfRoutes);
				$poiCollection->getPois()->detach($poi);
			}
		}

		return $poiCollection;
	}

	/**
	 * get poi from poi array
	 *
	 * @param \JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection
	 * @param $posIndex
	 * @return null|\JWeiland\Maps2\Domain\Model\Poi
	 */
	public function getPoiFromPoiArray(\JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection, $posIndex) {
		/** @var $poi \JWeiland\Maps2\Domain\Model\Poi */
		foreach($poiCollection->getPois() as $poi) {
			if ($poi->getPosIndex() == $posIndex) {
				return $poi;
			}
		}
		return NULL;
	}

}