<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documentación de la API</title>
    <link rel="stylesheet" href="https://unpkg.com">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com"></script>
    <script>
        window.onload = () => {
            window.ui = SwaggerUIBundle({
                url: '/docs/openapi.json', // Apunta directamente al archivo estático
                dom_id: '#swagger-ui',
            });
        };
    </script>
</body>
</html>
