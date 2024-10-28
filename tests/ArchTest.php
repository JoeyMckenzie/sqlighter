<?php

declare(strict_types=1);

arch('it will not use debugging functions')
    ->expect(['dd', 'dump'])
    ->each->not->toBeUsed();

arch('service provider and commands are final')
    ->expect('JoeyMcKenzie\\Sqlighter')
    ->toBeFinal();
