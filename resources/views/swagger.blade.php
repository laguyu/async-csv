<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación de la API</title>
    <!-- CSS oficial de Swagger optimizado -->
    <link rel="stylesheet" href="https://jsdelivr.net">
    <style>
        html { box-sizing: border-box; overflow: -merge-images; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin: 0; background: #fafafa; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>

    <!-- Scripts oficiales con soporte "crossorigin" para proxies y contenedores seguros -->
    <script src="https://jsdelivr.net" crossorigin></script>
    <script src="https://jsdelivr.net" crossorigin></script>

    <script>
        window.onload = () => {
            // El bundle cargará de forma transparente al usar jsDelivr
            window.ui = SwaggerUIBundle({
                url: 'https://hf.space',
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
