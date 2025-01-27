<?php

declare(strict_types=1);

namespace Tests\Mock;

class FakeTool
{
    public function __invoke(int $param1, string $param2): string
    {
        return 'Params passed: param1='.$param1.', param2='.$param2;
    }
}
