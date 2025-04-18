<?php

namespace App\Services;

class AddressAutocompleteService
{
    /**
     * Génère le script d'autocomplétion d'adresse
     *
     * @param string $selector Le sélecteur CSS pour le champ d'autocomplétion
     * @param array $options Options supplémentaires (limit, minLength, etc.)
     * @return string Le script HTML/JavaScript à insérer dans la page
     */
    public function generateAutocompleteScript(string $selector = '.adresse-autocomplete', array $options = []): string
    {
        // Valeurs par défaut pour les options
        $limit = $options['limit'] ?? 5;
        $minLength = $options['minLength'] ?? 3;
        $apiUrl = $options['apiUrl'] ?? 'https://api-adresse.data.gouv.fr/search/';

        // Chemins vers les fichiers JS et CSS statiques
        $cssPath = '/css/address-autocomplete.css';
        $jsPath = '/js/address-autocomplete.js';

        return "
        
        <script src=\"{$jsPath}\"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initAddressAutocomplete({
                    selector: '{$selector}',
                    limit: {$limit},
                    minLength: {$minLength},
                    apiUrl: '{$apiUrl}'
                });
            });
        </script>
        <link rel=\"stylesheet\" href=\"{$cssPath}\">
        ";
    }
}