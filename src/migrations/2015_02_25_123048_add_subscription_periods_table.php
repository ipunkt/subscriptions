<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubscriptionPeriodsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subscription_periods', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('subscription_id');
			$table->dateTime('start');
			$table->dateTime('end');
			$table->enum('state', ['unpaid', 'paid'])->default('unpaid');
			$table->string('invoice_reference');
			$table->decimal('invoice_sum');
			$table->dateTime('invoice_date');

			$table->foreign('subscription_id')->references('id')->on('subscriptions')->onUpdate('cascade')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subscription_periods');
	}

}