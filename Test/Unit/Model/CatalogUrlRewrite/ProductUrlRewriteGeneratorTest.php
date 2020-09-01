<?php

namespace Aune\ProductCategoryUrlFix\Test\Unit\Model\CatalogUrlRewrite;

use ReflectionClass;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Aune\ProductCategoryUrlFix\Model\CatalogUrlRewrite\ProductUrlRewriteGenerator;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductUrlRewriteGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $canonicalUrlRewriteGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $currentUrlRewritesRegenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $categoriesUrlRewriteGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $anchorUrlRewriteGenerator;

    /** @var \Aune\ProductCategoryUrlFix\Model\CatalogUrlRewrite\ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeViewService;

    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectRegistryFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;
    
    /** @var \use Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    private $productScopeRewriteGenerator;

    /**
     * Test method
     */
    protected function setUp()
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
            ->setMethods(['create'])
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

        // Inject private property to avoid ObjectManager error
        $reflection = new ReflectionClass(get_class($this->productUrlRewriteGenerator));
        $reflectionProperty = $reflection->getProperty('productScopeRewriteGenerator');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->productUrlRewriteGenerator, $this->productScopeRewriteGenerator);
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
        
        $productCategoriesMock->expects($this->exactly(2))
            ->method('addAttributeToSelect')
            ->withConsecutive(['url_key'], ['url_path'])
            ->willReturnSelf();
        
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
}
