<?php

class Request
{

    private $_r;

    /**
     * Assign the $_POST by reference to a local variable $_r
     */
    function __construct()
    {
        $this->_r = $_REQUEST;
    }


    /**
     * @param $key string the name of the parameter to get
     * @param $default mixed the default value if the parameter key does not exists
     * @return mixed the value of the parameter, or default, or false if none
     */
    function get($key, $default=null)
    {

        if($this->has($key))
        {
            return $this->_r[$key];
        } elseif(isset($default)) {
            return $default;
        } else {
            return false;
        }

    }


    /**
     * Returns all the parameters
     * @return array all params
     */
    function get_all()
    {
        return $this->_r;
    }


    /**
     * Returns the raw php input
     * @param $json_decode boolean require a json decode
     * @param $url_decode boolean require an url decode
     * @return mixed raw output
     */
    function raw($json_decode=false, $url_decode=false)
    {
        $raw = file_get_contents("php://input");

        if($json_decode) { $raw = json_decode($raw); }
        if($url_decode) { $raw = urldecode($raw); }

        return $raw;
    }



    /**
     * Sets a parameter, if it doesnt exists it will be created
     * @param $key string the name of the parameter to be set
     * @param $val mixed the value of the parameter to be set
     */
    function set($key, $val)
    {
        $this->_r[$key] = $val;
    }


    /**
     * Checks if the parameter exists
     * @param $key string the name of the parameter
     * @return bool returns true if found, false if not
     */
    function has($key)
    {
        if(isset($this->_r[$key]))
        {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Removes a named parameter from the request
     * @param $key string the name of the parameter to delete
     * @return bool returns true if deleted, false if no parameter found
     */
    function del($key)
    {
        if($this->has($key))
        {
            unset($this->_r[$key]);
            return true;
        } else {
            return false;
        }
    }


    /**
     * Returns true if a POST is sent, false if it's not
     * @return bool
     */
    function exists()
    {
        if($_POST)
        {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Returns true if the field passed is empty
     * @param $key field name
     * @return bool true if empty
     */
    function is_empty($key)
    {
        if($this->_r[$key] == "")
        {
            return true;
        } else {
            return false;
        }

    }

    /**
     * @return array Returns returns all the name of the params
     */
    function keys()
    {
        return array_keys($this->_r);
    }


    /**
     * @return int number of parameters
     */
    function count()
    {
        return count($this->_r);
    }


    /**
     * Returns the $_FILE with name passed
     * @param $key string input name
     */
    function file($key)
    {
        return $_FILES[$key];
    }


    /**
     * Funzione che ritorna solo i campi valorizzati come hash
     */
    function get_values()
    {
        $ret = array();
        foreach($this->_r as $field => $value)
        {
            if($value != "")
            {
                $ret[$field] = $value;
            }
        }
        return $ret;
    }



    /**
     * Returns ip address of the client
     * @return mixed ip address of client
     */
    function remote_addr()
    {

        if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
        {
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
        {
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;

    }

    /**
     * Returns an array with all the headers
     * @return mixed array with all the headers
     */
    function headers()
    {
        return getallheaders();
    }


}
