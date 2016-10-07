<?php

use Nicat\GoodReads\GoodReads;

class GoodReadsTest extends PHPUnit\Framework\TestCase {

    public function testAuthorIDByName()
    {
        $gr = new GoodReads('v1IdnkPHSutKrJ8UVKkDcQ');
        $search = $gr->authorIDByName('Joshgun Karimov');

        $this->assertInternalType("int", $search);
    }

}