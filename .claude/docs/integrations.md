# Integrations

Runtime requirements: `msstc4php/vo-base ^1.1.1`, `ext-mbstring`, `symfony/polyfill-intl-normalizer`.
`composer-local.json` (gitignored) points a path repository at `../vo-base`.

## Bridges

| Need | How |
|---|---|
| Validation, request input, routes | The vo-base bridges work as is (bundle, Laravel, Yii 2, CodeIgniter 4, Spiral) |
| Storage | As is in Laravel, Yii 2, CodeIgniter 4 (string classes) and Cycle; Doctrine through `vo-documents-doctrine` (`vod_<cc>_<snake_name>` types) |
| Composite names (`Name\RU\PersonName`, `Name\AU\PersonName`) | The mapper must be `new ValueObjectMapper(Codecs::all())` |

How each bridge gets its mapper (the README shows the code):

- vo-base-laravel: `ValueObjectServiceProvider` uses `singletonIf`, so an app binding made earlier wins.
- vo-base-spiral: `ValueObjectBootloader::SINGLETONS`; rebind in a bootloader listed after it. Cycle's typecast takes
  the mapper from the same container, or an instance in `S::TYPECAST_HANDLER`.
- vo-base-yii2: `Yii::$container->get(ValueObjectMapper::class)`; define it as a singleton. The ActiveRecord behavior
  falls back to a codec-less default mapper unless given `mapper`.
- vo-base-bundle: `PayloadCodec` services are autoconfigured and tagged; register the two codec classes as services.
- vo-base-codeigniter4: built-in codecs only; composites of this package are not supported.

`vo-documents-doctrine` has a completeness test that fails when a class here lacks a type: add the type there in the
same change as a new class.
