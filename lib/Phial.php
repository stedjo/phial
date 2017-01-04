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
    public $route_methods = array();


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

        $this->set_method(strtolower($_SERVER['REQUEST_METHOD']));

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

            $tmp_folder = phial_path.'apps/'.$app.'/templates/'.$controller;

            if(is_readable($tmp_folder))
            {
                $obj->template->addFolder($controller, $tmp_folder);
            } else {
                $this->errors[] = "Template folder missing <b>{$tmp_folder}</b>";
            }


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


    /**
     * Binds a variable to the template
     * @param string $var_name the variable name
     * @param string $var_value the variable value
     */
    function bind($var_name, $var_value)
    {
        $this->template->addData([$var_name => $var_value]);
    }


    /**
     * Sets the response
     * @param string $body the body of the response
     * @param int $code http code response
     * @param array $headers a list of all headers you want
     * @return string the body of the response
     */
    function response($body, $code = 200, $headers = [])
    {

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

        http_response_code($code);
        return $body;
    }


    /**
     * Loads the config file passed by param
     * @param string $cfg_file path to the config file
     * @return bool true if config file is found
     */
    function load_config($cfg_file = "default.php")
    {
        $this->config = require_once phial_path.'config/'.$cfg_file;
        return true;
    }

    /**
     * Returns the config option based on the key passed
     * @param string $key config key
     * @return mixed a single config option
     */
    function get_config($key)
    {
        return (isset($this->config[$key])) ? $this->config[$key] : null;
    }


    /**
     * Adding a route
     */
    function route($path, $closure = null)
    {
        $this->routes['get'][$path] = $closure;
        return true;
    }

    /**
     * Adding a GET verb
     */
    function get($path, $closure = null)
    {
        $this->routes['get'][$path] = $closure;
        return true;
    }

    /**
     * Adding a PUT verb
     */
    function put($path, $closure = null)
    {
        $this->routes['put'][$path] = $closure;
        return true;
    }

    /**
     * Adding a POST verb
     */
    function post($path, $closure = null)
    {
        $this->routes['post'][$path] = $closure;
        return true;
    }

    /**
     * Adding a DELETE verb
     */
    function delete($path, $closure = null)
    {
        $this->routes['delete'][$path] = $closure;
        return true;
    }

    /**
     * @return string returns the request's http method
     */
    function get_method()
    {
        return $this->http_method;
    }

    /**
     * @param string $method sets the request's http method
     */
    function set_method($method)
    {
        $this->http_method = $method;
    }

    /**
     * returns all the routing
     */
    function get_routes()
    {
        return $this->routes;
    }

    /**
     * @return string parses and returns the uri
     */
    function get_uri()
    {
        $request_uri = '/'.substr($_SERVER['REQUEST_URI'], strlen(substr($_SERVER['SCRIPT_NAME'], 0, -9)));
        return $request_uri;
    }

    /**
     * Prints out a list of errors
     */
    function render_errors()
    {
        echo "<hr noshade size='1' color='silver'><pre><h4>Phial Errors</h4><ul>";
        foreach($this->errors as $e) {
            echo "<li>{$e}</li>";
        }
        echo "</ul></pre>";
    }



    /**
     * Actually runs the Phial application
     */
    function run()
    {

        // defaulting the path
        $path = null;
        // default for custom routing
        $custom_rule_found = false;
        // defaulting wrong method
        $wrong_method = false;

        // this removes the index.php and base folder from request url
        $this->request_uri = $this->get_uri();

        /*
         *  Checking custom rules first
         */
        foreach($this->get_routes() as $method => $routes)
        {

            foreach($routes as $path => $closure)
            {

                $pattern = "@^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_]+)', preg_quote($path)) . "$@D";

                if(preg_match($pattern, $this->request_uri, $match))
                {

                    unset($match[0]); // first one must be removed

                    if($method == $this->get_method())
                    {
                        $wrong_method = false;
                        $this->response = call_user_func_array($closure, $match);
                        $custom_rule_found = true;

                    }

                }

            }

        }

        /*
         *  Automagic routing rules
         */
        if($wrong_method == true)
        {

            unset($this->response);
            $this->response(null, 405, null);

        } else {

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

        }


        // shows errors if any
        if(count($this->errors))
        {
            $this->render_errors();
        }

        echo $this->response;
        exit;
    }

}
