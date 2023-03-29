<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_payment', function (Blueprint $table) {
            $table->id();
            $table->float('amount_paid', 35, 25)->nullable();
            $table->float('display_amount_paid', 35, 25)->nullable();
            $table->float('underpaid_amount', 35, 25)->nullable();
            $table->float('overpaid_amount', 35, 25)->nullable();
            $table->boolean('non_pay_pro_payment_received')->nullable();
            $table->string('transaction_currency', 10)->nullable();
            $table->string('universal_codes_payment_string')->nullable();
            $table->string('universal_codes_verification_link')->nullable();
        });

        Schema::create('invoice_buyer_provided_info', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('selected_wallet')->nullable();
            $table->string('email_address')->nullable();
            $table->string('selected_transaction_currency', 10)->nullable();
            $table->string('sms')->nullable();
            $table->boolean('sms_verified')->nullable();
        });

        Schema::create('invoice_buyer', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_buyer_provided_info_id')->unsigned()->nullable(true);
            $table->string('name')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('notify')->nullable();
            $table->string('buyer_provided_email')->nullable();

            $table->foreign('invoice_buyer_provided_info_id', 'fk_inv_buyer_invoice_buyer_provided_info_id')
                ->references('id')
                ->on('invoice_buyer_provided_info')
                ->onUpdate('cascade')
                ->onDelete('cascade')
            ;
        });

        Schema::create('invoice_payment_currency_supported_transaction_currency', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->nullable();
            $table->string('reason')->nullable();
        });

        Schema::create('invoice_payment_currency_miner_fee', function (Blueprint $table) {
            $table->id();
            $table->integer('satoshis_per_byte')->nullable(true);
            $table->float('total_fee')->nullable(true);
            $table->float('fiat_amount')->nullable(true);
        });

        Schema::create('invoice_payment_currency', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_payment_id')->unsigned();
            $table->bigInteger('supported_transaction_currency_id')->unsigned()->nullable(true);
            $table->bigInteger('miner_fee_id')->unsigned()->nullable(true);
            $table->string('currency_code', 10)->nullable(false);
            $table->string('total')->nullable(false);
            $table->string('subtotal')->nullable(false);
            $table->string('display_total')->nullable(true);
            $table->string('display_subtotal')->nullable(true);

            $table->foreign('invoice_payment_id', 'fk_ipc_invoice_payment_id')
                ->references('id')
                ->on('invoice_payment')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('supported_transaction_currency_id', 'fk_ipc_inv_sup_transaction_currency_id')
                ->references('id')
                ->on('invoice_payment_currency_supported_transaction_currency')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('miner_fee_id', 'fk_ipc_miner_fee_id')
                ->references('id')
                ->on('invoice_payment_currency_miner_fee')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('invoice_payment_currency_exchange_rate', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_payment_currency_id')->unsigned();
            $table->string('currency_code')->nullable(false);
            $table->float('rate', 35, 25)->nullable(false);

            $table->foreign('invoice_payment_currency_id', 'fk_ipcer_invoice_payment_currency_id')
                ->references('id')
                ->on('invoice_payment_currency')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('invoice_payment_currency_code', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_payment_currency_id')->unsigned();
            $table->string('code')->nullable(false);
            $table->string('code_url')->nullable(false);

            $table->foreign('invoice_payment_currency_id', 'fk_ipcc_invoice_payment_currency_id')
                ->references('id')
                ->on('invoice_payment_currency')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('invoice_refund_info', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code')->nullable(false);
            $table->string('support_request')->nullable(false);
        });

        Schema::create('invoice_refund', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_refund_info_id')->unsigned()->nullable(true);
            $table->text('addresses_json')->nullable(true);
            $table->string('address_request_pending')->nullable(true);

            $table->foreign('invoice_refund_info_id', 'fk_invoice_refund_invoice_refund_info_id')
                ->references('id')
                ->on('invoice_refund_info')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('invoice_refund_info_amount', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_refund_info_id')->unsigned();
            $table->string('currency_code')->nullable(true);
            $table->float('amount')->nullable(true);

            $table->foreign('invoice_refund_info_id', 'fk_ifia_invoice_refund_info_id')
                ->references('id')
                ->on('invoice_refund_info')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_payment_id')->unsigned();
            $table->bigInteger('invoice_buyer_id')->unsigned();
            $table->bigInteger('invoice_refund_id')->unsigned()->nullable(true);
            $table->text('pos_data_json')->nullable(true);
            $table->float('price')->nullable(false);
            $table->string('currency_code')->nullable(false);
            $table->string('bitpay_id')->nullable(false);
            $table->string('status')->nullable(false);
            $table->dateTime('created_date')->nullable(true);
            $table->dateTime('expiration_time')->nullable(true);
            $table->string('bitpay_order_id')->nullable(false);
            $table->string('facade_type')->nullable(true);
            $table->string('bitpay_guid')->nullable(true);
            $table->string('exception_status')->nullable(true);
            $table->string('bitpay_url')->nullable(false);
            $table->string('redirect_url')->nullable(true);
            $table->string('close_url')->nullable(true);
            $table->integer('acceptance_window')->nullable(true);
            $table->string('token')->nullable(true);
            $table->string('merchant_name')->nullable(true);
            $table->text('item_description')->nullable(true);
            $table->string('bill_id')->nullable(true);
            $table->integer('target_confirmations')->nullable(true);
            $table->boolean('low_fee_detected')->nullable(true);
            $table->boolean('auto_redirect')->nullable(true);
            $table->string('shopper_user')->nullable(true);
            $table->boolean('json_pay_pro_required')->nullable(true);
            $table->boolean('bitpay_id_required')->nullable(true);
            $table->boolean('is_cancelled')->nullable(true);
            $table->string('transaction_speed')->nullable(true);
            $table->string('uuid')->nullable(false);

            $table->foreign('invoice_payment_id', 'fk_invoice_invoice_payment_id')
                ->references('id')
                ->on('invoice_payment')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('invoice_buyer_id', 'fk_invoice_invoice_buyer_id')
                ->references('id')
                ->on('invoice_buyer')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('invoice_refund_id', 'fk_invoice_invoice_refund_id')
                ->references('id')
                ->on('invoice_refund')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('invoice_itemized_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_id')->unsigned();
            $table->float('amount')->nullable(true);
            $table->string('description')->nullable(true);
            $table->boolean('is_fee')->nullable(true);

            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoice')
                ->onUpdate('cascade')
                ->onDelete('cascade')
            ;
        });

        Schema::create('invoice_transaction', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_id')->unsigned();
            $table->integer('amount')->nullable(true);
            $table->integer('confirmations')->nullable(true);
            $table->dateTime('received_time')->nullable(true);
            $table->string('txid')->nullable(true);

            $table->foreign('invoice_id', 'fk_invoice_transaction_invoice_id')
                ->references('id')
                ->on('invoice')
                ->onUpdate('cascade')
                ->onDelete('cascade')
            ;
        });

        Schema::create('invoice_transaction_ex_rate', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_transaction_id')->unsigned();
            $table->string('currency')->nullable(false);
            $table->float('amount', 30, 20)->nullable(false);

            $table->foreign('invoice_transaction_id', 'fk_inv_trans_ex_rate_invoice_trans_id')
                ->references('id')
                ->on('invoice_transaction')
                ->onUpdate('cascade')
                ->onDelete('cascade')
            ;
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoices_itemized_details');
        Schema::dropIfExists('invoice_transaction');
        Schema::dropIfExists('invoice_payment');
        Schema::dropIfExists('invoice_payment_currency');
        Schema::dropIfExists('invoice_payment_currency_exchange_rate');
        Schema::dropIfExists('invoice_payment_currency_code_supported_transaction_currency');
        Schema::dropIfExists('invoice_payment_currency_miner_fee');
        Schema::dropIfExists('invoice_buyer');
        Schema::dropIfExists('invoice_buyer_provided_info');
    }
};
