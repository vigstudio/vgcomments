<?php

namespace Vigstudio\VgComment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Rennokki\QueryCache\Traits\QueryCacheable;

class BaseModel extends Model
{
    use Traits\HasUuid;
    use QueryCacheable;

    protected $config;

    public $cacheFor = 3600;

    protected static $flushCacheOnUpdate = true;

    public function __construct(array $attributes = [])
    {
        $this->config = Config::get('vgcomment');

        $this->setConnection($this->config['connection']);

        $this->setTable($this->config['table'][$this::TABLE]);

        parent::__construct($attributes);
    }
}
