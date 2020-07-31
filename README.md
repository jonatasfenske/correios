# Correios @JonatasFenske

## Installation

Correios is available via Composer:

```bash
"jonatasfenske/correios": "*"
```

or run

```bash
composer require jonatasfenske/correios
```


###### The Correios component is a set of classes, which uses the Correiso query APIs, to search for zip codes, calculate freight values ​​and track orders.

O componente Correios é um conjunto de classes, que utiliza as APIs de consulta aos Correiso, para buscar CEPs, calular valores de Fretes e Rastrear encomendas.


## Examples

###### For more details on how to use Correios, see the examples below.

Para mais detalhes sobre como usar o Correios, veja os exemplos abaixo.


## Get Address By ZipCode

```php

    $correios = new JonatasFenske\Correios\Correios;

    $zip = $correios->zipcode()->find('88010-020');

    var_dump($zip->getJsonResponse());

    {"zipcode":"88010-020","street":"Rua Deodoro","complement":[[]],"district":"Centro","city":"Florian\u00f3polis","uf":"SC"}


    var_dump($zip->getResponse());

    array (size=6)
      'zipcode' => string '88010-020' (length=9)
      'street' => string 'Rua Deodoro' (length=11)
      'complement' => 
        array (size=1)
          0 => 
            array (size=0)
              empty
      'district' => string 'Centro' (length=6)
      'city' => string 'Florianópolis' (length=14)
      'uf' => string 'SC' (length=2)
```


## Freight Calculate

```php
    $correios = new JonatasFenske\Correios\Correios;

    $freight = $correios->freight()
                ->origin('88010-020')
                ->destination('88106-815')
                ->services(JonatasFenske\Correios\Config::SERVICE_PAC, JonatasFenske\Correios\Config::SERVICE_SEDEX)
                ->item(11, 7, 16, .065, 4) // largura, altura, comprimento, peso e quantidade
                ->item(16, 16, 16, .060, 2) // largura, altura, comprimento, peso e quantidade
                ->calculate();

    var_dump($freight->getJsonResponse());

    [{"name":"PAC","code":"4510","price":21,"deadline":8},{"name":"Sedex","code":"4014","price":22.5,"deadline":4}]


    var_dump($freight->getResponse());

    array (size=2)
      0 => 
        array (size=4)
          'name' => string 'PAC' (length=3)
          'code' => string '4510' (length=4)
          'price' => float 21
          'deadline' => int 8
      1 => 
        array (size=4)
          'name' => string 'Sedex' (length=5)
          'code' => string '4014' (length=4)
          'price' => float 22.5
          'deadline' => int 4
```


## Tracking By Code

```php
    $correios = new JonatasFenske\Correios\Correios;

    $result = $correios->tracking()->find('OD222222222BR');

    var_dump($result->getJsonResponse());

    {"code":null,"last_timestamp":1586955060,"last_status":"Objeto entregue ao destinat\u00e1rio","last_date":"2020-04-15 09:51","last_locale":... (length=1098)


    var_dump($result->getResponse());

    array (size=8)
      'code' => null
      'last_timestamp' => int 1586955060
      'last_status' => string 'Objeto entregue ao destinatário' (length=32)
      'last_date' => string '2020-04-15 09:51' (length=16)
      'last_locale' => ....{Continue...}

```

## Contributing

Please see [CONTRIBUTING](https://github.com/jonatasfenske/correios/blob/master/CONTRIBUTING.md) for details.

## Support

###### Security: If you discover any security related issues, please email contato@jonatasfenske.com instead of using the issue tracker.

Se você descobrir algum problema relacionado à segurança, envie um e-mail para contato@jonatasfenske.com em vez de usar o rastreador de problemas.

Thank you

## Credits

- [Jônatas Fenske](https://github.com/jonatasfenske) (Developer)
- [All Contributors](https://github.com/jonatasfenske/correios/contributors) (This Rock)

## License

The MIT License (MIT). Please see [License File](https://github.com/jonatasfenske/correios/blob/master/LICENSE.md) for more information.