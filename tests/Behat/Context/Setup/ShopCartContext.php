<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\PayPalPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class ShopCartContext implements Context
{
    /**
     * @param FactoryInterface<OrderInterface> $orderFactory
     * @param FactoryInterface<OrderItemInterface> $orderItemFactory
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(
        private readonly FactoryInterface $orderFactory,
        private readonly FactoryInterface $orderItemFactory,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProductVariantResolverInterface $variantResolver,
        private readonly OrderItemQuantityModifierInterface $itemQuantityModifier,
        private readonly SharedStorageInterface $sharedStorage,
    ) {
    }

    #[Given('the logged in customer has product :product in the cart')]
    public function theLoggedInCustomerHasProductInTheCart(ProductInterface $product): void
    {
        /** @var ShopUserInterface $user */
        $user = $this->sharedStorage->get('user');
        $customer = $user->getCustomer();

        /** @var ChannelInterface $channel */
        $channel = $this->sharedStorage->get('channel');

        /** @var OrderInterface $order */
        $order = $this->orderFactory->createNew();
        $order->setCustomerWithAuthorization($customer);
        $order->setChannel($channel);
        $order->setLocaleCode($channel->getDefaultLocale()->getCode());
        $order->setCurrencyCode($channel->getBaseCurrency()->getCode());

        $variant = $this->variantResolver->getVariant($product);

        /** @var OrderItemInterface $item */
        $item = $this->orderItemFactory->createNew();
        $item->setVariant($variant);

        $channelPricing = $variant->getChannelPricingForChannel($channel);
        $item->setUnitPrice($channelPricing->getPrice());

        $this->itemQuantityModifier->modify($item, 1);
        $order->addItem($item);

        $this->orderRepository->add($order);

        $this->sharedStorage->set('order', $order);
    }
}
