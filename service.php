<?php

use Apretaste\Request;
use Apretaste\Response;
use Framework\Core;
use Framework\Database;

class Service
{
	/**
	 * Home screen for the app, list of widgets and services
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _main (Request $request, Response $response)
	{
		// get list of widgets
		$preferences = Database::queryFirst("SELECT widgets, favorite_services FROM person WHERE id = {$request->person->id}");

		// get data for the widgets
		$widgets = [];

		// social
		if(strpos($preferences->widgets, 'social') !== false) {
			$widgets['social'] = (Object) [
				'amigos' => 112,
				'ranking' => 412,
				'mensajes' => 1652,
				'retos' => 12,
			];
		}

		// online
		if(strpos($preferences->widgets, 'online') !== false) {
			$widgets['online'] = (Object) [
				'dias' => 112,
				'semana' => 3,
			];
		}

		// experiencia
		if(strpos($preferences->widgets, 'experiencia') !== false) {
			$widgets['experiencia'] = (Object) [
				'experiencia' => 1142,
				'nivel' => 'Diamante',
			];
		}

		// pizarra
		if(strpos($preferences->widgets, 'pizarra') !== false) {
			$widgets['pizarra'] = (Object) [
				'like' => 912,
				'comment' => 123,
			];
		}

		// amuletos
		if(strpos($preferences->widgets, 'amuletos') !== false) {
			$widgets['amuletos'] = (Object) [
				'one' => 'camera',
				'two' => 'heart-broken',
				'three' => 'bomb',
			];
		}

		// ayuda
		if(strpos($preferences->widgets, 'ayuda') !== false) {
			$widgets['ayuda'] = (Object) [
				'tickets' => 123,
			];
		}

		// rifa
		if(strpos($preferences->widgets, 'rifa') !== false) {
			$widgets['rifa'] = (Object) [
				'fecha' => 'Nov 12',
				'tickets' => 123,
			];
		}

		// bolita
		if(strpos($preferences->widgets, 'bolita') !== false) {
			$widgets['bolita'] = (Object) [
				'day_one' => '12',
				'day_two' => '100',
				'day_three' => '1',
				'night_one' => '23',
				'night_two' => '65',
				'night_three' => '98',
			];
		}

		// piropazo
		if(strpos($preferences->widgets, 'piropazo') !== false) {
			$widgets['piropazo'] = (Object) [
				'parejas' => 12,
				'likes' => 123,
			];
		}

		// noticia
		if(strpos($preferences->widgets, 'noticia') !== false) {
			$widgets['noticia'] = (Object) [
				'titulo' => trim(substr('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incident', 0, 80)) . '...',
				'canal' => 'Diario de Cuba',
				'comment' => 123,
			];
		}

		// get list of services
		$services = Database::query("
			SELECT A.name, A.caption, A.icon, A.category, IFNULL(B.count, 0) AS count
			FROM service A
			LEFT JOIN (SELECT * FROM service_alerts WHERE person_id = {$request->person->id}) B
			ON A.name = B.name
			WHERE A.listed = 1 AND A.active = 1
			ORDER BY A.name ASC");

		// get services categories
		$cats = Database::queryCache("
			SELECT COUNT(*) AS cnt, category 
			FROM service
			WHERE listed = 1 AND active = 1
			GROUP BY category");

		// organize the categories
		$categories = [];
		foreach ($cats as $key => $value) {
			$categories[$value->category] = $value->cnt;
		}

		// get the favorite services
		$favorites = [];
		foreach ($services as $item) {
			if(strpos($preferences->favorite_services, $item->name) !== false) {
				$favorites[] = $item;
			}
		}

		// create the content array
		$content = [
			'widgets' => $widgets,
			'favorites' => $favorites,
			'services' => $services,
			'categories' => $categories
		];

		// create response
		$response->setCache('month');
		$response->setTemplate('home.ejs', $content);
	}

	/**
	 * List of widgets to edit
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _widgets (Request $request, Response $response)
	{
		// get list of widgets
		$preferences = Database::queryFirst("SELECT widgets, favorite_services FROM person WHERE id = {$request->person->id}");

		// get list of services
		$services = Database::query("SELECT name, caption FROM service WHERE listed = 1 AND active = 1 ORDER BY name ASC");

		// create the content array
		$content = [
			'services' => $services,
			'widgets' => $preferences->widgets,
			'favorites' => $preferences->favorite_services
		];

		// create response
		$response->setCache();
		$response->setTemplate('widgets.ejs', $content);
	}

	/**
	 * List of widgets to edit
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _salvar (Request $request, Response $response)
	{
		// save the widgets
		$widgets = implode(',', $request->input->data->widgets);
		$favorites = $request->input->data->favorites;

		// save widgets to the user profile
		Database::queryFirst("
			UPDATE person 
			SET widgets = '$widgets', favorite_services = '$favorites'
			WHERE id = {$request->person->id}");

		// create response
		return $this->_main($request, $response);
	}
}
