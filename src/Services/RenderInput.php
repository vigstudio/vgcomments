<?php

namespace Vigstudio\VgComment\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class RenderInput
{
    public const TEXT_TYPE = 'text';

    public const NUMBER_TYPE = 'number';

    public const SELECT_TYPE = 'select';

    public const BOOLEAN_TYPE = 'boolean';

    public const TEXTAREA_TYPE = 'textarea';

    protected string $type;

    protected string $key;

    protected array $data;

    public function __construct(string $type, array $data): void
    {
        $this->type = $type;
        $this->data = $data;
        $this->key = $data['key'];

        $value = Config::get('vgcomment.' . $this->key);

        if (is_array($value)) {
            $value = implode('\n', $value);
        }

        if ($type === self::BOOLEAN_TYPE || $type === self::SELECT_TYPE) {
            $this->data['selected'] = $value;
        } else {
            $this->data['value'] = $value;
        }

        if (! isset($this->data['label'])) {
            $this->data['label'] = trans('vgcomment::admin.' . $this->key . '_label');
        }

        if (! isset($this->data['help'])) {
            $this->data['help'] = trans('vgcomment::admin.' . $this->key . '_help');
        }
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function render(): HtmlString
    {
        return new HtmlString(
            View::make('vgcomment::components.form.' . $this->type, $this->data)
        );
    }

    public static function boolean(string $key, string $label = null, string|null $help = null): static
    {
        return new static(self::BOOLEAN_TYPE, compact('key', 'label', 'help'));
    }

    public static function text(string $key, string $label = null, string|null $help = null): static
    {
        return new static(self::TEXT_TYPE, compact('key', 'label', 'help'));
    }

    public static function textarea(string $key, string $label = null, string|null $help = null): static
    {
        return new static(self::TEXTAREA_TYPE, compact('key', 'label', 'help'));
    }

    public static function number(string $key, string $label = null, string|null $help = null): static
    {
        return new static(self::NUMBER_TYPE, compact('key', 'label', 'help'));
    }

    public static function select(string $key, array $options, string $label = null, string|null $help = null): static
    {
        $keys = array_keys($options);

        if (array_keys($keys) === $keys) {
            $temp = $options;
            $options = [];

            foreach ($temp as $option) {
                $options[$option] = $option;
            }
        }

        return new static(self::SELECT_TYPE, compact('key', 'label', 'help', 'options'));
    }
}
