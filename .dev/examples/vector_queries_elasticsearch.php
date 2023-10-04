<?php

use Elastic\Elasticsearch\ClientBuilder;
use GuzzleHttp\Client as GuzzleClient;
use MNC\LLM;
use MNC\LLM\Core\Document;
use Ramsey\Uuid\Uuid;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';

$ns = Uuid::fromString('3e96f6f9-3393-405e-a0d5-da9540327615');

$openAI = LLM\Backends\OpenAI::create(getenv('OPEN_AI_TOKEN'));
$elasticBackend = new LLM\Backends\ElasticSearch(
    ClientBuilder::create()
        ->setHttpClient(new GuzzleClient())
        ->setHosts(['elastic:9200'])
        ->build(),
    $openAI,
    LLM\Backends\OpenAI::VECTOR_DIMENSIONS
);

$elasticBackend->init();

$phrases = [
    'The cat chases the mouse',
    'The kitten hunts rodents',
    'I like ham sandwiches',
    'Santiago is the capital of Chile',
];

$documents = [];
foreach ($phrases as $i => $phrase) {
    $documents[] = Document::withId(Uuid::uuid3($ns, $i), $phrase);
}

$elasticBackend->insert(...$documents);

$vector = $openAI->vectorize('Tom and Jerry');

$documents = $elasticBackend->searchKNN($vector, 2);

foreach ($documents as $document) {
    echo sprintf('"%s" (score=%f)', $document->content, $document->getScore()).PHP_EOL;
}