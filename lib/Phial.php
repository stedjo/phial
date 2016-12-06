<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once 'common.php';

class Phial {

    public $errors;
    public $config;
    public $request;
    public $response;
    public $request_uri;
    public $routes = array();

    public $app;
    public $controller;
    public $action;
    public $params;

    public $template;
    public $logger;
    public $registry;

    public $is_cli;

    public $http_method;


    function __construct($basedir=null)
    {

        if(!defined('phial_path'))
        {

            $this->is_cli = php_sapi_name() === 'cli';

            define('phial_path', str_replace('\\', '/', $basedir).'/');
            define('phial_url', 'http://'.$_SERVER['SERVER_NAME'].str_replace('\\', '/', (strlen(dirname($_SERVER['SCRIPT_NAME'])) > 1) ? dirname($_SERVER['SCRIPT_NAME']).'/' : dirname($_SERVER['SCRIPT_NAME'])));

            $this->load_config();

            if($dbfcg = $this->get_config('database'))
            {

                $this->database = ActiveRecord\Config::instance();
                ActiveRecord\Connection::$datetime_format = 'Y-m-d H:i:s';
                $this->database->set_model_directory($dbfcg['models']);
                $this->database->set_connections(array($this->get_config('enviroment') => $dbfcg['connection']));
                $this->database->set_default_connection($this->get_config('enviroment'));

            }

            // autoloads classes
            spl_autoload_register(function ($class) {
                $class_file = phial_path . 'lib/' . $class . '.php';
                $model_file = phial_path . 'models/' . strtolower($class) . '.php';
                if(is_readable($class_file)){ require $class_file; }
                if(is_readable($model_file)){ require $model_file; }
            });

        }


        $this->request = new Request();
        $this->template = new League\Plates\Engine(phial_path.'templates/', 'phtml');
        $this->logger = new Logger($this->get_config('log_path'));
        $this->registry = new Registry();

    }


    function execute_app_action($app, $controller="index", $action="index", $params=array())
    {

        $this->response = ""; // empty response

        $class_file = phial_path."apps/{$app}/{$controller}.php";

        if(is_readable($class_file))
        {
            require_once $class_file;

            $controllerClassname = "apps\\{$app}\\{$controller}";

            $obj = new $controllerClassname;
            $obj->app = $app;
            $obj->controller = $controller;
            $obj->action = $action;
            $obj->params = $params;
            $obj->template->addFolder($controller, phial_path.'apps/'.$app.'/templates/'.$controller);

            if(method_exists($obj, $action))
            {

                if(method_exists($obj, 'before_action')) { $this->response .= @$obj->before_action(); }

                $this->response .= call_user_func_array(array($obj, $action), $params);

                if(method_exists($obj, 'after_action')) { $this->response .= @$obj->after_action(); }

            } else {
                $this->errors[] = "App <b>{$app}</b>: Missing action {$action} in controller <b>{$controller}</b>";
                return false;
            }

        } else {
            $this->errors[] = "App <b>{$app}</b>: Missing controller <b>{$controller}</b>";
            return false;
        }

        return true;
    }



    function render($template_name, $vars=array())
    {
        return $this->template->render($template_name, $vars);
    }


    function bind($var_name, $var_value)
    {
        $this->template->addData([$var_name => $var_value]);
    }


    function response($body, $code = 200, $headers = [])
    {
        http_response_code($code);

        array_walk($headers, function ($value, $key)
        {
            if (! preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $key)) {
                throw new InvalidArgumentException("Invalid header name - {$key}");
            }
            $values = is_array($value) ? $value : [$value];
            foreach ($values as $val) {
                if ( preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $val) || preg_match('/[^\x09\x0a\x0d\x20-\x7E\x80-\xFE]/', $val))
                {
                    throw new InvalidArgumentException("Invalid header value - {$val}");
                }

            }

            header($key.': '.implode(',', $values));

        });

        echo $body;
    }


    function load_config($cfg_file = "default.php")
    {
        $this->config = require_once phial_path.'config/'.$cfg_file;
        return true;
    }

    function get_config($key)
    {
        return (isset($this->config[$key])) ? $this->config[$key] : null;
    }


    /*
     * Adding a route
     */
    function route($path, $closure = null)
    {
        $this->routes[$path] = $closure;
        return true;
    }

    function get_method()
    {
        return $this->http_method;
    }

    function set_method($method)
    {
        $this->http_method = $method;
    }


    function get_uri()
    {
        $request_uri = substr($_SERVER['REQUEST_URI'], strlen(substr($_SERVER['SCRIPT_NAME'], 0, -9)));
        return '/'.rtrim($request_uri, '/');
    }

    function run()
    {

        // this removes the index.php and base folder from request url
        $this->request_uri = $this->get_uri();

        /*
         *  Checking custom rules first
         */
        $custom_rule_found = false;
        foreach($this->routes as $path => $closure)
        {

            if(preg_match("~^{$path}$~", $this->request_uri, $match))
            {
                $custom_rule_found = true;
                unset($match[0]);
                $this->response = call_user_func_array($closure, $match);
            }
        }


        /*
         *  Automagic rules
         */
        if($custom_rule_found == false)
        {

            // split parameters
            $request = explode('/', ltrim($this->request_uri, '/'));
            $app = array_shift($request);
            $controller = array_shift($request);
            $action = array_shift($request);

            if($app && $controller && $action)
            {
                $this->execute_app_action($app, $controller, $action, $request);

            } else {

                $this->errors[] = "Missing parameters. Automagic routing needs 3 parameters.";

            }

        }


        // shows errors if any
        if(count($this->errors))
        {
            var_dump($this->errors);
        }

        echo $this->response;

    }

}
