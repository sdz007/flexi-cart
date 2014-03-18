<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name: flexi cart lite library
*
* Author: 
* Rob Hussey
* flexicart@haseydesign.com
* haseydesign.com/flexicart
*
* Copyright 2012 Rob Hussey
* 
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
* 
* http://www.apache.org/licenses/LICENSE-2.0
* 
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* Description: A full shopping cart library
* Created: 01/01/2012
* Requirements: PHP5 or above and Codeigniter 2.0+
*/

class Flexi_cart_storage
{
	var $cart_data;
	public function __construct( $params )
	{
		$this->CI =& get_instance();
		$this->cart_session = $params['cart_session'];
		$this->cart_data = array();
		try {
			$this->redis = new Redis();
			$this->redis->connect($this->CI->config->item('sess_redis_host'), $this->CI->config->item('sess_redis_port'));
			//$this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP); //php5.4 can not work
		}catch(RedisException $e) {
		
		}
	}
	
	public function store_cart_data( $data , $newval = '') {
		if (is_string($data))
		{
			$data = array($data => $newval);
		}
		if (count($data) > 0)
		{
			foreach ($data as $key => $val)
			{
				$this->cart_data[$key] = $val;
			}
		}
		$this->persist();
	}
	
	function fetch_cart_data()
	{
		$row = json_decode($this->redis->get($this->cart_session), TRUE);
		if ( ! $row) {
			return FALSE;
		}
		return $row['flexi_cart'];
	}
	
	function delete_cart_data($newdata = array())
	{
		if (is_string($newdata))
		{
			$newdata = array($newdata => '');
		}
	
		if (count($newdata) > 0)
		{
			foreach ($newdata as $key => $val)
			{
				unset($this->cart_data[$key]);
			}
		}
		$this->persist();
	}
	
	private function persist() {
		try {
			$this->redis->multi()
			->delete($this->cart_session)
			->setex($this->cart_session, 3600, json_encode($this->cart_data))
			->exec();
		}catch(RedisException $e) {
		
		}
	}
}