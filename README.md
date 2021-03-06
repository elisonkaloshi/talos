# talos

A package that generates requests based on the columns types of a table.

## Install on laravel project

`composer require elison/talos`

### Add the provider in the app.php

`config/app.php`

`\Elison\Talos\TalosProvider::class,`

## Requirements

`laravel project`

`composer v2`

`doctrine/dbal`

## Usage

`php artisan migrate` -> create a table that comes by default with the package

`php artisan talos:generate-request table_name request_name` -> command to generate request


## Command example
`php artisan talos:generate-request examples TestRequest`

## Example request that will be generated by the package

```
public function rules()
    {
        return [ 
            'created_at' => ['date','nullable'],
            'description' => ['string','nullable'],
            'title' => ['string','required'],
            'updated_at' => ['date','nullable'],
            'value' => ['integer','required'],
        ];
    }
```
 

