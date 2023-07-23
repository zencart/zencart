# Api Query Builder #

[![GitHub](https://img.shields.io/github/license/mashape/apistatus.svg)](https://github.com/laravel-restive/restive/blob/master/LICENSE)

## Introduction ##

Api Query Builder allows for complex filtering, sorting via an api endpoint.

It is intended to be part of a larger package that also provides full CRUD abilities for a Laravel API.

Full documentation is available [here](https://laravel-restive.github.io)

but see examples below for an idea of what the package provides.

## Installation ##

There is no current release so to install you will need to do 

``composer require laravel-restive/restive dev-master``

## Filtering ##

Examples 

    where[]=id:eq:1
    
    whereIn[]=id:(1,2,3)
    
    whereBetween[]=age:18,45
    
    
   
## Sorting ##

    sort[]=id,-name

would sort ascending on id, the sort descending on name

## Columns ##

By default queries will return all columns 

You can restrict columns using 

    columns[]=id,name
    
## Lots More ##

Lots more filtering options are available.
see the [Documentation](https://laravel-restive.github.io)    