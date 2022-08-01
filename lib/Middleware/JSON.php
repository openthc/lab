<?php
/**
 * Inflate the JSON inbound
 */

namespace OpenTHC\Lab\Middleware;

class JSON
{
	public function __invoke($REQ, $RES, $NMW)
	{
		// Alpha Magic
		$type = strtok($_SERVER['CONTENT_TYPE'], ';');
		$size = intval($_SERVER['CONTENT_LENGTH']);

		if (($size > 0) && ('application/json' == $type)) {

			$json = file_get_contents('php://input');
			$data = json_decode($json, true);

			if (empty($data)) {
				$e = json_last_error_msg();
				return $RES->withJSON(array(
					'status' => 'failure',
					'result' => $e,
					'detail' => 'IFC#088: JSON Parsing Error',
				), 400);
			}

			$REQ = $REQ->withAttribute('JSON', $data);

		}

		$RES = $NMW($REQ, $RES);

		// Omega Magic
		// $want = $_SERVER['HTTP_ACCEPT'];

		return $RES;
	}
}
