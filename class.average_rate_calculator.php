<?php
//This file is part of FreePBX.
//
//    FreePBX is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 2 of the License, or
//    (at your option) any later version.
//
//    FreePBX is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with FreePBX.  If not, see <http://www.gnu.org/licenses/>.
//
//   Copyright 2007 Greg MacLellan
//
class average_rate_calculator {
	var $_max_age;
	var $_values;
	
	/** Constructor 
	 * @param   array	A reference to an array to use for storage. This will be populated with key/value pairs that store the time/value, respectively.
	 * 			Because it is passed by reference, it can be stored externally in a session or database, allowing persistant use of this object
	 *			across page loads.
	 * @param  int	The maximum age of values to store, in seconds
	 */
	function average_rate_calculator(&$storage_array, $max_age) {
		$this->_max_age = $max_age;
		if (!is_array($storage_array)) {
			$storage_array = array();
		}
		$this->_values =& $storage_array;
	}
	/** Adds a value to the array
	 * @param  float	The value to add
	 * @param  int	The timestamp to use for this value, defaults to now
	 */
	function add($value, $timestamp=null) {
		if (!$timestamp) $timestamp = time();
		$this->_values[$timestamp] = $value;
	}
	/** Calculate the average per second value 
	 * @return  The average value, as a rate per second
	 */
	function average() {
		$this->_clean();
		
		$avgs = array();
		$last_time = false;
		$last_val = false;
		foreach ($this->_values as $time=>$val) {
			if ($last_time) {
				$avgs[] = ($val - $last_val) / ($time - $last_time);
			}
			$last_time = $time;
			$last_val = $val;
		}
		// return the average of all our averages
		if ($count = count($avgs)) {
			return array_sum($avgs) / $count;
		} else {
			return 'unknown';
		}
	}
	/** Clean old values out of the array
	 */
	function _clean() {
		$too_old = time() - $this->_max_age;
		
		foreach (array_keys($this->_values) as $key) {
			if ($key < $too_old) {
				unset($this->_values[$key]);
			}
		}
	}
}

?>
