<?php
$moduleID = basename(dirname(__FILE__));
Bitrix\Main\Loader::registerAutoLoadClasses(
    $moduleID,
    array(
        's34web\Mobile\Api\Init' => 'lib/Init.php',
        's34web\Mobile\Api\Options' => 'lib/Options.php',
        's34web\Mobile\Api\Controller' => 'lib/Controller.php',
        's34web\Mobile\Api\Request' => 'lib/Request.php',
        's34web\Mobile\Api\Response' => 'lib/Response.php',
        's34web\Mobile\Api\Router' => 'lib/Router.php',
        's34web\Mobile\Api\controllers\v1\Main' => 'lib/controllers/v1/Main.php',

        'Nowakowskir\JWT\JWT' => 'vendor/php-jwt/src/JWT.php',
        'Nowakowskir\JWT\TokenDecoded' => 'vendor/php-jwt/src/TokenDecoded.php',
        'Nowakowskir\JWT\TokenEncoded' => 'vendor/php-jwt/src/TokenEncoded.php',
        'Nowakowskir\JWT\Base64Url' => 'vendor/php-jwt/src/Base64Url.php',
        'Nowakowskir\JWT\Validation' => 'vendor/php-jwt/src/Validation.php',
        'Nowakowskir\JWT\Exceptions\InvalidStructureException' => 'vendor/php-jwt/src/Exceptions/InvalidStructureException.php',
        'Nowakowskir\JWT\Exceptions\EmptyTokenException' => 'vendor/php-jwt/src/Exceptions/EmptyTokenException.php',
        'Nowakowskir\JWT\Exceptions\AlgorithmMismatchException' => 'vendor/php-jwt/src/Exceptions/AlgorithmMismatchException.php',
        'Nowakowskir\JWT\Exceptions\InsecureTokenException' => 'vendor/php-jwt/src/Exceptions/InsecureTokenException.php',
        'Nowakowskir\JWT\Exceptions\IntegrityViolationException' => 'vendor/php-jwt/src/Exceptions/IntegrityViolationException.php',
        'Nowakowskir\JWT\Exceptions\InvalidClaimTypeException' => 'vendor/php-jwt/src/Exceptions/InvalidClaimTypeException.php',
        'Nowakowskir\JWT\Exceptions\SigningFailedException' => 'vendor/php-jwt/src/Exceptions/SigningFailedException.php',
        'Nowakowskir\JWT\Exceptions\TokenExpiredException' => 'vendor/php-jwt/src/Exceptions/TokenExpiredException.php',
        'Nowakowskir\JWT\Exceptions\TokenInactiveException' => 'vendor/php-jwt/src/Exceptions/TokenInactiveException.php',
        'Nowakowskir\JWT\Exceptions\UndefinedAlgorithmException' => 'vendor/php-jwt/src/Exceptions/UndefinedAlgorithmException.php',
        'Nowakowskir\JWT\Exceptions\UnsupportedAlgorithmException' => 'vendor/php-jwt/src/Exceptions/UnsupportedAlgorithmException.php',
        'Nowakowskir\JWT\Exceptions\UnsupportedTokenTypeException' => 'vendor/php-jwt/src/Exceptions/UnsupportedTokenTypeException.php',
    )
);