<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneIdempotencyCommand extends Command
{
    protected $signature = 'idempotency:prune';

    protected $description = 'Hapus kunci idempotensi yang sudah kedaluwarsa';

    public function handle(): int
    {
        if (! DB::getSchemaBuilder()->hasTable('idempotency_keys')) {
            return self::SUCCESS;
        }

        DB::table('idempotency_keys')->where('expires_at', '<', now())->delete();

        return self::SUCCESS;
    }
}
