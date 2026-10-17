<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

final class WebPrincipal extends Authenticatable
{
    protected $table = 'web_principals';

    protected $guarded = ['*'];

    protected $hidden = ['password_hash'];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected function casts(): array
    {
        return ['disabled_at' => 'immutable_datetime', 'source_id' => 'integer', 'source_table_id' => 'integer', 'source_record_id' => 'integer'];
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getRememberTokenName(): string
    {
        return '';
    }
}
