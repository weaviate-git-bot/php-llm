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
use MNC\LLM\Core\Vectorizer;
use Weaviate\Weaviate as WeaviateClient;

/**
 * Implements Weaviate as a storage engine.
 *
 * Weaviate is quite performant and easy to use.
 */
final class Weaviate implements KNNSearcher, Inserter
{
    public function __construct(
        private readonly WeaviateClient $client,
        private readonly Vectorizer $vectorizer,
        private readonly string $className = 'TextDocument',
    ) {
    }

    public function init(): void
    {
        $this->client->schema()->createClass([
            'class' => $this->className,
            'vectorizer' => 'none',
            'properties' => [
                [
                    'name' => 'contents',
                    'dataType' => ['string'],
                ],
            ],
        ]);
    }

    public function insert(Document ...$documents): void
    {
        $batch = [];
        foreach ($documents as $document) {
            $vector = $this->vectorizer->vectorize($document->content);

            $batch[] = [
                'class' => $this->className,
                'id' => $document->getId(),
                'properties' => [
                    'contents' => $document->content,
                ],
                'vector' => $vector->points,
            ];
        }

        $this->client->batch()->create($batch);
    }

    /**
     * @return array|Document[]
     */
    public function searchKNN(Vector $vector, int $k): array
    {
        $vec = $vector->toString();

        $data = $this->client->graphql()->get("{
            Get {
                {$this->className}(
                    nearVector: {
                        vector: {$vec}
                    }, limit: {$k}
                ) {
                    contents
                    _additional { 
                        id
                        distance
                        certainty
                    }
                }
            }
        }");

        $documents = [];

        foreach ($data['data']['Get'][$this->className] ?? [] as $result) {
            $documents[] = Document::withId(
                $result['_additional']['id'],
                $result['contents'],
            )
                ->withCertainty($result['_additional']['certainty'])
                ->withDistance($result['_additional']['distance'])
            ;
        }

        return $documents;
    }
}
