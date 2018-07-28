<?php

namespace Galexth\QueryBuilder\Tests;

use Galexth\QueryBuilder\Builder;
use Galexth\QueryBuilder\Tests\Rules\Prospect;

final class ParseTest extends TestCase
{

    public function testParse()
    {
        $query = '@sd is dsf OR (@name has and AND @industry is "rty(tr), cxvcv" OR (@name has "and" AND @industry is "rtytr")) AND @name is "axe" AND @industry has "asd or , dfg" OR (@name has "vxv" AND @industry is "rtytr") AND @name is "axe" OR (@asd has sdff AND (@dsfdsf is xvbvc OR @dsf is fds)) AND @sd is dsf';
//        $query = '@name is "axe" AND @industry has "asd, dfg" OR (@name has vxv AND @industry is rtytr)';
        $builder = new Builder(new Prospect);

        dd($builder->run($query));
    }

}
