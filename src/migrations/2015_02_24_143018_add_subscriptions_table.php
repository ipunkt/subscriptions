<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubscriptionsTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subscriptions', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('model_id');
			$table->string('model_class');
			$table->string('plan');
			$table->dateTime('trial_ends_at')->nullable();
			$table->dateTime('subscription_ends_at');
			$table->enum('state', ['active', 'canceled'])->default('active');
			$table->timestamps();

			$table->unique(['model_id', 'model_class', 'plan']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subscriptions');
	}
}