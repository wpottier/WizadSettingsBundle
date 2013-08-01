Access Settings from Twig
==========================

You can get all the dynamic settings from your template by adding the settings model service as a global Twig variable.

```yaml
twig:
    debug:            %kernel.debug%
    strict_variables: %kernel.debug%
    ...
    globals:
        wizad_settings: @wizad_settings.model.settings
```

Now if you want to retrieve the value of the settings name "email.sender_name" defined in the [Installation Guide](https://github.com/wpottier/WizadSettingsBundle/blob/master/Resources/doc/install.md), just use :

```
{{ wizad_settings['my_site.email.sender_name'] }}
```

For informations, the container parameters is named : %wizad_settings.dynamic.my_site.email.sender_name%