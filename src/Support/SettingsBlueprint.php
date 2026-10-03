<?php

namespace PressCrew\Adapter\Support;

use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;

/** Settings page blueprint, built at runtime from the site's real collections and fields. */
class SettingsBlueprint
{
    private static function t(string $key, array $replace = []): string
    {
        return __("statamic-adapter::settings.$key", $replace);
    }

    public static function build(): array
    {
        $config = config('presscrew-adapter');
        $saved = Options::saved();

        // Senza un token salvato né nel .env ne proponiamo uno nuovo: diventa valido quando si preme Salva.
        $hasToken = ! empty($saved['token']) || ! empty($config['token']);

        $logo = 'data:image/svg+xml;base64,'.base64_encode(file_get_contents(__DIR__.'/../../resources/svg/logo.svg'));
        $link = '<a href="https://presscrew.it" target="_blank" rel="noopener"><strong><u>'.self::t('promo_link').'</u></strong></a>';

        return ['tabs' => [
            'connection' => ['display' => self::t('tab_connection'), 'sections' => [[
                'fields' => [
                    ['handle' => 'discount', 'field' => [
                        'type' => 'html',
                        'display' => 'PressCrew',
                        'html' => '<a href="https://presscrew.it" target="_blank" rel="noopener"><img src="'.$logo.'" alt="PressCrew" width="200" height="52" style="margin-bottom:.75rem"></a>'
                            .'<p>'.self::t('promo', ['code' => 'PCSTATAMIC10', 'link' => $link]).'</p>',
                    ]],
                    ['handle' => 'info', 'field' => [
                        'type' => 'html',
                        'display' => self::t('how_label'),
                        'html' => self::t('how', ['url' => e(url('/')), 'route' => e(Options::get('route'))]),
                    ]],
                    ['handle' => 'enabled', 'field' => [
                        'type' => 'toggle',
                        'display' => self::t('enabled_label'),
                        'instructions' => self::t('enabled_instructions'),
                        'default' => true,
                    ]],
                    ['handle' => 'token', 'field' => [
                        'type' => 'text',
                        'display' => self::t('token_label'),
                        'instructions' => self::t($hasToken ? 'token_has' : 'token_new'),
                        'default' => $hasToken ? null : bin2hex(random_bytes(24)),
                        'validate' => ['min:24'],
                    ]],
                ],
            ]]],
            'collections' => ['display' => self::t('tab_collections'), 'sections' => [
                [
                    'display' => self::t('where_label'),
                    'instructions' => self::t('where_instructions', ['collection' => $config['collection']]),
                    'fields' => self::collectionFields(),
                ],
            ]],
            'advanced' => ['display' => self::t('tab_advanced'), 'sections' => [[
                'fields' => [
                    ['handle' => 'route', 'field' => [
                        'type' => 'text',
                        'display' => self::t('route_label'),
                        'instructions' => self::t('route_instructions'),
                        'default' => $config['route'],
                        'validate' => ['required'],
                    ]],
                    ['handle' => 'throttle', 'field' => [
                        'type' => 'text',
                        'display' => self::t('throttle_label'),
                        'instructions' => self::t('throttle_instructions'),
                        'default' => $config['throttle'],
                    ]],
                    ['handle' => 'timezone', 'field' => [
                        'type' => 'text',
                        'display' => self::t('timezone_label'),
                        'instructions' => self::t('timezone_instructions'),
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
            'display' => self::t('collections_label'),
            'options' => $collections->mapWithKeys(fn ($c) => [$c->handle() => $c->title()])->all(),
        ]]];

        foreach ($collections as $collection) {
            $handle = $collection->handle();
            $blueprints = $collection->entryBlueprints();
            $when = ['collections' => 'contains '.$handle];

            if ($blueprints->count() > 1) {
                $fields[] = ['handle' => Options::key('blueprint', $handle), 'field' => [
                    'type' => 'select',
                    'display' => self::t('blueprint_label', ['collection' => $collection->title()]),
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
                'display' => self::t('fields_label', ['collection' => $collection->title()]),
                'instructions' => $options->isEmpty() ? self::t('no_fields') : null,
                'options' => $options->all(),
                'if' => $when,
            ]];
        }

        return $fields;
    }
}
