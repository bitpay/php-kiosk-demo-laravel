# BitPay Kiosk Demo - PHP / Laravel

This is a demonstration Laravel app to show how BitPay can be used in the
context of a retail kiosk. It utilizes the the `pos` facade and with a simple
configuration file you can customize the `posData` fields that are sent to
BitPay. This app uses Eloquent to manage the database schema and by default
uses an embedded H2 database to make it easy to start. Feel free to use other RDBMS like MySQL.

## Functionality

- Create invoices
- View a grid of invoices (`/invoices`)
- View invoice details (`/invoices/:invoiceId`)
- Store invoices in a database
- Receives instant payment notifications (IPN) to update the database
- Uses EventSource to update the frontend upon receiving IPN

## Prerequisites

- BitPay Account
- PHP 8.1

## Configuration

### Environment Variables

This app can use either a `.env` file or global environment variables. If you
would like to use a `.env` file, you will need to copy `.env.example` to `.env`
and update the values.

By default, this application uses SQLite for demonstration purposes, so you will
need to set an absolute path for `DB_DATABASE` in your environment.

Note that these are standard Laravel environment variables and may not apply
to all systems. See the [Laravel documentation](https://laravel.com/docs/10.x/#environment-based-configuration) for more information.

### YAML Configuration

This app uses a YAML configuration file. To configure it, copy
`application-example.yaml` to the `application.yaml` and override specific YAML
values.

### General Information

| YAML Key                     | Description                                                                                        |
| ---------------------------- | --------------------------------------------                                                       |
| bitpay.design.hero.bgColor   | CSS color for hero background                                                                      |
| bitpay.design.hero.title     | The title to show in the hero                                                                      |
| bitpay.design.hero.body      | The text to show under the title in the hero                                                       |
| bitpay.design.logo           | URL for the logo                                                                                   |
| bitpay.design.posdata.fields | See the `POS Data Fields` section below                                                            |
| bitpay.design.mode                             | Determines whether the app should be run in `standard` or `donation` mode        |
| bitpay.design.donation.denominations           | Available donations to choose. The highest value determined the maximum donation |
| bitpay.design.donation.enableOther             | Determines whether the app should allow to use own donation value.               |
| bitpay.design.donation.footerText              | The text to show in the footer                                                   |
| bitpay.design.donation.buttonSelectedBgColor   | CSS color for selected donation background                                       |
| bitpay.design.donation.buttonSelectedTextColor | CSS color for selected donation text                                             |
| bitpay.token                 | Your BitPay token                                                                                  |
| bitpay.notificationEmail     | The email you want to use for notifications                                                        |
| bitpay.environment           | BitPay environment ( test / prod )                                                                 |

### POS Data Fields

#### Dropdown (posData)

| YAML Key      | Description                                            |
| ------------- | ------------------------------------------------------ |
| type          | Set to "select"                                        |
| required      | Determines whether the field should be required or not |
| id            | Field identifier                                       |
| name          | Field name                                             |
| label         | Field label                                            |
| options.id    | (options array) ID for a given selection               |
| options.label | (options array) Label for a given selection            |
| options.value | (options array) Value for a given selection            |

#### Fieldset (posData)

| YAML Key      | Description                                            |
| ------------- | ------------------------------------------------------ |
| type          | Set to "fieldset"                                      |
| required      | Determines whether the field should be required or not |
| name          | Field name                                             |
| label         | Field label                                            |
| options.id    | (options array) ID for a given selection               |
| options.label | (options array) Label for a given selection            |
| options.value | (options array) Value for a given selection            |

#### Text (posData)

| YAML Key | Description                                            |
| -------- | ------------------------------------------------------ |
| type     | Set to "text"                                          |
| required | Determines whether the field should be required or not |
| name     | Field name                                             |
| label    | Field label                                            |

#### Price

| YAML Key | Description                                            |
| -------- | ------------------------------------------------------ |
| type     | Set to "price"                                         |
| required | Determines whether the field should be required or not |
| name     | Field name                                             |
| label    | Field label                                            |
| currency | Currency for the field                                 |

## Running

- `composer install`
- `cp .env.example .env` and configure it
- `cp application-example.yaml application.yaml` and configure it
- `php artisan migrate` to run DB migrations (and create sqlite DB if you use this database)
- `php artisan key:generate` to generate an encryption key
- `php artisan serve` to run the application

## Testing

Unit tests:

Run `./vendor/bin/phpunit --testsuite=Unit` to run unit tests.

Integration tests:

Run `./vendor/bin/phpunit --testsuite=Integration` to run integration tests.

Functional tests:

Create `application-functional.yaml` with test configuration (based on application-example.yaml - they send real requests to the BitPay API so don't use them on prod env)
Run `./vendor/bin/phpunit --testsuite=Functional`.
