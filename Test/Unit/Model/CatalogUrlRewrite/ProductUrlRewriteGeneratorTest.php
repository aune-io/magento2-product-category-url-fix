<?php

namespace Aune\ProductCategoryUrlFix\Test\Unit\Model\CatalogUrlRewrite;

use ReflectionClass;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\GetVisibleForStores;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Aune\ProductCategoryUrlFix\Model\CatalogUrlRewrite\ProductUrlRewriteGenerator;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Aune\ProductCategoryUrlFix\Model\CatalogUrlRewrite\ProductUrlRewriteGenerator::class)]
class ProductUrlRewriteGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \\PHPUnit\Framework\MockObject\MockObject */
    protected $canonicalUrlRewriteGenerator;

    /** @var \\PHPUnit\Framework\MockObject\MockObject */
    protected $currentUrlRewritesRegenerator;

    /** @var \\PHPUnit\Framework\MockObject\MockObject */
    protected $categoriesUrlRewriteGenerator;

    /** @var \\PHPUnit\Framework\MockObject\MockObject */
    protected $anchorUrlRewriteGenerator;

    /** @var \Aune\ProductCategoryUrlFix\Model\CatalogUrlRewrite\ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\\PHPUnit\Framework\MockObject\MockObject */
    protected $storeViewService;

    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory|\\PHPUnit\Framework\MockObject\MockObject */
    protected $objectRegistryFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface|\\PHPUnit\Framework\MockObject\MockObject */
    protected $storeManager;
    
    /** @var \use Magento\Framework\App\Config\ScopeConfigInterface|\\PHPUnit\Framework\MockObject\MockObject */
    protected $scopeConfig;

    /** @var \\PHPUnit\Framework\MockObject\MockObject  */
    private $productScopeRewriteGenerator;

    /** @var GetVisibleForStores|\PHPUnit\Framework\MockObject\MockObject */
    private $visibleForStores;

    /**
     * Test method
     */
    protected function setUp(): void
    {
        $this->storeManager = $this->getMockBuilder(
                \Magento\Store\Model\StoreManagerInterface::class
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->currentUrlRewritesRegenerator = $this->getMockBuilder(
                \Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator::class
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->canonicalUrlRewriteGenerator = $this->getMockBuilder(
                \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator::class
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoriesUrlRewriteGenerator = $this->getMockBuilder(
                \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator::class
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->anchorUrlRewriteGenerator = $this->getMockBuilder(
                \Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator::class
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectRegistryFactory = $this->getMockBuilder(
                \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory::class
            )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->storeViewService = $this->getMockBuilder(
                \Magento\CatalogUrlRewrite\Service\V1\StoreViewService::class
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(
                ScopeConfigInterface::class
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->productScopeRewriteGenerator = $this->getMockBuilder(
                ProductScopeRewriteGenerator::class
            )
            ->disableOriginalConstructor()
            ->getMock();
        
        // GetVisibleForStores only exists in Magento >= 2.4.7. Core (and our override) fetch it from the
        // ObjectManager, so provide one that returns the mock.
        if (class_exists(GetVisibleForStores::class)) {
            $this->visibleForStores = $this->getMockBuilder(GetVisibleForStores::class)
                ->disableOriginalConstructor()
                ->getMock();

            $objectManager = $this->createStub(ObjectManagerInterface::class);
            $objectManager->method('get')->willReturnMap([[GetVisibleForStores::class, $this->visibleForStores]]);
            AppObjectManager::setInstance($objectManager);
        }

        $this->productUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            ProductUrlRewriteGenerator::class,
            [
                'canonicalUrlRewriteGenerator' => $this->canonicalUrlRewriteGenerator,
                'categoriesUrlRewriteGenerator' => $this->categoriesUrlRewriteGenerator,
                'currentUrlRewritesRegenerator' => $this->currentUrlRewritesRegenerator,
                'objectRegistryFactory' => $this->objectRegistryFactory,
                'storeViewService' => $this->storeViewService,
                'storeManager' => $this->storeManager,
                'scopeConfig' => $this->scopeConfig,
            ]
        );

        // Inject private properties to avoid ObjectManager errors
        $reflection = new ReflectionClass(get_class($this->productUrlRewriteGenerator));
        $reflectionProperty = $reflection->getProperty('productScopeRewriteGenerator');
        $reflectionProperty->setValue($this->productUrlRewriteGenerator, $this->productScopeRewriteGenerator);
    }

    protected function tearDown(): void
    {
        // Do not leak the mocked ObjectManager into other tests
        $instance = new ReflectionClass(AppObjectManager::class);
        foreach ($instance->getProperties(\ReflectionProperty::IS_STATIC) as $property) {
            if ($property->getName() === '_instance') {
                $property->setValue(null, null);
            }
        }
    }

    /**
     * Covers generate().
     *
     * @return void
     * 
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function testGenerateWithCategories()
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $storeId = 1;
        $urls = ['dummy-url.html'];

        $productMock->expects($this->once())
            ->method('getVisibility')
            ->willReturn(2);
        
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        
        $productCategoriesMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $selected = [];
        $productCategoriesMock->expects($this->exactly(2))
            ->method('addAttributeToSelect')
            ->willReturnCallback(function ($attribute) use (&$selected, $productCategoriesMock) {
                $selected[] = $attribute;
                return $productCategoriesMock;
            });
        
        $productMock->expects($this->once())
            ->method('getCategoryCollection')
            ->willReturn($productCategoriesMock);
        
        $this->productScopeRewriteGenerator->expects($this->once())
            ->method('generateForSpecificStoreView')
            ->with($storeId, $productCategoriesMock, $productMock)
            ->willReturn($urls);
        
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('catalog/seo/product_use_categories', 'store', 1)
            ->willReturn(true);
        
        $this->assertEquals($urls, $this->productUrlRewriteGenerator->generate($productMock, 1));
        $this->assertSame(['url_key', 'url_path'], $selected);
    }
    
    /**
     * Covers generate().
     *
     * @return void
     */
    public function testGenerateWithoutCategories()
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $storeId = 1;
        $urls = ['dummy-url.html'];

        $productMock->expects($this->once())
            ->method('getVisibility')
            ->willReturn(2);
        
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        
        $productMock->expects($this->never())
            ->method('getCategoryCollection');
        
        $this->productScopeRewriteGenerator->expects($this->once())
            ->method('generateForSpecificStoreView')
            ->with($storeId, [], $productMock)
            ->willReturn($urls);
        
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('catalog/seo/product_use_categories', 'store', 1)
            ->willReturn(false);
        
        $this->assertEquals($urls, $this->productUrlRewriteGenerator->generate($productMock, 1));
    }

    /**
     * Not visible individually and not visible in any store: no rewrites.
     *
     * @return void
     */
    public function testGenerateNotVisibleAnywhere()
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->method('getVisibility')->willReturn(1);

        // On Magento 2.4.6 core has no such service and returns nothing for these products as well
        if ($this->visibleForStores) {
            $this->visibleForStores->expects($this->once())
                ->method('execute')
                ->with($productMock)
                ->willReturn([]);
        }

        $this->productScopeRewriteGenerator->expects($this->never())
            ->method('generateForSpecificStoreView');

        $this->assertEquals([], $this->productUrlRewriteGenerator->generate($productMock, 1));
    }

    /**
     * Not visible individually in the product's store view, but visible in another one: no rewrites for this store.
     *
     * @return void
     */
    public function testGenerateNotVisibleInStore()
    {
        if (!$this->visibleForStores) {
            $this->markTestSkipped('GetVisibleForStores exists in Magento >= 2.4.7 only');
        }

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->method('getVisibility')->willReturn(1);
        $productMock->method('getStoreId')->willReturn(2);

        $this->visibleForStores->method('execute')->willReturn([3]);

        $this->productScopeRewriteGenerator->expects($this->never())
            ->method('generateForSpecificStoreView');

        $this->assertEquals([], $this->productUrlRewriteGenerator->generate($productMock, 1));
    }

    /**
     * Not visible individually in the default scope but visible in some store view: rewrites are generated.
     *
     * @return void
     */
    public function testGenerateNotVisibleDefaultScopeVisibleElsewhere()
    {
        if (!$this->visibleForStores) {
            $this->markTestSkipped('GetVisibleForStores exists in Magento >= 2.4.7 only');
        }

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->method('getVisibility')->willReturn(1);
        $productMock->method('getStoreId')->willReturn(0);

        $this->visibleForStores->method('execute')->willReturn([1]);
        $this->scopeConfig->method('isSetFlag')->willReturn(false);

        $urls = ['dummy-url.html'];
        $this->productScopeRewriteGenerator->method('isGlobalScope')->with(0)->willReturn(true);
        $this->productScopeRewriteGenerator->expects($this->once())
            ->method('generateForGlobalScope')
            ->with([], $productMock, 1)
            ->willReturn($urls);

        $this->assertEquals($urls, $this->productUrlRewriteGenerator->generate($productMock, 1));
    }
}
