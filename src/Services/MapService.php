<?php

namespace App\Services;


class MapService
{
    public function generateMapScript(float $latitude, float $longitude, string $markerTitle): string
    {
    return "
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let map = L.map('map').setView([$latitude, $longitude], 13);

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