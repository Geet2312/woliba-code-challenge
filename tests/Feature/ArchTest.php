<?php

test('does not use debugging functions', function () {

    expect(['dd', 'dump', 'ray', 'var_dump', 'echo'])
        ->not->toBeUsed();

});
