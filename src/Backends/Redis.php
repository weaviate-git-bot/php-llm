<?php

declare(strict_types=1);

/**
 * @project PHP LLM
 * @link https://github.com/mnavarrocarter/php-llm
 * @project mnavarrocarter/php-llm
 * @author Matias Navarro-Carter mnavarrocarter@gmail.com
 * @license BSD-3-Clause
 * @copyright 2023 Castor Labs Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MNC\LLM\Backends;

use MNC\LLM\Core\Document;
use MNC\LLM\Core\Document\Inserter;
use MNC\LLM\Core\Document\KNNSearcher;
use MNC\LLM\Core\Document\Vector;
use Predis\Client;

final class Redis implements KNNSearcher, Inserter
{
    public function __construct(
        private readonly Client $predis,
        private readonly int $dimensions,
        private readonly string $indexName = 'documents',
    ) {
    }

    public function insert(Document ...$documents): void
    {
        throw new \LogicException('Not Implemented');
    }

    /**
     * @return Document[]
     */
    public function searchKNN(Vector $vector, int $k): array
    {
        throw new \LogicException('Not Implemented');
    }
}
