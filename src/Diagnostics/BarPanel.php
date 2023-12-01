<?php declare (strict_types = 1);

namespace Contributte\RabbitMQ\Diagnostics;

use Contributte\RabbitMQ\Producer\Producer;
use Contributte\RabbitMQ\Producer\ProducerFactory;
use Nette\Utils\Html;
use Tracy\IBarPanel;

class BarPanel implements IBarPanel
{

	/**
	 * how many messages to display maximum on tracy bar, set 0 for unlimited?
	 */
	public static int $displayCount = 100;

	private ProducerFactory $producerFactory;

	/** @var array<string, string[]> */
	private array $sentMessages = [];

	private int $totalMessages = 0;

	public function __construct(ProducerFactory $producerFactory)
	{
		$this->producerFactory = $producerFactory;

		$this->producerFactory->addOnCreatedCallback(
			function (string $name, Producer $producer): void {
				$this->sentMessages[$name] = [];
				$producer->addOnPublishCallback(
					function (string $message) use ($name): void {
						if (self::$displayCount === 0 || $this->totalMessages < self::$displayCount) {
							$this->sentMessages[$name][] = $message;
						}

						$this->totalMessages++;
					}
				);
			}
		);
	}

	public function getTab(): string
	{
		$img = Html::el('')->addHtml((string) file_get_contents(__DIR__ . '/rabbitmq-icon.svg'));
		$tab = Html::el('span')->title('RabbitMq')->addHtml($img);

		if ($this->totalMessages > 0) {
			$title = Html::el('span')->class('tracy-label');
			$tab->addHtml($title->setText(' (' . $this->totalMessages . ')'));
		}

		return (string) $tab;
	}

	public function getPanel(): string
	{
		ob_start(static function (): void {
		});

		// @codingStandardsIgnoreStart
		$sentMessages = $this->sentMessages;
		$totalMessages = $this->totalMessages;
		$displayCount = self::$displayCount;
		// @codingStandardsIgnoreEnd

		try {
			require __DIR__ . '/BarPanel.phtml';

			return (string) ob_get_clean();
		} catch (\Throwable $e) {
			ob_get_clean();

			throw $e;
		}
	}

}
