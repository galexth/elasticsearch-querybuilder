<?php

namespace Galexth\QueryBuilder\Tests;

use Elastica\Query\BoolQuery;
use Galexth\QueryBuilder\Builder;
use Galexth\QueryBuilder\Tests\Rules\Prospect;

final class ParseTest extends TestCase
{

    public function testParse1()
    {
        $query = '@name is John and @industry is Medicine';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        d($bool->toArray());
    }

    public function testParse2()
    {
        $query = '@name is "John" and @industry is \'Medicine\'';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        d($bool->toArray());
    }

    public function testParse3()
    {
        $query = '@name is "John" and @industry is \'Medicine\' or (@industry is Oil and @name is Chris) or @name is Jesus';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        d($bool->toArray());
    }

    public function testParse4()
    {
        $query = '@name is "John" and @industry is \'Medicine\' or @tags has 12,13';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        d($bool->toArray());
    }

    public function testParse5()
    {
        $query = '@name is John';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        d($bool->toArray());
    }

    public function testParse6()
    {
        $query = '@name is John and (@industry is Medicine)';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        d($bool->toArray());
    }

    public function testParse7()
    {
        $query = '@keywords has John';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        d($bool->toArray());
    }

    public function testParse8()
    {
        $query = '@revenue gte 123 and @revenue lt 523';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        d($bool->toArray());
    }

    public function testParse9()
    {
        $query = '@revenue between 2018-01-01, 2018-01-02';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        d($bool->toArray());
    }

    public function testParse10()
    {
        $query = '@location.country has asds';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        d($bool->toArray());
    }

    public function testParse11()
    {
        $query = '@rank.alexa is 15';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        d($bool->toArray());
    }

    public function testParse12()
    {
        $query = '@persona is 15';

        $builder = new Builder(new Prospect);

        $bool = $builder->build($query);

        $this->assertTrue($bool instanceof BoolQuery);

        d($bool->toArray());
    }
}
