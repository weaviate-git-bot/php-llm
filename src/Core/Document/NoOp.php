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

namespace MNC\LLM\Core\Document;

use MNC\LLM\Core\Document;
use MNC\LLM\Core\TextSplitter;

final class NoOp implements Loader, Inserter, KNNSearcher
{
    public function loadDocuments(TextSplitter $splitter): \Iterator
    {
        return new \ArrayIterator([]);
    }

    public function searchKNN(Vector $vector, int $k): array
    {
        return [];
    }

    public function insert(Document ...$documents): void
    {
        // Noop
    }
}
