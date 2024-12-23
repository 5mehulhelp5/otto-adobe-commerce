M2E OTTO Connect is a trusted Adobe Commerce (Magento) extension that enables businesses to fully integrate their Magento-based systems with the otto.de marketplace.

The extension makes it easy to upload your Magento inventory to OTTO. It provides automatic inventory and order synchronization, keeping your product and order information up-to-date both on Magento and OTTO.

Installation

1. Install Composer Installer.

2. Provide the Composer Installer as a dependence on the composer.json file of your project. Use the command:

composer require m2e/otto-adobe-commerce

3. To complete the installation, run the commands:

php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

Setup

1. Once M2E OTTO Connect is installed, you’ll see an additional tab in your Adobe Commerce (Magento) admin panel.

2. Click on the tab and connect your OTTO account.

3. Follow the short step-by-step wizard to provide the general settings.

Check out our [user documentation](https://docs-m2.m2epro.com/docs-category/otto-magento-integration-en/) for more information about the extension.