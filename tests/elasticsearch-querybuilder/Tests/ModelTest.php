<?php

namespace Galexth\QueryBuilder\Tests;

use Galexth\QueryBuilder\Builder;
use Galexth\QueryBuilder\Tests\Rules\Prospect;

final class ParseTest extends TestCase
{

    public function testParse()
    {
        $query = ['query', 'or', 'query', 'or', 'query', 'and', 'query'];

//        $query = '@name is dsf OR (@name has and AND @industry is "rty(tr), cxvcv" OR (@name has "and" AND @industry is "rtytr")) AND @name is "axe" AND @industry has "asd or , dfg" OR (@name has "vxv" AND @industry is "rtytr") AND @name is "axe" OR (@asd has sdff AND (@dsfdsf is xvbvc OR @dsf is fds)) AND @sd is dsf';
        $query = '@industry is empty and @name is dsf';
//        $query = '@name is dsf and @industry is empty';
//        $query = '@name is dsf and @industry is not aaa or @name is bbb or (@name is ccc and @industry is nnnnn and (@industry is oooooo)) and @industry is vvvvv and (@name is pppp and @industry is zzzz)';
//        $query = '@name is "axe" AND @industry has "asd, dfg" OR (@name has vxv AND @industry is rtytr)';
        $builder = new Builder(new Prospect);

        d(json_encode($builder->run($query)->toArray()));
    }

}
