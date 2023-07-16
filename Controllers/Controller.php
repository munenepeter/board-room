<?php

/**
 * Main controller 
 * 
 * 
 * @author Peter.Njega <munenenjega@gmail.com>
 * 
 */

namespace BoardRoom\Controllers;

use BoardRoom\Core\Mantle\Middleware;
use BoardRoom\Core\Mantle\Request;
use BoardRoom\Core\Mantle\Paginator;

class Controller {


    public function middleware($middleware) {
        return (new Middleware)->middleware($middleware);
    }
    public function request() {
        return (new Request);
    }
    public function upload(array $file, string $location, int $max_size, array $mime_types) {
        return $this->request()->upload($file, $location, $max_size, $mime_types);
    }
    public function paginate(array $data, $per_page) {
        return Paginator::paginate($data, $per_page);
    }
    /**
     * Return a json response
     * 
     * @param mixed $values what to send back
     * @param int $code http response code
     * 
     * @return void
     */
    public function json(mixed $values, int $code = 200): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode($values, JSON_UNESCAPED_UNICODE);
    }
}
