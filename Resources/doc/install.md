Getting started with WizadSettingsBundle
========================================

Settings system for Symfony2 applications, editable via web user interface, injected in service container.

## Prerequisites

This version of the bundle requires Symfony 2.3+.

The Redis database server is required to store all the parameters.

## Installation

### Step 1: Download WizadSettingsBundle using composer

Add WizadSettingsBundle in your composer.json:

```js
{
    "require": {
        "wizad/settings-bundle": "dev-master"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update wizad/settings-bundle
```

Composer will install the bundle to your project's `vendor/wizad/settings-bundle` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Wizad\SettingsBundle\WizadSettingsBundle(),
    );
}
```

### Step 3: Configuration

Edit your application config file to provide connections informations to redis and to list the bundle wich contains configurable parameters.

```yaml
# app/config/config.yml
wizad_settings:
    redis:
        dsn: tcp://127.0.0.1:6379
        prefix: my.site.parameters
    bundles: [ ... ]
```

 * dns : the connection string to redis server
 * prefix : key prefix to isolate your data in the redis server
 * bundles : list of bundles that will contains configurable settings

### Step 4: Declaring configurable settings

In your bundle, create a file name settings.xml in the folder <bundle_dir>/Resources/config/.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<settings prefix="my_site">

    <parameter key="email.sender_name">
        <name>Email sender name</name>
        <default>Me</default>
    </parameter>

    <parameter key="email.sender_email">
        <name>Email sender address</name>
        <default>me@my-site.com</default>
    </parameter>

</settings>
```

### Step 5: Use the parameters

You can now use the parameters in you service container. For exemple the settings.xml file above will register the parameters:

```
%wizad_settings.dynamic.my_site.email.sender_name%
```

```
%wizad_settings.dynamic.my_site.email.sender_email%
```
