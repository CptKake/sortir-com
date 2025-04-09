<?php

namespace App\Services;


class MapService
{
    public function generateMapScript(float $latitude, float $longitude, string $markerTitle): string
    {
    return "

        <!-- Inclure les fichiers Leaflet -->
        <link rel=\"stylesheet\" href=\"https://unpkg.com/leaflet@1.9.4/dist/leaflet.css\"
              integrity=\"sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=\"
              crossorigin=\"\"/>
        <script src=\"https://unpkg.com/leaflet@1.9.4/dist/leaflet.js\"
                integrity=\"sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=\"
                crossorigin=\"\"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var map = L.map('map').setView([$latitude, $longitude], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors'
                }).addTo(map);

                L.marker([$latitude, $longitude]).addTo(map)
                    .bindPopup('$markerTitle').openPopup();
            });
        </script>
        ";
    }
}