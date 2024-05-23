<?php

/**
 * Joomla Article Notification - System plugin
 *
 * @copyright  (C) 2024 Jeroen Moolenschot | Joomill Extensions <https://www.joomill-extensions.com>
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomill\Plugin\System\ArticleNotification\Extension\ArticleNotification;

return new class () implements ServiceProviderInterface {

	public function register(Container $container): void
	{
		$container->set(
			PluginInterface::class,
			function (Container $container) {
				$plugin     = new ArticleNotification(
					$container->get(DispatcherInterface::class),
					(array) PluginHelper::getPlugin('system', 'articlenotification')
				);
				$plugin->setApplication(Factory::getApplication());
				$plugin->setDatabase($container->get(DatabaseInterface::class));
				$plugin->setUserFactory($container->get(UserFactoryInterface::class));

				return $plugin;
			}
		);
	}
};
