<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Ollama Chat API",
 *     version="1.0.0"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class SwaggerController
{
    // Apenas para manter as anotações globais do Swagger.
}
