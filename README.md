# Cianbox SDK Api Integration Library

* [Install](#install)
* [Specific methods](#specific-methods)

<a name="install"></a>
## Install

### With Composer

From command line

```
composer require cianbox/sdk:0.1.0
```

As a dependency in your project's composer.json

```json
{
    "require": {
        "cianbox/sdk": "0.1.0"
    }
}
```

### By downloading

1. Clone/download this repository
2. Copy `lib` folder to your project's desired folder.

<a name="specific-methods"></a>
## Specific methods

### Return Values
Api returns all values as **array**

### Configure your credentials

* Get your **CUENTA**, **USUARIO** and **CONTRASEÑA** from Cianbox

```php
require_once ('lib/cianbox.php');

try {
    $cb = new CianboxApi('CUENTA', 'USUARIO', 'CONTRASEÑA');
} catch (Exception $e) {
    echo $e->getMessage() . ' ' . $e->getCode();
}
```

### Using valid refresh token

```php
try {
    $cb = new CianboxApi('CUENTA', 'USUARIO', 'CONTRASEÑA');
    $cb->post_auth_refresh($refreshToken = null);
} catch (Exception $e) {
    echo $e->getMessage() . ' ' . $e->getCode();
}
```

### Methods

#### Get Clientes Lista

https://github.com/cianbox/api-docs/blob/master/get_clientes_lista.md

```php
$params = array(
    "numero_documento" => '11111111111,99999999',
    "limit" => 1,
);

$clientes = $cb->get_clientes_lista($params);

print_r($clientes);
```

#### Get Estados De Pedidos

https://github.com/cianbox/api-docs/blob/master/get_estados_pedidos_lista.md

```php
$estadosDePedidos = $cb->get_estados_pedidos_lista();

print_r($estadosDePedidos);
```

#### Get Productos Lista

https://github.com/cianbox/api-docs/blob/master/get_productos_lista.md

```php
$params = array(
    "codigo_interno" => 'AD0053,ZKX1957',
    "limit" => 10,
);

$productos = $cb->get_productos_lista($params);

print_r($productos);
```

#### Get Sucursales

```php
$sucursales = $cb->get_sucursales();

print_r($sucursales);
```

#### Post Pedidos Alta

https://github.com/cianbox/api-docs/blob/master/post_pedidos_alta.md

```php
$pedido = array(
    "fecha" => date('Y-m-d'),
    "id_canal" => 14,
    "id_cuenta" => 1,
    "id_cliente" => 45,
    "id_usuario" => 12,
    "id_sucursal" => 1,
    "id_estado" => 1,
    "observaciones" => 'Pedido web: ' . $order_number, // Should be created by your eCommerce platform
    "productos" => array(
        array(
            "id" => 67,
            "id_lista_precio" => 0,
            "cantidad" => 1.0000,
            "alicuota" => 21.0000,
            "neto_uni" => 974.2500,
        ),
        array(
            "id" => 256,
            "id_lista_precio" => 2,
            "cantidad" => 2.0000,
            "alicuota" => 21.0000,
            "neto_uni" => 419.0000,
        ),
        array(
            "id" => 0,                     // If id equals 0, detalle field is mandatory
            "detalle" => "Costo de Envío", // Optional unless id equals 0
            "id_lista_precio" => 0,
            "cantidad" => 1.0000,
            "alicuota" => 21.0000,
            "neto_uni" => 246.0000,
        )
    ),
);

$result = $cb->post_pedidos_alta($pedido);

print_r($result);
