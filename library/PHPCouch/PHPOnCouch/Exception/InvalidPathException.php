<?php

namespace Dotenv\Exception;
require_once("ExceptionInterface.php");
use InvalidArgumentException;

/**
 * This is the invalid path exception class.
 */
class InvalidPathException extends InvalidArgumentException implements ExceptionInterface
{
    //
}
