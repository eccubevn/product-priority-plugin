<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductPriority\Tests\Web\HookPoint;

use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Plugin\ProductPriority\Entity\ProductPriority;
use Plugin\ProductPriority\Repository\ProductPriorityRepository;

class OnAdminProductDeleteCompleteTest extends AbstractAdminWebTestCase
{
    /**
     * @var ProductPriorityRepository
     */
    private $productPriorityRepos;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->productPriorityRepos = $this->container->get(ProductPriorityRepository::class);
    }

    public function testOnAdminProductDeleteComplete()
    {
        $Product = $this->createProduct(null, 0);
        $i = 1;
        foreach ($Product->getProductCategories() as $ProductCategory) {
            $ProductPriority = new ProductPriority();
            $ProductPriority->setProductId($Product->getId());
            $ProductPriority->setCategoryId($ProductCategory->getCategory()->getId());
            $ProductPriority->setPriority($i++);
            $this->entityManager->persist($ProductPriority);
            $this->entityManager->flush($ProductPriority);
        }

        $ProductPriorities = $this->productPriorityRepos
            ->findBy(['product_id' => $Product->getId()]);

        $this->assertGreaterThanOrEqual(1, count($ProductPriorities), '1件以上登録されている');

        $crawler = $this->client->request(
            'DELETE',
            $this->generateUrl('admin_product_product_delete', ['id' => $Product->getId()])
        );

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(
                $this->generateUrl('admin_product_page', ['page_no' => 1]).'?resume=1'
            )
        );

        $ProductPriorities = $this->productPriorityRepos
            ->findBy(['product_id' => $Product->getId()]);

        $this->expected = 0;
        $this->actual = count($ProductPriorities);
        $this->verify('削除されているはず');
    }
}
