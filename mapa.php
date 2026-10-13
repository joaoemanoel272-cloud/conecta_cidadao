<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <title>Mapa - Minha Cidade</title>

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <style>

        #map {
            width: 100%;
            height: 600px;
        }

    </style>

</head>

<body>

    <h1>🗺️ Mapa da Minha Cidade</h1>

    <div id="map"></div>


    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
    </script>


    <script>

        const map = L.map("map")
            .setView(
                [-8.7619, -63.9039],
                12
            );


        L.tileLayer(
            "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            {
                maxZoom: 19,

                attribution:
                    "&copy; OpenStreetMap contributors"
            }
        ).addTo(map);


        L.marker([
            -8.7619,
            -63.9039
        ])
        .addTo(map)
        .bindPopup(
            "<b>📍 Minha Cidade</b><br>Porto Velho"
        )
        .openPopup();

    </script>

</body>

</html>