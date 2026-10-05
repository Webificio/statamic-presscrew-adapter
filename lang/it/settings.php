<?php

return [
    'tab_connection' => 'Connessione',
    'tab_collections' => 'Collezioni e campi',
    'tab_advanced' => 'Avanzate',

    'promo' => 'Non hai ancora un account? Con il codice <strong><code>:code</code></strong> ottieni il <strong>10% di sconto</strong> su PressCrew, la redazione AI che scrive nel tuo stile e pubblica su Statamic. :link',
    'promo_link' => 'Scopri PressCrew →',

    'how_label' => 'Come collegare PressCrew',
    'how' => '<ol><li>In PressCrew aggiungi una destinazione di tipo <strong>Statamic</strong>.</li><li>URL del sito: <code>:url</code></li><li>Rotta: <code>:route</code></li><li>Token: copia quello qui sotto <strong>dopo aver premuto Salva</strong>.</li></ol>',

    'enabled_label' => 'Adapter attivo',
    'enabled_instructions' => 'Se disattivato, il sito non riceve più articoli da PressCrew.',

    'token_label' => 'Token di accesso',
    'token_has' => 'Se vuoto si usa il token del file .env. Per cambiarlo, scrivi un valore lungo e casuale e aggiorna anche PressCrew.',
    'token_new' => 'Generato per te: premi Salva per attivarlo, poi incollalo in PressCrew.',

    'where_label' => 'Dove pubblica PressCrew',
    'where_instructions' => 'Scegli le collezioni e, per ciascuna, i campi che l\'AI di PressCrew può compilare (titolo, testo ed estratto sono sempre inviati). Senza scelte si usa la collezione «:collection» senza campi aggiuntivi.',
    'collections_label' => 'Collezioni',
    'blueprint_label' => 'Blueprint di «:collection»',
    'fields_label' => 'Campi compilati dall\'AI in «:collection»',
    'no_fields' => 'Nessun campo compatibile: sono supportati testo, area di testo, markdown, scelte, numero e interruttore.',

    'route_label' => 'Rotta',
    'route_instructions' => 'Indirizzo che riceve gli articoli. Se le rotte sono in cache esegui `php artisan route:clear` dopo averla cambiata.',
    'throttle_label' => 'Limite di richieste',
    'throttle_instructions' => 'Formato «richieste,minuti», ad esempio 30,1.',
    'timezone_label' => 'Fuso orario',
    'timezone_instructions' => 'Per la data degli articoli senza data, ad esempio Europe/Rome.',
    'asset_container_label' => 'Contenitore degli asset',
    'asset_container_instructions' => 'Libreria media in cui PressCrew cerca e carica le immagini, per handle (predefinito «assets»).',
    'asset_folder_label' => 'Cartella di caricamento',
    'asset_folder_instructions' => 'Cartella del contenitore che riceve le immagini caricate da PressCrew.',
];
