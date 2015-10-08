Esendex Magento Module
======================

## Installation

### Requirements
 - PHP >= 5.3.0
 

There are three ways to install this module, Connect, Composer or from an Archive file.

### Composer

Add the following to the require section in your `composer.json` file: 

```
{
    "require": {
        ...
        "esendex/sms": "dev-master", //replace with actual version
        ...
    }
}

```

Then run `composer update`. If you are using a Magento Composer Installer (eg [this](https://github.com/magento-hackathon/magento-composer-installer) one), your files should automatically be installed to your Magento installations root directory after the update has finished.

### Connect

Visit the Connect [page](...)

### Archive

Download the archive from [GitHub](https://github.com/esendex/esendex-magento-extension/archive/master.zip), extract the files and copy then to your Magento installations root directory


* * * 

Clear the cache, logout from the admin panel and then login again to complete the installation.


## Developer Documentation

A tutorial detailing how to add custom events to the Esendex SMS module can be found [here](docs/Building A Custom Module.md)

A further tutorial showing how to create advanced events (building on from the previous tutorial can be found [here](Adding Fields To Notification.md)