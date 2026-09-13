<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components;

use Yii;
use yii\base\InvalidConfigException;

class GraphqlParser
{
    private array $includes = [];

    public function load(string $name): string
    {
        $file = Yii::getAlias("@shopify/../resources/graphql/$name.graphql");
        $content = is_file($file) ? file_get_contents($file) : false;

        if ($content === false) {
            throw new InvalidConfigException("The GraphQL document \"$name\" was not found.");
        }

        return $this->parse($content);
    }

    protected function parse(string $content): string
    {
        if (preg_match_all('/\.{3}([A-Z][a-zA-Z]*)/', $content, $matches)) {
            foreach ($matches[1] as $match) {
                if (!in_array($match, $this->includes, true)) {
                    // Marked before the recursive load, or two fragments spreading each other never terminate.
                    $this->includes[] = $match;
                    $content .= PHP_EOL . $this->load($match);
                }
            }
        }

        return $content;
    }
}
