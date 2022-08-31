<?php

declare (strict_types=1);

namespace Contributte\RabbitMQ\Diagnostics;

use Contributte\RabbitMQ\Producer\IProducer;
use Contributte\RabbitMQ\Producer\ProducerFactory;
use Nette\Utils\Html;
use Tracy\IBarPanel;

class BarPanel implements IBarPanel
{

	/**
	 * how many messages to display maximum on tracy bar, set 0 for unlimited?
	 */
	public static int $displayCount = 100;

	/**
	 * @var array<string, string[]>
	 */
	private array $sentMessages = [];
	private int $totalMessages = 0;

	public function __construct(private ProducerFactory $producerFactory)
	{
		$this->producerFactory->addOnCreatedCallback(
			function (string $name, IProducer $producer): void {
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
		$panel = Html::el();
		$panel->addHtml(Html::el('h1')->setText("RabbitMq, total sent {$this->totalMessages}"));

		if (self::$displayCount !== 0 && $this->totalMessages > self::$displayCount) {
			$panel->addHtml('p')->setText('Displayed only first ' . self::$displayCount . ' messages');
		}


		$table = Html::el('table', ['class' => 'tracy-bs-main']);
		foreach ($this->sentMessages as $producer => $messages) {
			$table->addHtml(Html::el()->setHtml('<tr><th>Producer: ' . $producer . '</th></tr>'));
			foreach ($messages as $message) {
				$table->addHtml(Html::el()->setHtml('<tr><td><pre>' . Html::el()->setText($message) . '</pre></td></tr>'));
			}
		}

		$panel->addHtml(Html::el('div', ['class' => 'tracy-inner'])->addHtml($table));

		return (string) $panel;
	}
}
