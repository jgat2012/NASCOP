<?php
class Escm_Drug extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('name', 'varchar', 255);
		$this -> hasColumn('abbreviation', 'varchar', 255);
		$this -> hasColumn('strength', 'varchar', 255);
		$this -> hasColumn('packsize', 'int', 7);
		$this -> hasColumn('formulation', 'varchar', 255);
		$this -> hasColumn('unit', 'varchar', 255);
		$this -> hasColumn('note', 'varchar', 255);
		$this -> hasColumn('weight', 'int', 4);
		$this -> hasColumn('category_id', 'int', 11);
		$this -> hasColumn('regimen_id', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('escm_drug');
		$this -> hasMany('escm_drug_merge as drug', array('local' => 'id', 'foreign' => 'drug_id'));
		$this -> hasMany('escm_drug_merge as merged_with', array('local' => 'id', 'foreign' => 'merged_with'));
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("escm_drug");
		$sync_drug = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $sync_drug;
	}

	public function getAllSettings() {
		$query = Doctrine_Query::create() -> select("name,abbreviation,strength,packsize,formulation,unit,weight") -> from("escm_drug");
		$sync_drug = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $sync_drug;
	}

	public function getActive() {
		$drug_name = "CONCAT_WS('] ',CONCAT_WS(' [',name,abbreviation),CONCAT_WS(' ',strength,formulation)) as name";
		$query = Doctrine_Query::create() -> select("id,$drug_name") -> from("escm_drug") -> where("category_id='1' or category_id='2' or category_id='3'") -> orderBy("name asc");
		$sync_drug = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $sync_drug;
	}

	public function getActiveList() {
		$drug_name = "CONCAT_WS('] ',CONCAT_WS(' [',name,abbreviation),CONCAT_WS(' ',strength,formulation)) as Drug,unit as Unit_Name,packsize as Pack_Size,category_id as Category";
		$query = Doctrine_Query::create() -> select("id,$drug_name") -> from("escm_drug") -> where("category_id='1' or category_id='2' or category_id='3'") -> orderBy("category_id asc");
		$sync_drug = $query -> execute();
		return $sync_drug;
	}

	public function getPackSize($id) {
		$query = Doctrine_Query::create() -> select("packsize") -> from("escm_drug") -> where("id='$id'");
		$sync_drug = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $sync_drug[0];
	}
	
	public function getDrugId($drug_name,$drug_abbr,$strength,$pack_size){
		$and = "";
		if($drug_abbr!=''){
			$and .= "AND abbreviation = '$drug_abbr' ";
		}
		if($strength!=''){
			$and .= "AND strength='$strength'  ";
		}
		if($pack_size!=''){
			$and .= "AND packsize ='$pack_size' ";
		}
		$query = Doctrine_Query::create() -> select("id") -> from("escm_drug") -> where("name='$drug_name'  $and") ->limit('1');
		$sync_drug = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $sync_drug;
	}
	
	public function getNotMergedDrugs(){
		$query = Doctrine_Query::create() -> select("sdm.drug_id, d.name,d.abbreviation,d.strength,d.formulation,d.unit,d.packsize") 
									      -> from("escm_drug d")-> leftJoin('d.drug sdm')-> where("sdm.drug_id IS NULL OR (sdm.drug_id=sdm.merged_with AND sdm.visible='1')");
		$result = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		//return $query->getSqlQuery();
		return $result;
	}
	
	public function getDrugs(){
		$query = Doctrine_Query::create() -> select("d.name,d.abbreviation,d.strength,d.formulation,d.unit,d.packsize") 
									      -> from("escm_drug d");
		$result = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		//return $query->getSqlQuery();
		return $result;
	}

}
?>