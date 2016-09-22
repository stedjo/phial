<?php

class Registry {

	// imposto il valore data la chiave
	function set($key, $value, $group="")
	{
		if($group) $_SESSION[$group][$key] = $value;
		else $_SESSION[$key] = $value;
	}

	// ritorno il valore data la chiave
	function get($key, $group="")
	{
		if($group) {
			return (isset($_SESSION[$group][$key])) ? $_SESSION[$group][$key]: '';
		} else {
			return (isset($_SESSION[$key])) ? $_SESSION[$key]: '';
		}
	}

	// verifico se esiste una chiave
	function has($key, $group="")
	{
		if($group) {
			if( isset($_SESSION[$group][$key]) ) return true;
		} else {
			if( isset($_SESSION[$key]) ) return true;
		}

		return false;
	}

	// elimino una chiave dalla sessione
	function del($key, $group="")
	{
		if($group) {
			unset($_SESSION[$group][$key]);
		} else {
			unset($_SESSION[$group][$key]);
		}
	}

}
