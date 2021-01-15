<?php

declare (strict_types = 1);

namespace Contributte\RabbitMQ\Diagnostics;

use Contributte\RabbitMQ\Producer\Producer;
use Contributte\RabbitMQ\Producer\ProducerFactory;
use Nette\Utils\Html;
use Tracy\Helpers;
use Tracy\IBarPanel;

class BarPanel implements IBarPanel
{

	private ProducerFactory $producerFactory;

	/**
	 * how many messages to display maximum on tracy bar, set 0 for unlimited?
	 */
	public static int $displayCount = 100;

	/**
	 * @var array<string, string[]>
	 */
	private $sentMessages = [];

	private int $totalMessages = 0;

	public function __construct(ProducerFactory $producerFactory)
	{
		$this->producerFactory = $producerFactory;

		$this->producerFactory->onCreated[] = function (string $name, Producer $producer) {
			$this->sentMessages[$name] = [];
			$producer->onPublish[] = function (string $message) use ($name) {
				if (self::$displayCount === 0 || $this->totalMessages < self::$displayCount) {
					$this->sentMessages[$name][] = $message;
				}

				$this->totalMessages++;
			};
		};
	}


	/**
	 * @return string
	 */
	public function getTab()
	{
		$img = Html::el('')->addHtml(file_get_contents(__DIR__ . '/rabbitmq-icon.svg'));
		$tab = Html::el('span')->title('RabbitMq')->addHtml($img);

		if ($this->totalMessages > 0) {
			$title = Html::el('span')->class('tracy-label');
			$tab->addHtml($title->setText(' (' . $this->totalMessages . ')'));
		}

		return (string) $tab;
	}


	function getPanel()
	{
		return Helpers::capture(function () {
			require __DIR__ . '/BarPanel.phtml';
		});
	}

}
