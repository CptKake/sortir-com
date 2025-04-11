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

        return "
        <style>
            .address-suggestions {
                position: absolute;
                z-index: 1000;
                width: 100%;
                background-color: white;
                border: 1px solid #dbdbdb;
                border-radius: 4px;
                box-shadow: 0 2px 3px rgba(10, 10, 10, 0.1);
                max-height: 200px;
                overflow-y: auto;
            }
            .suggestion {
                padding: 10px;
                cursor: pointer;
                border-bottom: 1px solid #f5f5f5;
                transition: background-color 0.2s;
            }
            .suggestion:hover {
                background-color: #f5f5f5;
            }
            .suggestion.active {
                background-color: #f5f5f5;
            }
            
          
        @media (prefers-color-scheme: dark) {
            .address-suggestions {
                position: absolute;
                z-index: 1000;
                width: 100%;
                background-color: #333; /* Fond sombre */
                border: 1px solid #555; /* Bordure plus claire */
                border-radius: 4px;
                box-shadow: 0 2px 5px rgba(255, 255, 255, 0.2); /* Ombre claire */
                max-height: 200px;
                overflow-y: auto;
                color: #fff; /* Texte clair */
            }
            .suggestion {
                padding: 10px;
                cursor: pointer;
                border-bottom: 1px solid #444; /* Bordure plus claire */
                transition: background-color 0.2s;
            }
            .suggestion:hover {
                background-color: #444; /* Fond légèrement plus clair au survol */
            }
            .suggestion.active {
                background-color: #555; /* Fond légèrement plus clair quand actif */
            }
        }
   
        </style>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialiser l'autocomplétion
                initAutocomplete();
                
                function initAutocomplete() {
                    const searchFields = document.querySelectorAll('$selector');
                    if (searchFields.length === 0) {
                        console.warn('Aucun champ d\\'autocomplétion trouvé avec le sélecteur: $selector');
                        return;
                    }
                    
                    searchFields.forEach(field => {
                        field.addEventListener('input', debounce(function() {
                            const query = this.value;
                            if (query.length < $minLength) {
                                const existingSuggestions = document.querySelector('.address-suggestions');
                                if (existingSuggestions) {
                                    existingSuggestions.remove();
                                }
                                return;
                            }
                            
                            fetchAddresses(query, field);
                        }, 300));
                        
                        // Gestion des touches pour la navigation
                        field.addEventListener('keydown', function(e) {
                            handleKeyNavigation(e, field);
                        });
                    });
                }
                
                // Récupérer les adresses depuis l'API
                function fetchAddresses(query, field) {
                    fetch(`$apiUrl?q=\${encodeURIComponent(query)}&limit=$limit`)
                        .then(response => response.json())
                        .then(data => displaySuggestions(data, field))
                        .catch(error => console.error('Erreur lors de la récupération des adresses:', error));
                }
                
                // Afficher les suggestions
                function displaySuggestions(data, field) {
                    const existingSuggestions = document.querySelector('.address-suggestions');
                    if (existingSuggestions) {
                        existingSuggestions.remove();
                    }
                    
                    if (!data.features || data.features.length === 0) {
                        return;
                    }
                    
                    const suggestions = document.createElement('div');
                    suggestions.classList.add('address-suggestions');
                    
                    data.features.forEach((feature, index) => {
                        const suggestion = document.createElement('div');
                        suggestion.classList.add('suggestion');
                        if (index === 0) suggestion.classList.add('active');
                        
                        const address = feature.properties;
                        suggestion.innerHTML = `<span class=\"suggestion-text\">\${address.label}</span>`;
                        
                        suggestion.addEventListener('mouseover', function() {
                            this.style.backgroundColor = '#f5f5f5';
                        });
                        
                        suggestion.addEventListener('mouseout', function() {
                            this.style.backgroundColor = '';
                        });
                        
                        suggestion.addEventListener('click', function() {
                            fillAddressFields(field, feature);
                            suggestions.remove();
                        });
                        
                        suggestions.appendChild(suggestion);
                    });
                    
                    field.parentNode.style.position = 'relative';
                    field.parentNode.appendChild(suggestions);
                    
                    document.addEventListener('click', function(e) {
                        if (!field.contains(e.target) && !suggestions.contains(e.target)) {
                            suggestions.remove();
                        }
                    }, { once: true });
                }
                
                // Remplir les champs avec les données sélectionnées
                function fillAddressFields(field, feature) {
                    const adresseItem = field.closest('.adresse-item');
                    const address = feature.properties;
                    
                    const rueField = adresseItem.querySelector('[id$=\"_rue\"]');
                    const villeField = adresseItem.querySelector('[id$=\"_ville\"]');
                    const codePostalField = adresseItem.querySelector('[id$=\"_codePostal\"]');
                    const latitudeField = document.querySelector('[id$=\"_latitude\"]');
                    const longitudeField = document.querySelector('[id$=\"_longitude\"]');
                    
                    if (rueField) rueField.value = address.name;
                    if (codePostalField) codePostalField.value = address.postcode;
                    if (villeField) villeField.value = address.city;
                    
                    if (latitudeField && longitudeField && feature.geometry) {
                        longitudeField.value = feature.geometry.coordinates[0];
                        latitudeField.value = feature.geometry.coordinates[1];
                    }
                    
                    field.value = address.label;
                    
                    const parentDiv = adresseItem;
                    parentDiv.style.transition = 'background-color 0.5s';
                    parentDiv.style.backgroundColor = '#ebffeb';
                    setTimeout(() => {
                        parentDiv.style.backgroundColor = '';
                    }, 1000);
                }
                
                // Gérer la navigation au clavier
                function handleKeyNavigation(e, field) {
                    const suggestions = document.querySelector('.address-suggestions');
                    if (!suggestions) return;
                    
                    const active = suggestions.querySelector('.suggestion.active');
                    const items = suggestions.querySelectorAll('.suggestion');
                    
                    switch (e.key) {
                        case 'ArrowDown':
                            e.preventDefault();
                            if (active) {
                                active.classList.remove('active');
                                active.style.backgroundColor = '';
                                const next = active.nextElementSibling || items[0];
                                next.classList.add('active');
                                next.style.backgroundColor = '#f5f5f5';
                                next.scrollIntoView({ block: 'nearest' });
                            } else if (items.length) {
                                items[0].classList.add('active');
                                items[0].style.backgroundColor = '#f5f5f5';
                            }
                            break;
                            
                        case 'ArrowUp':
                            e.preventDefault();
                            if (active) {
                                active.classList.remove('active');
                                active.style.backgroundColor = '';
                                const prev = active.previousElementSibling || items[items.length - 1];
                                prev.classList.add('active');
                                prev.style.backgroundColor = '#f5f5f5';
                                prev.scrollIntoView({ block: 'nearest' });
                            } else if (items.length) {
                                items[items.length - 1].classList.add('active');
                                items[items.length - 1].style.backgroundColor = '#f5f5f5';
                            }
                            break;
                            
                        case 'Enter':
                            if (active) {
                                e.preventDefault();
                                active.click();
                            }
                            break;
                            
                        case 'Escape':
                            suggestions.remove();
                            break;
                    }
                }
                
                // Fonction debounce pour limiter les appels API
                function debounce(func, wait) {
                    let timeout;
                    return function(...args) {
                        const later = () => {
                            clearTimeout(timeout);
                            func.apply(this, args);
                        };
                        clearTimeout(timeout);
                        timeout = setTimeout(later, wait);
                    };
                }
            });
        </script>
        ";
    }
}