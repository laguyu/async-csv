<?php

return [
    /*
     * Define qué rutas de tu backend están expuestas a CORS.
     */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Permite peticiones desde cualquier origen (esencial para el iframe de Hugging Face)
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    // Permite los métodos POST y GET que usa tu sistema
    'allowed_methods' => ['*'],

    // Permite las cabeceras estándar de Content-Type y Accept de Swagger
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
