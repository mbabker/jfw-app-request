Joomla Framework Application Request Class

This is an experimental Request class object which would aide in the deprecation of all classes in and inheriting from the `Joomla\Input` namespace.

## Installation

### Composer

To include this package in a Composer application, you will need to manually register the repository to your `composer.json` as this repository is not on Packagist.

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/mbabker/jfw-app-request"
        }    
    ]
}
```

Then run `composer require mbabker/jfw-app-request:dev-master`
