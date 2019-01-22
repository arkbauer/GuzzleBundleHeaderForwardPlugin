<?php

namespace EncoreLabs\Bundle\GuzzleBundleHeaderForwardPlugin;

use EightPoints\Bundle\GuzzleBundle\EightPointsGuzzleBundlePlugin;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GuzzleBundleHeaderForwardPlugin extends Bundle implements EightPointsGuzzleBundlePlugin
{
    /**
     * {@inheritdoc}
     */
    public function getPluginName(): string
    {
        return 'header_forward';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $pluginNode)
    {
        $pluginNode
            ->canBeEnabled()
            ->children()
                ->arrayNode('headers')
                    ->normalizeKeys(false)
                    ->scalarPrototype()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function loadForClient(array $config, ContainerBuilder $container, string $clientName, Definition $handler)
    {
        if (true === $config['enabled'] && !empty($config['headers'])) {
            $forwardHeaderMiddlewareDefinitionName = sprintf('guzzle_bundle_header_forward_plugin.middleware.%s', $clientName);
            $forwardHeaderMiddlewareDefinition     = new Definition(GuzzleForwardHeaderMiddleware::class);
            $forwardHeaderMiddlewareDefinition->setArguments([
                new Reference('request_stack'),
                $config['headers']
            ]);

            $container->setDefinition($forwardHeaderMiddlewareDefinitionName, $forwardHeaderMiddlewareDefinition);

            $forwardHeaderMiddlewareExpression = new Expression(sprintf(
                'service(\'%s\')',
                $forwardHeaderMiddlewareDefinitionName
            ));

            $handler->addMethodCall('unshift', [$forwardHeaderMiddlewareExpression, $this->getPluginName()]);
        }
    }
}
