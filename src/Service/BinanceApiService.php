<?php
namespace Sdtech\BinanceApiLaravel\Service;


abstract class BinanceApiService {
    
    public function __call($method, $args)
    {
        $a ="_$method";
        return $this->$a(...$args);
    }
}
