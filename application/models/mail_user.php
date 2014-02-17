<?php
class Mail_User extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('list_id', 'int', 11);
		$this -> hasColumn('email_id', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('mail_user');
		$this -> hasOne('Mail_List as List', array('local' => 'list_id', 'foreign' => 'id'));
		$this -> hasOne('User_Emails as Email', array('local' => 'email_id', 'foreign' => 'id'));
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("mail_user");
		$mail = $query -> execute();
		return $mail;
	}

	public function getActive() {
		$query = Doctrine_Query::create() -> select("*") -> from("mail_user");
		$mail = $query -> execute();
		return $mail;
	}
	
	public function getLists($email_id) {
		$query = Doctrine_Query::create() -> select("*") -> from("mail_user") -> where("email_id='$email_id'");
		$mail = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $mail;
	}
	
	public function getMails($list_id) {
		$query = Doctrine_Query::create() -> select("*") -> from("mail_user") -> where("list_id='$list_id'");
		$mail = $query -> execute();
		return $mail;
	}
	

}
?>