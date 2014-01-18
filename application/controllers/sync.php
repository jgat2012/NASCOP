<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Sync extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function user($email) {
		$email = urldecode($email);
		$user = Sync_User::getUser($email);
		echo json_encode($user);
	}

	public function drugs() {
		$user = Sync_Drug::getAll();
		echo json_encode($user);
	}

	public function facilities() {
		$user = Sync_Facility::getAll();
		echo json_encode($user);
	}

}
