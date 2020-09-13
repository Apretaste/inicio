<?php

use Apretaste\Chats;
use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Challenges;
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

		// get data for the widgets
		$widgets = [];

		// do not get widget data for guest users
		if (empty($request->person->isGuest)) {
			// get list of widgets
			$preferences = Database::queryFirst("SELECT widgets, favorite_services, week_rank FROM person WHERE id = {$request->person->id}");

			// social
			if (strpos($preferences->widgets, 'social') !== false) {
				$widgets['social'] = (Object) [
					'amigos' => count($request->person->getFriends()),
					'ranking' => $preferences->week_rank,
					'mensajes' => Chats::unreadCount($request->person->id),
					'retos' => count(array_filter(Challenges::getList($request->person->id), static function ($ch) {
						return (int) $ch->done == 0;
					})),
				];
			}

			// online
			if (strpos($preferences->widgets, 'online') !== false) {
				$widgets['online'] = (Object) [
					'desde' => date("d/m/Y", strtotime($request->person->insertionDate)),
					'semana' => $request->person->daysStreak,
				];
			}

			// experiencia
			if (strpos($preferences->widgets, 'experiencia') !== false) {
				$widgets['experiencia'] = (Object) [
					'experiencia' => $request->person->experience,
					'nivel' => $request->person->level,
				];
			}

			// pizarra
			if (strpos($preferences->widgets, 'pizarra') !== false) {
				$data = Database::queryFirst("
					SELECT SUM(likes) AS likes, SUM(comments) AS comments
					FROM _pizarra_notes
					WHERE id_person = {$request->person->id}
					AND inserted >= CURRENT_DATE");

				// set widget
				$widgets['pizarra'] = (Object) [
					'like' => empty($data->likes) ? 0 : $data->likes,
					'comment' => empty($data->comments) ? 0 : $data->comments,
				];
			}

			// amuletos
			if (strpos($preferences->widgets, 'amuletos') !== false) {
				// get data
				$data = Database::query("
					SELECT B.icon
					FROM _amulets_person A
					JOIN _amulets B
					ON A.amulet_id = B.id
					WHERE A.person_id = {$request->person->id}
					AND A.active = 1
					AND (A.expires IS NULL OR A.expires > CURRENT_TIMESTAMP)");

				// set widget
				$widgets['amuletos'] = (Object) [
					'one' => empty($data[0]->icon) ? '' : $data[0]->icon,
					'two' => empty($data[1]->icon) ? '' : $data[1]->icon,
					'three' => empty($data[2]->icon) ? '' : $data[2]->icon,
				];
			}

			// ayuda
			if (strpos($preferences->widgets, 'ayuda') !== false) {
				// get data
				$data = Database::queryFirst("SELECT COUNT(id) as cnt FROM support_tickets WHERE status = 'NEW' AND from_id = {$request->person->id}");

				// set widget
				$widgets['ayuda'] = (Object) [
					'tickets' => $data->cnt,
				];
			}

			// rifa
			if (strpos($preferences->widgets, 'rifa') !== false) {
				// get the current raffle deadline
				$data = Database::queryFirst('SELECT end_date FROM raffle WHERE CURRENT_TIMESTAMP BETWEEN start_date AND end_date ORDER BY start_date');
				$deadline = empty($data->end_date) ? '' : $data->end_date;

				// get number of tickets by the user
				$data = Database::queryFirst("SELECT COUNT(ticket_id) AS cnt FROM ticket WHERE raffle_id is NULL AND person_id = '{$request->person->id}'");
				$tickets = (int) $data->cnt;

				// set widgets
				$widgets['rifa'] = (Object) [
					'fecha' => $deadline,
					'tickets' => $tickets,
				];
			}

			// bolita
			if (strpos($preferences->widgets, 'bolita') !== false) {
				// get data
				$data = Database::queryFirst("
					SELECT 
						corrida, papeleta_day, fijo_day, corrido1_day, corrido2_day, 
						papeleta_night, fijo_night, corrido1_night, corrido2_night
				 	FROM _bolita_results ORDER BY corrida DESC");

				// set widgets
				$widgets['bolita'] = $data;
			}

			// piropazo
			if (strpos($preferences->widgets, 'piropazo') !== false) {
				// get data
				$parejas = Database::queryFirst("
					SELECT COUNT(id) AS cnt
					FROM _piropazo_relationships 
					WHERE status = 'match'
					AND (id_from = {$request->person->id} OR id_to = {$request->person->id})");

				$likes = Database::queryFirst("
					SELECT likes AS cnt
					FROM _piropazo_people 
					WHERE id_person = {$request->person->id}");

				// set widgets
				$widgets['piropazo'] = (Object) [
					'parejas' => $parejas->cnt,
					'likes' => empty($likes->cnt) ? 0 : $likes->cnt,
				];
			}

			// noticia
			if (strpos($preferences->widgets, 'noticia') !== false) {
				// get data
				$data = Database::queryFirst("
					SELECT A.id, A.title, A.comments, B.caption AS channel
					FROM _news_articles A
					JOIN _news_media B
					ON A.media_id = B.id
					WHERE A.inserted >= DATE_ADD(CURRENT_DATE, INTERVAL -3 DAY)
					ORDER BY A.comments DESC 
					LIMIT 1");

				// set widget
				if ($data) {
					$widgets['noticia'] = (Object) [
						'id' => $data->id,
						'titulo' => trim(substr(quoted_printable_decode($data->title), 0, 80)) . '...',
						'canal' => $data->channel,
						'comment' => $data->comments,
					];
				}
			}

			// favoritos
			if (strpos($preferences->widgets, 'favoritos') !== false) {
				$widgets['favoritos'] = [];
				foreach ($services as $item) {
					if (strpos($preferences->favorite_services, $item->name) !== false) {
						$widgets['favoritos'][] = $item;
					}
				}
			}
		}

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

		// create the content array
		$content = [
			'widgets' => $widgets,
			'services' => $services,
			'categories' => $categories
		];

		// create response
		$response->setCache();
		$response->setTemplate('home.ejs', $content);
	}

	/**
	 * List of widgets to edit
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _editar(Request $request, Response $response)
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
		$response->setTemplate('edit.ejs', $content);
	}

	/**
	 * List of widgets to edit
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _salvar(Request $request, Response $response)
	{
		// save the widgets
		$widgets = implode(',', $request->input->data->widgets);
		$favorites = $request->input->data->favorites;

		// save widgets to the user profile
		Database::queryFirst("
			UPDATE person 
			SET widgets = '$widgets', favorite_services = '$favorites'
			WHERE id = {$request->person->id}");

		Challenges::complete('edit-widgets', $request->person->id);

		// create response
		return $this->_main($request, $response);
	}
}
