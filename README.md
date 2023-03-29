# BitPay Kiosk Demo - PHP / Laravel

This is a demonstration Spring Boot app to show how BitPay can be used in the
context of a retail kiosk. It utilizes the the `pos` facade and with a simple
configuration file you can customize the `posData` fields that are sent to
BitPay. This app uses Eloquent to manage the database schema and by default
uses an embedded H2 database to make it easy to start.

## Functionality

- Create invoices
- View a grid of invoices
- View invoice details
- Store invoices in a database
- Receives instant payment notifications (IPN) to update the database
- Uses EventSource to update the frontend upon receiving IPN

## Prerequisites

- BitPay Account
- PHP 8.1

## Configuration

This app uses a YAML configuration file. To configure it, you'll need to either
copy `application-example.yaml` to the `application.yaml` and override specific YAML values.

### General Information

| YAML Key                                | Description                                             |
| --------------------------------------- | ------------------------------------------------------- |
| bitpay.design.hero.bgColor              | CSS color for hero background                           |
| bitpay.design.hero.title                | The title to show in the hero                           |
| bitpay.design.hero.body                 | The text to show under the title in the hero            |
| bitpay.design.logo                      | URL for the logo                                        |
| bitpay.design.posdata.fields            | See the `POS Data Fields` section below                 |
| bitpay.token                            | Your BitPay token                                       |
| bitpay.notificationEmail                | The email you want to use for notifications             |
| bitpay.environment                      | BitPay environment ( test / prod )                      |

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
- `php artisan migrate` to run DB migrations

## Testing

Unit tests:

Run `./vendor/bin/phpunit --testsuite=Unit` to run unit tests.

Unit & integration tests:

Create `application-test.yaml` with test configuration (based on application-example.yaml)
Run `./vendor/bin/phpunit` (they send real requests to the BitPay API so don't use them on prod env).
