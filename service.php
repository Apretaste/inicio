<?php

use Apretaste\Request;
use Apretaste\Response;
use Framework\Core;
use Framework\Database;

class Service
{
	/**
	 * Main function
	 *
	 * @param Request $request
	 * @param Response $response
	 * @throws Alert
	 * @author salvipascual
	 */
	public function _main(Request $request, Response $response)
	{
		// get list of services
		$services = Database::query("
			SELECT A.name, A.caption, A.icon, A.category, IFNULL(B.count, 0) AS count
			FROM service A
			LEFT JOIN (SELECT * FROM service_alerts WHERE person_id = {$request->person->id}) B
			ON A.name = B.name
			WHERE A.listed = 1 AND A.active = 1
			ORDER BY A.name ASC");

		// create the list of categories
		$categories = [];
		foreach ($services as $c) {
			if (isset($categories[$c->category])) {
				$categories[$c->category]->count++;
			} else {
				$category = new \stdClass();
				$category->code = $c->category;
				$category->name = Core::$serviceCategories[$c->category];
				$category->count = 1;
				$categories[$c->category] = $category;
			}
		}

		// sort the categories alphabetically
		ksort($categories);

		// create the content array
		$content = [
			'services' => $services,
			'categories' => $categories
		];

		// create response
		$response->setCache('month');
		$response->setTemplate('home.ejs', $content);
	}
}
