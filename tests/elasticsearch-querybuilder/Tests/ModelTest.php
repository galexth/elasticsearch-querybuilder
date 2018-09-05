<?php

namespace Galexth\QueryBuilder\Tests;

use Elastica\Client;
use Elastica\Query\BoolQuery;
use Galexth\QueryBuilder\Builder;
use Galexth\QueryBuilder\Tests\Rules\Prospect;

final class ParseTest extends TestCase
{
    /**
     * @var \Elastica\Client
     */
    protected $client;

    /**
     * @var \Elastica\Type
     */
    protected $type;

    /**
     * ParseTest constructor.
     *
     * @param null|string $name
     * @param array       $data
     * @param string      $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

//        $this->client = new Client([
//            'host' => '0.0.0.0',
//            'port' => 2040
//        ]);
//
//        $this->type = $this->client->getIndex('salestools_prospector_local_prospect')
//            ->getType('prospect');
    }

    public function testParse1()
    {
        $query = '@name is Prof. Johan Schoen and @industry is Network Security Hardware & Software';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse2()
    {
        $query = '@name is "John" and @industry is \'Medicine\'';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse3()
    {
        $query = '@name is "John" and @industry is \'Medicine\' or (@industry is Oil and @name is Chris) or @name is Jesus';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse4()
    {
        $query = '@name is "John" and @industry is \'Medicine\' or @tags has 12,13';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse5()
    {
        $query = '@name is John';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse6()
    {
        $query = '@name is John and (@industry is Medicine)';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse7()
    {
        $query = '@keywords has John';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse8()
    {
        $query = '@revenue gte 123 and @revenue lt 523';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse9()
    {
        $query = '@revenue between 2018-01-01, 2018-01-02';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse10()
    {
        $query = '@location.country has asds';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse11()
    {
        $query = '@rank.alexa is 15';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse12()
    {
        $query = '@persona is 15';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse13()
    {
        $query = '@revenue between "2018-01-01 and 2018-01-02"';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse14()
    {
        $query = '@revenue before 2018-01-01';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse15()
    {
        $query = '@revenue after 2018-01-01';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse16()
    {
        $query = '@revenue less 12';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }

    public function testParse17()
    {
        $query = '@revenue more 12';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        $response = $this->type->search($bool);

        d($bool->toArray());

        $this->assertTrue($response->getResponse()->isOk());
    }
}
