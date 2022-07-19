<?php
/**
 * Accept Upload of Sampole Files
 */

namespace App\Controller\Result;

class Upload extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->loadSiteData();
		$data['Page'] = array('title' => 'Result :: COA :: Upload');
		$data['Company'] = $_SESSION['Company'];

		switch ($_SERVER['REQUEST_METHOD']) {
		case 'GET':

			// @todo use dbc_auth and create an auth_context_ticket
			$data['coa_upload_hash'] = _encrypt(json_encode(array(
				'a' => 'coa-upload-bulk',
				'company_id' => $_SESSION['Company']['id'],
				'x' => $_SERVER['REQUEST_TIME'] + (86400 * 4)
			)));

			return $RES->write( $this->render('result/upload.php', $data) );

		case 'POST':

			if (1 == count($_FILES)) {
				if (0 == $_FILES['file']['error']) {

					$import_queue_path = sprintf('%s/var/import/%s', APP_ROOT, $_SESSION['License']['id']);
					$import_queue_file = sprintf('%s/%s', $import_queue_path, urlencode($_FILES['file']['name']));

					if (!is_dir($import_queue_path)) {
						mkdir($import_queue_path, 0755, true);
					}

					move_uploaded_file($_FILES['file']['tmp_name'], $import_queue_file);

				}
			}

			return $RES->withJSON(array(
				'status' => 'success',
			));

			break;
		}
	}

	/**
	 * Generate a Preview of the File
	 * @param [type] $REQ [description]
	 * @param [type] $RES [description]
	 * @param [type] $ARG [description]
	 * @return [type] [description]
	 */
	function preview($REQ, $RES, $ARG)
	{
		// $import_queue_path = sprintf('%s/var/import/%s', APP_ROOT, $_SESSION['License']['id']);

		$pdf_file = $this->resolveFile($_GET['f']);
		$png_file = preg_replace('/pdf$/i', 'png', $pdf_file);

		if (!is_file($png_file)) {
			// Get First Page as PNG
			// Conversion via Imagick CLI
			$cmd = array('/usr/bin/convert');
			$cmd[] = '-quiet';
			$cmd[] = '-background white';
			$cmd[] = '-fill white';
			$cmd[] = sprintf('-crop %dx%d+%d+%d', $w, $h, $x, $y);
			$cmd[] = escapeshellarg(sprintf('%s[0]', $pdf_file)); // the '[0]' bit tells Imagick to do first page only
			$cmd[] = escapeshellarg($png_file);
			//$cmd[] = 'png:-';
			$cmd[] = '2>/dev/null';
			$cmd = implode(' ', $cmd);
			//var_dump($cmd);
			$buf = shell_exec($cmd);
			// var_dump($buf);
		}

		// Emit PNG
		header('content-type: image/png');

		readfile($png_file);

		exit(0);

	}

	protected function resolveFile($f)
	{
		$f = basename($f);

		$import_queue_path = sprintf('%s/var/import/%s', APP_ROOT, $_SESSION['Company']['id']);
		$import_queue_file = $import_queue_path . '/' . $f;
		if (is_file($import_queue_file)) {
			return $import_queue_file;
		}

		$import_queue_path = sprintf('%s/var/import/%s', APP_ROOT, $_SESSION['License']['id']);
		$import_queue_file = $import_queue_path . '/' . $f;
		if (is_file($import_queue_file)) {
			return $import_queue_file;
		}

		_exit_text(sprintf('Bad File "%s"', $f), 400);
	}
}
