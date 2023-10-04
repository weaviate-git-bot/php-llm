<?php

use MNC\LLM;
use MNC\LLM\Core\Document;
use OpenSearch\ClientBuilder;
use Ramsey\Uuid\Uuid;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';

$ns = Uuid::fromString('3e96f6f9-3393-405e-a0d5-da9540327615');

$openAI = LLM\Backends\OpenAI::create(getenv('OPEN_AI_TOKEN'));
$openSearchBackend = new LLM\Backends\OpenSearch(
    ClientBuilder::create()
        ->setHosts(['opensearch:9200'])
        ->setSSLVerification(false)
        ->setBasicAuthentication('admin', 'admin')
        ->build(),
    $openAI,
    LLM\Backends\OpenAI::VECTOR_DIMENSIONS
);

$openSearchBackend->init();

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

$openSearchBackend->insert(...$documents);

$vector = $openAI->vectorize('Tom and Jerry');

$documents = $openSearchBackend->searchKNN($vector, 2);

foreach ($documents as $document) {
    echo sprintf('"%s" (score=%f)', $document->content, $document->getScore()).PHP_EOL;
}