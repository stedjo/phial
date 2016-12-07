<?php

// ~~~~~~  Global functions ~~~~~~~ //

function url()
{

}

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

function dd($v, $die=false)
{
    echo "<pre class='xdebug-var-dump'>";
    print_r($v);
    echo "</pre>";
    if($die) die();
}