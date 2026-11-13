<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents;

enum Sex: string
{
    case Female = 'female';
    case Male = 'male';
}
