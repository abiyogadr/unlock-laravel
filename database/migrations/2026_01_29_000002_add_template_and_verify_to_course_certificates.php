<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // No-op: template_id column and certificate_templates table intentionally removed.
    }

    public function down()
    {
        // No-op
    }
};
