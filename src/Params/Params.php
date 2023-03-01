<?php

namespace Pw\Params;

class Params  {

    /**
     * @param objet $request
     * @param string $param
     * @param mixed  $default
     * @return mixed
    */
    function get($request, $param, $default = null) {
        return  $request->query->get($param, $default);
    }

    /**
     * @param objet $request
     * @param string $param
     * @param mixed  $default
     * @return mixed
    */
    function post($request, $param, $default = null) {
        return $request->request->get($param, $default);
    }

    /**
     * @param array $arr
     * @param string $key
     * @param mixed  $default
     * @return mixed
    */
    function array($arr, $key, $default = "") {
        return isset($arr[$key]) ? $arr[$key] : $default;
    }

    /**
     * @param string $status
     * @param string $message
     * @param array  $default
     * @return array
    */
    function response($status, $message, $data = []) {
        return array(
            'status' => $status,
            'message' => $message,
            'data' => $data
        );
    } 
}