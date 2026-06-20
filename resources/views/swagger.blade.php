<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación de la API</title>
    <!-- CDN de Alta Disponibilidad de Cloudflare (Súper estable y rápido) -->
    <link rel="stylesheet" href="https://cloudflare.com">
    <style>
        html { box-sizing: border-box; overflow: -merge-images; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin: 0; background: #fafafa; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>

    <!-- Scripts oficiales de respaldo global -->
    <script src="https://cloudflare.com" crossorigin="anonymous"></script>
    <script src="https://cloudflare.com" crossorigin="anonymous"></script>

    <script>
        window.onload = () => {
            if (typeof SwaggerUIBundle === 'undefined') {
                document.getElementById('swagger-ui').innerHTML =
                    "<div style='padding:20px; text-align:center; font-family:sans-serif;'>" +
                    "<h3>⚠️ Error de conexión de red</h3>" +
                    "<p>No se pudieron cargar los scripts de Swagger desde el servidor global. Por favor, refresca la página.</p>" +
                    "</div>";
                return;
            }

            window.ui = SwaggerUIBundle({
                url: '/docs/openapi.json',
                dom_id: '#swagger-ui',
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: "BaseLayout",
                deepLinking: true
            });
        };
    </script>
</body>
</html>
