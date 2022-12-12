<?php

namespace Vigstudio\VgComment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class BaseModel extends Model
{
    use Traits\HasUuid;

    protected $config;

    public function __construct(array $attributes = [])
    {
        $this->config = Config::get('vgcomment');

        $this->setConnection($this->config['connection']);

        $this->setTable($this->config['table'][$this::TABLE]);

        parent::__construct($attributes);
    }
}
