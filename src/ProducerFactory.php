<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ;

final class ProducerFactory
{

	/**
	 * @var ProducersDataBag
	 */
	private $producersDataBag;


	public function __construct(ProducersDataBag $producersDataBag)
	{
		$this->producersDataBag = $producersDataBag;
	}

}
