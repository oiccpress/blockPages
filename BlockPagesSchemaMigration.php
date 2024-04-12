<?php

/**
 * @file StaticPagesSchemaMigration.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StaticPagesSchemaMigration
 *
 * @brief Describe database table structures.
 */

namespace APP\plugins\generic\blockPages;

use APP\core\Application;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BlockPagesSchemaMigration extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // List of static pages for each context
        Schema::create('block_pages', function (Blueprint $table) {
            $table->bigInteger('block_page_id')->autoIncrement();
            $table->string('path', 255);
            $table->bigInteger('context_id');
            $table->foreign('context_id', 'block_pages_context_id')->references(Application::getContextDAO()->primaryKeyColumn)->on(Application::getContextDAO()->tableName)->onDelete('cascade');
        });

        // Static Page settings.
        Schema::create('block_page_settings', function (Blueprint $table) {
            $table->bigIncrements('block_page_setting_id');
            $table->bigInteger('block_page_id');
            $table->foreign('block_page_id', 'block_page_settings_block_page_id')->references('block_page_id')->on('block_pages')->onDelete('cascade');
            $table->index(['block_page_id'], 'block_page_settings_block_page_id');

            $table->string('locale', 14)->default('');
            $table->string('setting_name', 255);
            $table->longText('setting_value')->nullable();
            $table->string('setting_type', 6)->comment('(bool|int|float|string|object)');
            $table->index(['block_page_id'], 'block_page_settings_block_page_id');
            $table->unique(['block_page_id', 'locale', 'setting_name'], 'block_page_settings_pkey');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::drop('block_page_settings');
        Schema::drop('block_pages');
    }
}
