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

use MNC\LLM\Core\Chat\History;
use MNC\LLM\Core\Chatter;
use MNC\LLM\Core\Document\Vector;
use MNC\LLM\Core\Instructor;
use MNC\LLM\Core\Tokenizer;
use MNC\LLM\Core\Vectorizer;
use OpenAI\Client;
use Yethee\Tiktoken\EncoderProvider;

/**
 * The OpenAI backend abstracts away all the operations you can do with OpenAI.
 *
 * It is capable of simple instruction prompts, chat prompts, vectorization of text and tokenization.
 */
final class OpenAI implements Instructor, Vectorizer, Tokenizer, Chatter
{
    public const VECTOR_DIMENSIONS = 1536;

    private const DEFAULT_INSTRUCT_MODEL = 'text-davinci-003';
    private const DEFAULT_CHAT_MODEL = 'gpt-3.5-turbo';
    private const DEFAULT_VECTOR_MODEL = 'text-embedding-ada-002';

    public function __construct(
        private readonly Client $client,
        private readonly EncoderProvider $provider,
        private readonly string $vectorModel,
        private readonly string $chatModel,
        private readonly string $instructModel,
        private readonly float $temperature,
        private readonly int $maxOutputTokens,
    ) {
    }

    public static function create(
        string $token,
        string $vectorModel = self::DEFAULT_VECTOR_MODEL,
        string $chatModel = self::DEFAULT_CHAT_MODEL,
        string $instructModel = self::DEFAULT_INSTRUCT_MODEL,
        string $vocabularyCachePath = '/tmp',
        float $temperature = 0,
        int $maxOutputTokens = 500,
    ): OpenAI {
        $provider = new EncoderProvider();
        $provider->setVocabCache($vocabularyCachePath);

        return new self(
            \OpenAI::client($token),
            $provider,
            $vectorModel,
            $chatModel,
            $instructModel,
            $temperature,
            $maxOutputTokens
        );
    }

    public function tokenize(string $text): array
    {
        return $this->provider->getForModel($this->chatModel)->encode($text);
    }

    public function countTokens(string $text): int
    {
        return count($this->tokenize($text));
    }

    public function getMaxTokens(): int
    {
        return 4_096;
    }

    public function instruct(string $prompt): string
    {
        $response = $this->client->completions()->create([
            'prompt' => $prompt,
            'model' => $this->instructModel,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxOutputTokens,
        ]);

        return trim($response->choices[0]->text);
    }

    public function vectorize(string $text): Vector
    {
        $response = $this->client->embeddings()->create([
            'input' => $text,
            'model' => $this->vectorModel,
        ]);

        return new Vector($response->embeddings[0]->embedding);
    }

    public function chat(string $system, History $history): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $system,
            ],
        ];

        foreach ($history->getMessages() as $message) {
            $messages[] = [
                'role' => $message->author->value,
                'content' => $message->contents,
            ];
        }

        $response = $this->client->chat()->create([
            'messages' => $messages,
            'model' => $this->chatModel,
            'max_tokens' => $this->maxOutputTokens,
            'temperature' => $this->temperature,
        ]);

        return trim($response->choices[0]->message->content);
    }
}
