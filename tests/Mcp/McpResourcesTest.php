<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Mcp;

use Hirtz\Shopify\Test\TestCase;
use Hirtz\Skeleton\Mcp\Mcp;
use Hirtz\Skeleton\Mcp\Tools\UpdateRecordTool;

class McpResourcesTest extends TestCase
{
    public function testProductsAreReadOnly(): void
    {
        $resource = Mcp::getComponent()->getResource('product');

        self::assertNotNull($resource);
        self::assertTrue($resource->isReadOnly());
        self::assertNotContains('product', array_map(
            fn ($resource): string => $resource->getName(),
            (new UpdateRecordTool(Mcp::getComponent()))->getResources(),
        ));
    }
}
