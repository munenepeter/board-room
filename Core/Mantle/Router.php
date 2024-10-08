<?php

namespace BoardRoom\Core\Mantle;


class Router {
    public $routes = [
        'GET' => [],
        'POST' => []
    ];

    public static function load($file) {

        $router = new static;
        require $file;
        return $router;
    }

    public function get($uri, $controller) {


        $uri = preg_replace('/{[^}]+}/', '(.+)', $uri);
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller) {
        $uri = preg_replace('/{[^}]+}/', '(.+)', $uri);
        $this->routes['POST'][$uri] = $controller;
    }
    


    public function direct($uri, $requestType) {

        /*
        ---- DON'T KNOW WHY IT DOES NOT WORK, THIS WAS PLACED HERE FOR TEST PURPOSES!!!---
        
        if (!array_key_exists($uri, $this->routes[$requestType])) {
            throw new \Exception("Oops, you forgot to include <b>/{$uri}</b>, There is no such route! ", 404);
            exit;
        }
        */
      
        $params = [];
        $regexUri = '';
        //  dd($this->routes[$requestType]);
        foreach ($this->routes[$requestType] as $route => $controller) {
            if (preg_match("%^{$route}$%", $uri, $matches) === 1) {
                //echo($uri.'--->'.$controller.'<br>');   
                $regexUri = $route;
                $this->routes[$requestType][$regexUri] = $controller;

                unset($matches[0]);
                $params = $matches;
                break;
            }
        }

        if(empty($this->routes[$requestType][$regexUri])){
            logger("Error", "Router: There is no route to handle <b>". strtoupper($requestType)." /{$uri}</b>");
            throw new \Exception("There is no route to handle <b>". strtoupper($requestType)." /{$uri}</b>", 404);
        }
        if (is_callable($this->routes[$requestType][$regexUri])) {
            $this->routes[$requestType][$regexUri](...$params);
        } else {

            if (!empty($regexUri) && $regexUri !== "") {
                return $this->callAction(
                    $params,
                    ...explode('@', $this->routes[$requestType][$regexUri])
                );
            } elseif (!array_key_exists($uri, $this->routes[$requestType])) {
                logger("Error", "Router: There is no route to handle <b>". strtoupper($requestType)." /{$uri}</b> ");
                throw new \Exception("There is no route to handle <b>". strtoupper($requestType)." /{$uri}</b>", 404);
            } else {
                return $this->callAction(
                    $params,
                    ...explode('@', $this->routes[$requestType][$uri])
                ); 
            }
            
        }
    }
    protected function callAction($params, $controller, $action) {

        $controller = "BoardRoom\\Controllers\\{$controller}";

        if (!class_exists($controller)) {
            logger("Debug", "Router: Class $controller doesn't not exist!");
            throw new \Exception("Class <b>$controller</b> doesn't not exist!", 500);
        }

        $controller = new $controller;

        $name = get_class($controller);

        if (!method_exists($controller, $action)) {
            logger("Debug", "Router: {$name} doesn't not respond to {$action} Method!");
            throw new \Exception("{$name} doesn't not respond to {$action} Method!", 500);
        }

        return $controller->$action(...$params);
    }
}
