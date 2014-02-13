<?php
class User_Emails extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('email_address', 'varchar', 150);
		$this -> hasColumn('active', 'int', 2);

	}

	public function setUp() {
		$this -> setTableName('user_emails');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("user_emails");
		$mail = $query -> execute();
		return $mail;
	}

	public function getActive() {
		$query = Doctrine_Query::create() -> select("*") -> from("user_emails") -> where("active='1'");
		$mail = $query -> execute();
		return $mail;
	}
	
	public function getMail($id) {
		$query = Doctrine_Query::create() -> select("email_address") -> from("user_emails") -> where("id='$id' and active='1'");
		$mail = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $mail[0];
	}

}
?>