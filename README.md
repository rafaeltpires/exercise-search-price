# ecisolutions

## Server side | Laravel
## To import file run

```php
php artisan import:csv
```
## Endpoint
* {{hostname}}/api/product-price

## Params
* prod_code - string
* prod_code[] - string 
* account_id - int

#### Example
```
127.0.0.1:8000/api/product-price?prod_code[]=BIOMBO&prod_code[]=VRJPOO&account_id=461
```

## Files:

### Command
* /server/app/Console/Commands/importCSV.php

### Models

* /server/app/Models/Account.php
* /server/app/Models/Price.php
* /server/app/Models/Product.php
* /server/app/Models/User.php

### Controller

* /server/app/Http/Controllers/ProductPrices.php