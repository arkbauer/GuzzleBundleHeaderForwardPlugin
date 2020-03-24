<?php

namespace EncoreLabs\Bundle\GuzzleBundleHeaderForwardPlugin;

use EightPoints\Bundle\GuzzleBundle\PluginInterface;
use EncoreLabs\Bundle\GuzzleBundleHeaderForwardPlugin\DependencyInjection\GuzzleBundleHeaderForwardExtension;
use EncoreLabs\Bundle\GuzzleBundleHeaderForwardPlugin\Middleware\GuzzleForwardHeaderMiddleware;
use Exception;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GuzzleBundleHeaderForwardPlugin extends Bundle implements PluginInterface
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
    public function addConfiguration(ArrayNodeDefinition $pluginNode): void
    {
        $pluginNode
            ->canBeEnabled()
                ->children()
                    ->arrayNode('headers')
                    ->normalizeKeys(false)
                    ->scalarPrototype()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function loadForClient(array $config, ContainerBuilder $container, string $clientName, Definition $handler): void
    {
        if (true === $config['enabled'] && !empty($config['headers'])) {
            $definitionName = sprintf('guzzle_bundle_header_forward_plugin.middleware.%s', $clientName);
            $middlewareDefinition = new Definition(GuzzleForwardHeaderMiddleware::class);
            $middlewareDefinition->setArguments([
                new Reference('request_stack'),
                $config['headers']
            ]);

            $container->setDefinition($definitionName, $middlewareDefinition);

            $middlewareExpression = new Expression(sprintf(
                'service(\'%s\')',
                $definitionName
            ));

            $handler->addMethodCall('unshift', [$middlewareExpression, $this->getPluginName()]);
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $extension = new GuzzleBundleHeaderForwardExtension();
        $extension->load($configs, $container);

    }
}
