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

namespace MNC\LLM\Core\Chat;

use MNC\LLM\Core\Chat;
use MNC\LLM\Core\Chat\History\Author;
use MNC\LLM\Core\Chat\History\Message;

final class ArrayHistory implements Chat, History
{
    /**
     * @var Message[]
     */
    private array $history = [];

    public function __construct(
        private readonly Chat $next
    ) {
    }

    public function receive(): string
    {
        $message = $this->next->receive();

        $this->history[] = new Message(
            Author::USER,
            $message
        );

        return $message;
    }

    public function send(string $message): void
    {
        $this->history[] = new Message(
            Author::ASSISTANT,
            $message,
        );

        $this->next->send($message);
    }

    /**
     * @return Message[]
     */
    public function getMessages(): array
    {
        return $this->history;
    }

    public function hasMessages(): bool
    {
        return [] !== $this->history;
    }
}
