<?php

namespace Pw\ApiResponse;

class ApiResponse
{
    public function jsonApi($status, $data="")
    {
        $result = [
            "status" => $status
        ];

        if (is_string($data)) {
            $result["message"] = $data;
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $result[$key] = $value;
            }
        }

        if (!isset($result["message"])) {
            if ($status == 200) {
                $result["message"] = "L'opération a réussi.";
            } elseif ($status == 500) {
                $result["message"] = "Une erreur est survenue";
            }
        }

        return $result;
    }
}