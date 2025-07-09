<?php

namespace davidhirtz\yii2\shopify\components;

use Yii;

class GraphqlParser
{
    private array $includes = [];

    public function load(string $name): string
    {
        $file = Yii::getAlias("@shopify/components/graphql/$name.graphql");
        $content = file_get_contents($file);

        return $this->parse($content);
    }

    protected function parse(string $content): string
    {
        if (preg_match_all('/\.{3}([A-Z][a-zA-Z]*)/', $content, $matches)) {
            foreach ($matches[1] as $match) {
                if (!in_array($match, $this->includes)) {
                    $content .= PHP_EOL . $this->load($match);
                    $this->includes[] = $match;
                }
            }
        }

        return $content;
    }
}
