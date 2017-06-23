# API Library for 1881

A simple PHP library to communicate with 1881's API.

Uses JWT and Guzzle.

**This library is not associated with Digitale Medier or 1881 in any way.**

## Installation

```
composer require andersevenrud/1881
```

## Examples

Supports all API methods provided, example to look up person:

```php
use DM1881\DM1881;

$client = new DM1881([
    'username' => 'dummy',
    'client_id' => 'Dummy',
    'secret' => 'hash'
]);

var_dump($client->searchPerson('ola bull'));;
```

## Changelog

* **0.5.2** - Updated composer.json
* **0.5.1** - Updated documentation
* **0.5.0** - Initial release

## License

MIT
