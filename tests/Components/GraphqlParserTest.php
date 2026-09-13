<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Components;

use Hirtz\Shopify\Components\GraphqlParser;
use Hirtz\Shopify\Test\TestCase;
use yii\base\InvalidConfigException;

/**
 * A query is assembled from the fragments it spreads, so every `...Fragment` has to end up in the document the
 * API is sent.
 */
class GraphqlParserTest extends TestCase
{
    public function testAQueryCarriesEveryFragmentItSpreads(): void
    {
        $document = (new GraphqlParser())->load('ProductQuery');

        self::assertStringContainsString('...ProductFields', $document);
        self::assertStringContainsString('fragment ProductFields on Product', $document);

        // Spread by `ProductFields`, so it is only there if the parser follows a fragment of a fragment.
        self::assertStringContainsString('fragment MediaFields on Media', $document);
        self::assertStringContainsString('fragment ProductVariantFields on ProductVariant', $document);
    }

    public function testAFragmentIsIncludedOnlyOnce(): void
    {
        $document = (new GraphqlParser())->load('ProductQuery');

        self::assertSame(1, substr_count($document, 'fragment MediaFields on Media'));
    }

    public function testADocumentThatIsNotThereIsReported(): void
    {
        $this->expectException(InvalidConfigException::class);
        (new GraphqlParser())->load('NotAQuery');
    }
}
