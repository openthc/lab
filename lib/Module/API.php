<?php
/**
 * Wraps all the Routing for the API Module
 */

namespace OpenTHC\Lab\Module;

class API extends \OpenTHC\Module\Base
{
	function __invoke($a)
	{
		// Instructions
		$a->get('', function($REQ, $RES) {
			$data = array('Page' => array('title' => 'API'));
			return $this->view->render($RES, 'page/api/index.html', $data);
		})->add('OpenTHC\Lab\Middleware\Menu');

		// The Versioned Endpoint
		$a->group('/v2015', function() {

			$this->get('/metric', function($REQ, $RES, $ARG) {

				$ret = array();
				$res = $this->DBC_Main->fetchAll('SELECT * FROM lab_metric ORDER BY type, name');
				foreach ($res as $rec) {
					$rec['meta'] = json_decode($rec['meta'], true);
					$ret[ $rec['id'] ] = array(
						'id' => $rec['id'],
						'type' => $rec['type'],
						'name' => $rec['name'],
						'uom' => $rec['meta']['uom'],
					);
				}

				return $RES->withJSON($ret, 200, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

			});

			// Return List of Samples
			// $this->get('/qa', function($REQ, $RES, $ARG) {
			// 	return require_once(APP_ROOT . '/api/qa/search.php');
			// })->add('OpenTHC\Lab\Middleware\Auth');

			//$this->post('/qa', function($REQ, $RES, $ARG) {
			//	die('Create QA Sample');
			//})->add($MWA);

			//$this->get('/qa/sample', function($REQ, $RES, $ARG) {
			//	die('List QA Samples');
			//})->add($MWA);

			// // Select QA Sample+Result
			// $this->get('/sample/{code}/result', function($REQ, $RES, $ARG) {
			// 	require_once(APP_ROOT . '/api/qa/sample.php');
			// });

			// Create a Sample
			//$this->post('/qa/sample', function($REQ, $RES, $ARG) {
			//	require_once(APP_ROOT . '/api/qa/sample-create.php');
			//})->add('Middleware_Auth');


			// Select Specific Lab Result
			$this->get('/result/{id}', 'OpenTHC\Lab\Controller\API\Result\Single');

			$this->get('/result/{id}.pdf', function($REQ, $RES, $ARG) {
				return require_once(APP_ROOT . '/api/qa/result.pdf.php');
			});

			// Create a Result
			$this->post('/result', 'OpenTHC\Lab\Controller\API\Result\Create');

			// Update Result
			$this->post('/result/{id}', 'OpenTHC\Lab\Controller\API\Result\Update');

		});

		// Publish a Result or Report
		$a->post('/v2018/pub', 'OpenTHC\Lab\Controller\API\Pub');

		// Update Result Metrics
		$a->post('/v2018/result/{id}/upload', 'OpenTHC\Lab\Controller\API\Result\Update:upload');

		$a->post('/v2022/pub', 'OpenTHC\Lab\Controller\API\v2022\Pub');

	}
}
