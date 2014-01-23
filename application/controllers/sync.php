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
                $user = Facilities::getAll();
                echo json_encode($user);
        }

        public function regimen() {
                $user = Regimen::getAllRegimens();
                echo json_encode($user);
        }

        public function cdrr($cdrr_id = 0) {
                if ($cdrr_id == 0) {
                        $main_array = $_POST;
                        $main_array = $post_array['data'];
                        $main_array = json_decode($main_array, TRUE);
                        $cdrr = array();
                        $cdrr_items = array();
                        $cdrr_log = array();
                        $temp_items = array();
                        $temp_log = array();
                        foreach ($main_array as $index => $main) {
                                if ($index == "ownCdrr_item") {
                                        $cdrr_items[$index] = $main;
                                } else if ($index == "ownCdrr_log") {
                                        $cdrr_log[$index] = $main;
                                } else {
                                        $cdrr[$index] = $main;
                                }
                        }
                        //Insert the cdrr and retrieve the auto_id assigned to it,this will be the cdrr_id
                        $this -> db -> insert('cdrr', $cdrr);
                        $cdrr_id = $this -> db -> insert_id();

                        //Loop through cdrr_log and add cdrr_id
                        foreach ($cdrr_log as $index => $log) {
                                foreach ($log as $ind => $lg) {
                                        if ($ind == "cdrr_id") {
                                                $temp_log['cdrr_id'] = $cdrr_id;
                                        } else {
                                                $temp_log[$ind] = $lg;
                                        }
                                }
                                $this -> db -> insert('cdrr_log', $temp_log);
                                unset($temp_log);
                        }

                        //Loop through cdrr_item and add cdrr_id
                        foreach ($cdrr_items as $index => $cdrr_item) {
                                foreach ($cdrr_item as $counter => $items) {
                                        foreach ($items as $ind => $item) {
                                                if ($ind == "cdrr_id") {
                                                        $temp_items[$counter]['cdrr_id'] = $cdrr_id;
                                                } else {
                                                        $temp_items[$counter][$ind] = $item;
                                                }
                                        }
                                }
                        }
                        $this -> db -> insert_batch('cdrr_item', $temp_items);
                }
                echo json_encode($this -> getCdrr($cdrr_id));
        }

        public function getCdrr($cdrr_id) {
                $main_array = array();
                $main_array = Cdrr::getCdrr($cdrr_id);
                $main_array["ownCdrr_item"] = Cdrr_Item::getItems($cdrr_id);
                $main_array["ownCdrr_log"] = Cdrr_Log::getLogs($cdrr_id);
                return $main_array;
        }

}