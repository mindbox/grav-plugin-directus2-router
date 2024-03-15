# Directus Router Plugin

The **Directus Router** Plugin is an extension for [Grav CMS](http://github.com/getgrav/grav). redirects expired urls to new routes configured in directus

This extension reads routing data from a directus backend and reroutes deprecated urls and requres the [directus2 plugin](https://github.com/mindbox/grav-plugin-directus2).

## Configuration

Before configuring this plugin, you should copy the `user/plugins/directus2-router/directus-router.yaml` to `user/config/plugins/directus2-router.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
track_unknown: false
mapping:
  table: routing_table
  request_field: field_for_deprecated_url
  target_field: field_for_target_site
  status_field: field_for_status_code
  page_instance_field: optional_field_for_instancing
additionalFilters:
  some_field.id:
    operator: _eq
    value: 1
```

| Parameter | Description |
| --- | --- |
| track_unknown | add unknown routes that not match existing pages or routes as drafts (data graveyard ahead!) |
| table | the table name of the routing data |
| request_field | the route of the deprecated url |
| target_field | the new url for the redirect |
| status_field | the status code field. (example value is 301 for permanently moved) |
| page_instance_field | can be used if you run multiple frontend (e.g. for subsidaries) |
| additionalFilters | here you can specify some more filter options. The syntax is the same as in the directus plugin |

Note that if you use the Admin Plugin, a file with your configuration named directus-router.yaml will be saved in the `user/config/plugins/`-folder once the configuration is saved in the Admin.


## Installation

### Installation as dependency (skeleton)

To install the plugin automaticall with `bin/grav install`, add the following to the git section of your `user/.dependecies` file:

```
git:
    directus2:
        url: https://github.com/mindbox/grav-plugin-directus2-router
        path: user/plugins/directus2-router
        branch: main
```

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `directus-router`. You can find these files on [GitHub](https://github.com/mindbox/grav-plugin-directus2-router).

You should now have all the plugin files under

    /your/site/grav/user/plugins/directus2-router

> NOTE: This plugin is a modular component for Grav which may require other plugins to operate, please see its [blueprints.yaml-file on GitHub](https://github.com//grav-plugin-directus-router/blob/master/blueprints.yaml).

## Usage

If this plugin is correctly configured, it will work out of the box.


