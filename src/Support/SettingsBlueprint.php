<?php

namespace PressCrew\Adapter\Support;

use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;

/** Blueprint della pagina impostazioni: costruito a runtime con le collezioni e i campi reali del sito. */
class SettingsBlueprint
{
    public static function build(): array
    {
        $config = config('presscrew-adapter');
        $saved = Options::saved();
        $route = Options::get('route');

        // Senza un token salvato né nel .env ne proponiamo uno nuovo: diventa valido quando si preme Salva.
        $hasToken = ! empty($saved['token']) || ! empty($config['token']);

        return ['tabs' => [
            'connection' => ['display' => 'Connessione', 'sections' => [[
                'fields' => [
                    ['handle' => 'discount', 'field' => [
                        'type' => 'html',
                        'display' => 'PressCrew',
                        'html' => '<p>Non hai ancora un account? Con il codice <strong><code>PCSTATAMIC10</code></strong> ottieni il <strong>10% di sconto</strong> su PressCrew, la redazione AI che scrive nel tuo stile e pubblica su Statamic. '
                            .'<a href="https://presscrew.it" target="_blank" rel="noopener"><strong><u>Scopri PressCrew →</u></strong></a></p>',
                    ]],
                    ['handle' => 'info', 'field' => [
                        'type' => 'html',
                        'display' => 'Come collegare PressCrew',
                        'html' => '<ol><li>In PressCrew aggiungi una destinazione di tipo <strong>Statamic</strong>.</li>'
                            .'<li>URL del sito: <code>'.e(url('/')).'</code></li>'
                            .'<li>Rotta: <code>'.e($route).'</code></li>'
                            .'<li>Token: copia quello qui sotto <strong>dopo aver premuto Salva</strong>.</li></ol>',
                    ]],
                    ['handle' => 'enabled', 'field' => [
                        'type' => 'toggle',
                        'display' => 'Adapter attivo',
                        'instructions' => 'Se disattivato, il sito non riceve più articoli da PressCrew.',
                        'default' => true,
                    ]],
                    ['handle' => 'token', 'field' => [
                        'type' => 'text',
                        'display' => 'Token di accesso',
                        'instructions' => $hasToken
                            ? 'Se vuoto si usa il token del file .env. Per cambiarlo, scrivi un valore lungo e casuale e aggiorna anche PressCrew.'
                            : 'Generato per te: premi Salva per attivarlo, poi incollalo in PressCrew.',
                        'default' => $hasToken ? null : bin2hex(random_bytes(24)),
                        'validate' => ['min:24'],
                    ]],
                ],
            ]]],
            'collections' => ['display' => 'Collezioni e campi', 'sections' => [
                [
                    'display' => 'Dove pubblica PressCrew',
                    'instructions' => 'Scegli le collezioni e, per ciascuna, i campi che l\'AI di PressCrew può compilare (titolo, testo ed estratto sono sempre inviati). Senza scelte si usa la collezione «'.$config['collection'].'» senza campi aggiuntivi.',
                    'fields' => self::collectionFields(),
                ],
            ]],
            'advanced' => ['display' => 'Avanzate', 'sections' => [[
                'fields' => [
                    ['handle' => 'route', 'field' => [
                        'type' => 'text',
                        'display' => 'Rotta',
                        'instructions' => 'Indirizzo che riceve gli articoli. Se le rotte sono in cache esegui `php artisan route:clear` dopo averla cambiata.',
                        'default' => $config['route'],
                        'validate' => ['required'],
                    ]],
                    ['handle' => 'throttle', 'field' => [
                        'type' => 'text',
                        'display' => 'Limite di richieste',
                        'instructions' => 'Formato «richieste,minuti», ad esempio 30,1.',
                        'default' => $config['throttle'],
                    ]],
                    ['handle' => 'timezone', 'field' => [
                        'type' => 'text',
                        'display' => 'Fuso orario',
                        'instructions' => 'Per la data degli articoli senza data, ad esempio Europe/Rome.',
                        'default' => $config['timezone'],
                    ]],
                ],
            ]]],
        ]];
    }

    private static function collectionFields(): array
    {
        $collections = Collection::all();

        $fields = [['handle' => 'collections', 'field' => [
            'type' => 'checkboxes',
            'display' => 'Collezioni',
            'options' => $collections->mapWithKeys(fn ($c) => [$c->handle() => $c->title()])->all(),
        ]]];

        foreach ($collections as $collection) {
            $handle = $collection->handle();
            $blueprints = $collection->entryBlueprints();
            $when = ['collections' => 'contains '.$handle];

            if ($blueprints->count() > 1) {
                $fields[] = ['handle' => Options::key('blueprint', $handle), 'field' => [
                    'type' => 'select',
                    'display' => 'Blueprint di «'.$collection->title().'»',
                    'options' => $blueprints->mapWithKeys(fn (Blueprint $b) => [$b->handle() => $b->title()])->all(),
                    'default' => $blueprints->first()->handle(),
                    'if' => $when,
                ]];
            }

            $options = $blueprints
                ->flatMap(fn (Blueprint $b) => $b->fields()->all()->all())
                ->filter(fn (Field $f) => in_array($f->type(), Targets::TYPES, true) && ! in_array($f->handle(), Targets::RESERVED, true))
                ->mapWithKeys(fn (Field $f) => [$f->handle() => $f->display().' ('.$f->handle().')']);

            $fields[] = ['handle' => Options::key('fields', $handle), 'field' => [
                'type' => 'checkboxes',
                'display' => 'Campi compilati dall\'AI in «'.$collection->title().'»',
                'instructions' => $options->isEmpty() ? 'Nessun campo compatibile: sono supportati testo, area di testo, markdown, scelte, numero e interruttore.' : null,
                'options' => $options->all(),
                'if' => $when,
            ]];
        }

        return $fields;
    }
}
