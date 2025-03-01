<?php

namespace App\Exceptions;

class MappingException extends HttpException
{
    public function __construct(
        int $code = 4000
    ) {
        parent::__construct(
            400,
            "Bad Request",
            "The request could not be understood.",
            $code
        );
    }
}
