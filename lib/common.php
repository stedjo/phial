<?php

// ~~~~~~  Global functions ~~~~~~~ //


/**
 * Sends user to a new URL
 * @param string $path the url path
 */
function redirect($path)
{
    if (headers_sent()) {
        echo "<script>document.location.href='{$path}';</script>\n";
    } else {
        //@ob_end_clean(); // clear output buffer
        header( 'HTTP/1.1 301 Moved Permanently' );
        header( "Location: ". $path );
    }
    exit;

}

/**
 * Debug shortcut
 * @param mixed $v whatever you want to debug
 * @param bool $die if true it dies
 */
function dd($v, $die=false)
{
    echo "<pre class='xdebug-var-dump'>";
    print_r($v);
    echo "</pre>";
    if($die) die();
}


/**
 * @param $data array of result data
 * @param string $root name of the root element
 * @return string
 */
function to_json($data, $root="")
{

    $result = array();

    if(is_array($data))
    {
        foreach($data as $row)
        {
            array_push($result, $row->to_array()); //using to_array instead of to_json
        }

        echo ($root!="") ? json_encode(array($root => $result)) : json_encode($result);

    } else {

        echo ($root!="") ? json_encode(array($root => $data)) : json_encode($data);

    }

}