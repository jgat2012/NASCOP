<?php
class Mail_List extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('name', 'varchar', 255);
		$this -> hasColumn('creator_id', 'int', 11);
		$this -> hasColumn('active', 'int', 2);

	}

	public function setUp() {
		$this -> setTableName('mail_list');
		$this -> hasOne('Users as User', array('local' => 'creator_id', 'foreign' => 'id'));
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("mail_list");
		$mail = $query -> execute();
		return $mail;
	}

	public function getActive() {
		$query = Doctrine_Query::create() -> select("*") -> from("mail_list") -> where("active='1'");
		$mail = $query -> execute();
		return $mail;
	}

}
?>