<?php
class Escm_drug_merge extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('drug_id', 'int', 4);
		$this -> hasColumn('merged_with', 'int', 4);
		$this -> hasColumn('visible', 'bit', 1);
	}
	
	public function setUp() {
		$this -> setTableName('escm_drug_merge');
		$this -> hasOne('escm_drug as drug', array('local' => 'drug_id', 'foreign' => 'id'));
		$this -> hasOne('escm_drug as merged', array('local' => 'merged_with', 'foreign' => 'id'));
	}
	
	public function getMergedDrugs(){
		$query = Doctrine_Query::create() -> select("DISTINCT(sdm.drug_id), d.name,d.abbreviation,d.strength,d.formulation,d.unit,d.packsize") 
									      -> from("escm_drug_merge sdm")
									      -> leftJoin('u.drug d');
		$result = $query -> execute();
		return $result;
	}
	
	public function getMergedDrugDetails(){
		$query = Doctrine_Query::create() -> select("sdm.drug_id, d.name as name,d.abbreviation as abbreviation,d.strength as strength,d.formulation as formulation,d.unit as unit,d.packsize as packsize,md.name as m_name,md.abbreviation as m_abbreviation,md.strength as m_strength,md.formulation as m_formulation,md.unit as m_unit,md.packsize as m_packsize") 
									      -> from("escm_drug_merge sdm")
									      -> leftJoin('sdm.drug d')
										  -> leftJoin('sdm.merged md')
										  -> where("sdm.drug_id!=sdm.merged_with");
		$result = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $result;
	}
	
	public function getDrugsToUnmerge($ids){
		$query = Doctrine_Query::create() -> select("sdm.drug_id, d.name as name,d.abbreviation as abbreviation,d.strength as strength,d.formulation as formulation") 
									      -> from("escm_drug_merge sdm")
									      -> leftJoin('sdm.drug d')
										  -> leftJoin('sdm.merged md')
										  -> where("sdm.drug_id!=sdm.merged_with AND sdm.id NOT IN ($ids)");
		$result = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $result;
	}

}
?>
