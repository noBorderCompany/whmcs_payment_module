# noBorder Crypto Payment Gateway for WHMCS

## Table of Contents

- [Description](#description)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Contributing](#contributing)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Contact Information](#contact-information)
- [Changelog](#changelog)

---

## Description

The noBorder Crypto Payment Gateway for WHMCS is a plugin that allows WHMCS users to accept cryptocurrency payments via the noBorder platform.

- **Plugin Name:** noBorder Crypto Payment Gateway for WHMCS
- **Version:** 1.1
- **Author:** noBorder.tech
- **Author URI:** [https://noBorder.tech](https://noBorder.tech)
- **Author Email:** info@noBorder.tech
- **Text Domain:** noBorder_whmcs_payment_module
- **WC tested up to:** 8.8
- **Copyright (C):** 2020 noBorder
- **License:** [GPLv3 or later](http://www.gnu.org/licenses/gpl-3.0.html)

---

## Installation

After submitting your website on noBorder.tech and getting an API key, to install the noBorder Crypto Payment Gateway for WHMCS, follow these steps:

1. Copy the `noBorder.php` file to the `modules/gateways/` directory of your WHMCS installation.

2. Log in to your WHMCS admin panel.

3. Go to the "Setup" menu and select "Payment Gateways."

4. Under the "All Payment Gateways" tab, you should see "noBorder" in the list of gateways. Click on it to configure the settings.

5. Configure the gateway settings as described in the [Configuration](#configuration) section below.

6. Once configured, save your settings.

---

## Configuration

To configure the noBorder Crypto Payment Gateway for WHMCS, follow these steps:

1. Log in to your WHMCS admin panel.

2. Go to the "Setup" menu and select "Payment Gateways."

3. Under the "All Payment Gateways" tab, select "noBorder" from the list of gateways.

4. Configure the following settings:

   - **API Key:** Enter your noBorder API key. You can obtain this key by visiting [https://noBorder.tech/cryptosite](https://noBorder.tech/cryptosite).

   - **Pay Currency:** Specify the currencies available for payment. You can list multiple currencies separated by a dash (e.g., bitcoin-dogecoin-ethereum).

   - **Success Message:** Customize the message to display to customers after a successful payment. You can use placeholders like `{invoice_id}` and `{request_id}` to display the invoice and request IDs.

   - **Failed Message:** Customize the message to display to customers after a failed payment. You can use placeholders like `{invoice_id}` and `{request_id}` to display the invoice and request IDs.

5. Save your settings.

---

## Usage

Once the noBorder Crypto Payment Gateway is configured, customers can use it to make payments for their invoices. Here's how it works:

1. Customers can view and select the noBorder payment option when paying their invoices in the WHMCS client area.

2. Upon selecting noBorder as the payment method, customers will be redirected to complete the payment on the noBorder platform.

3. After making the payment on noBorder, customers will be redirected back to WHMCS.

4. WHMCS will update the invoice status based on the payment result. If the payment is successful, the invoice status will change to "Paid."

---

## Contributing

If you want to contribute to this project or report issues, please visit the GitHub repository: [noBorder Crypto Payment Gateway for WHMCS](https://github.com/noBorderTech/whmcs_payment_module).

---

## Troubleshooting

If you encounter any issues or have questions about the noBorder Crypto Payment Gateway for WooCommerce, please refer to the [official documentation](https://noBorder.tech) or contact our support team at [info@noBorder.tech](mailto:info@noBorder.tech).

---

## License

This project is licensed under the GPLv3 or later. See the [GNU General Public License](http://www.gnu.org/licenses/gpl-3.0.html) for more details.

---

## Contact Information

If you have any questions or need assistance, please contact us at [info@noBorder.tech](mailto:info@noBorder.tech).

---

## Changelog

- 1.1, feb 18, 2023
Bugs fixed

- 1.0, jan 15, 2023
First release.

---

Thank you for using the noBorder Crypto Payment Gateway for WooCommerce! We appreciate your business.

---

*Copyright (C) 2020 noBorder*
