<?php

namespace App\Contract;

interface BuilderInterface
{
    public function build(): object;

    public function reset(): self;

}
