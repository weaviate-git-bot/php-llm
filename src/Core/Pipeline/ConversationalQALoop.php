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

namespace MNC\LLM\Core\Pipeline;

use MNC\LLM\Core\Chat;
use MNC\LLM\Core\Chat\History;
use MNC\LLM\Core\Chatter;
use MNC\LLM\Core\Document;
use MNC\LLM\Core\Pipeline;
use MNC\LLM\Core\Prompt\TemplateFactory;
use MNC\LLM\Core\Vectorizer;

/**
 * TODO: This QA loop is quite naive and it doesn't account for the chat history growing too large in tokens.
 *  At some point, in a big conversation, it will explode.
 *  I need to make it resilient.
 */
final class ConversationalQALoop implements Pipeline
{
    private const TEMPLATE = 'conversational_qa_loop.system';

    public function __construct(
        private readonly Chat&History $chat,
        private readonly Chatter&Vectorizer $llm,
        private readonly Document\KNNSearcher $documents,
        private readonly TemplateFactory $templates,
        private readonly string $template = self::TEMPLATE,
        private readonly int $contextSize = 3,
        private readonly string $endMark = '[DONE]',
    ) {
    }

    public function run(string $message = 'Hi, how can I help you today?'): void
    {
        $this->chat->send($message);

        $ongoing = true;
        while ($ongoing) {
            $message = $this->chat->receive();
            if ('' === $message) {
                continue;
            }

            $vector = $this->llm->vectorize($message);

            $documents = $this->documents->searchKNN($vector, $this->contextSize);

            $system = $this->templates->create($this->template)->render([
                'context' => $this->documentsToText($documents),
                'end' => $this->endMark,
            ]);

            $response = $this->llm->chat($system, $this->chat);
            [$response, $shouldFinish] = $this->processResponse($response);

            if ($shouldFinish) {
                $ongoing = false;
            }

            $this->chat->send($response);
        }
    }

    /**
     * @return array{0: string, 1: bool}
     */
    public function processResponse(string $response): array
    {
        if (str_ends_with($response, $this->endMark)) {
            $response = str_replace($this->endMark, '', $response);

            return [$response, true];
        }

        return [$response, false];
    }

    /**
     * @param Document[] $documents
     */
    private function documentsToText(array $documents): string
    {
        $lines = [];
        foreach ($documents as $document) {
            $lines[] = $document->content;
        }

        return implode("\n", $lines);
    }
}
