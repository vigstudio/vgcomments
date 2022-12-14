<?php

namespace Vigstudio\VgComment\Support;

use Illuminate\Contracts\Cache\Repository as CacheInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Parser;
use s9e\TextFormatter\Renderer;
use s9e\TextFormatter\Unparser;
use Vigstudio\VgComment\Events\FormatterConfiguratorEvent;
use Vigstudio\VgComment\Repositories\ContractsInterface\CommentFormatterInterface;

class CommentFormatter implements CommentFormatterInterface
{
    protected $config;

    protected $cache;

    protected $configurator;

    protected $storage;

    protected $patchCacheDir = 'vgcomments/configurator';

    public function __construct(array $config, CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->config = $config;
        $this->configurator = new Configurator();
        $this->storage = Storage::disk('local');

        if ($this->storage->missing($this->patchCacheDir)) {
            $this->storage->makeDirectory($this->patchCacheDir);
        }
    }

    public function parse(string $text): string
    {
        return $this->getParser()->parse($text);
    }

    public function unparse(string $xml): string
    {
        return Unparser::unparse($xml);
    }

    public function render(string $xml): string
    {
        return $this->getRenderer()->render($xml);
    }

    public function flush(): void
    {
        File::cleanDirectory($this->storage->path($this->patchCacheDir));
        $this->cache->forget('vgcomments.formatter');
    }

    protected function getParser(): Parser
    {
        return $this->getComponent('parser');
    }

    protected function getRenderer(): Renderer
    {
        spl_autoload_register(function ($class) {
            $file = $this->storage->path("$this->patchCacheDir/$class.php");

            if (file_exists($file)) {
                include $file;
            }
        });

        return $this->getComponent('renderer');
    }

    protected function getComponent(string $key): mixed
    {
        $cacheKey = "vgcomments.formatter.$key";

        return $this->cache->rememberForever($cacheKey, function () use ($key) {
            return $this->getConfigurator()->finalize()[$key];
        });
    }

    protected function getConfigurator(): Configurator
    {
        $this->configurator->rootRules->enableAutoLineBreaks();
        $this->configurator->rendering->engine = 'PHP';
        $this->configurator->rendering->engine->cacheDir = $this->storage->path($this->patchCacheDir);
        $this->configurator->rendering->engine->className = 'VgComment_Render';

        //Auto email
        $this->configurator->Autoemail;

        //Auto image
        $this->configurator->Autoimage;

        //Auto link
        $urlTag = $this->configurator->tags->add('URL');
        $filterUrlTag = $this->configurator->attributeFilters->get('#url');
        $urlTag->attributes->add('url')->filterChain->append($filterUrlTag);
        $urlTag->template = '<a target="_blank" href="{@url}"><xsl:apply-templates/></a>';

        $this->configurator->Autolink;
        $this->configurator->Autolink->matchWww = true;
        $this->configurator->urlConfig->allowScheme('ftp');
        $this->configurator->urlConfig->allowScheme('irc');

        //Auto video
        $this->configurator->Autovideo;

        //Auto Escaper defines the backslash character \ as an escape character.
        $this->configurator->Escaper;

        //Auto Censor
        if ($this->config['censor']) {
            foreach ($this->config['censors_text'] as $censorWord) {
                $this->configurator->Censor->add($censorWord);
            }
        }

        //Media Embed
        $youtubeTag = $this->configurator->MediaEmbed->add('youtube');
        $youtubeTag->template = "<xsl:if test='@id'><a target='_blank' href='https://youtu.be/{@id}'>https://youtu.be/<xsl:value-of select='@id'/></a></xsl:if><div class='vgcomments__video'><div class='vgcomments-player' data-plyr-provider='youtube' data-plyr-embed-id='{@id}'></div></div>";

        // $this->configurator->MediaEmbed->add('facebook');

        // $this->configurator->MediaEmbed->add('tiktok');
        // $this->configurator->MediaEmbed->add('amazon');
        // $this->configurator->MediaEmbed->add('applepodcasts');
        // $this->configurator->MediaEmbed->add('youmaker');
        // $this->configurator->MediaEmbed->add('youku');
        // $this->configurator->MediaEmbed->add('instagram');
        // $this->configurator->MediaEmbed->add('mixcloud');
        // $this->configurator->MediaEmbed->add('ted');
        // $this->configurator->MediaEmbed->add('vevo');
        // $this->configurator->MediaEmbed->add('vimeo');
        // $this->configurator->MediaEmbed->add('vine');

        //Litedown
        $this->configurator->Litedown;
        $this->configurator->PipeTables;
        $this->configurator->TaskLists;

        //use Emoticons
        $this->addEmoticons($this->configurator);

        event(new FormatterConfiguratorEvent($this->configurator));

        return $this->configurator;
    }

    protected function addEmoticons(Configurator $configurator): void
    {
        $dataEmoticons = json_decode(file_get_contents(__DIR__ . '/../../emoji.json'));

        foreach ($dataEmoticons as $key => $value) {
            $configurator->Emoticons->add($key, $value);
        }
    }
}
