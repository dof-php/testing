<?php

$gwt->unit('Testing Util::isClosure()', function ($t) {
    $t->false(\DOF\Testing\Util::isClosure(null));
    $t->false(\DOF\Testing\Util::isClosure(0));
    $t->false(\DOF\Testing\Util::isClosure(''));
    $t->false(\DOF\Testing\Util::isClosure([]));
    $t->true(\DOF\Testing\Util::isClosure(function () {}));
});

$gwt->unit('Testing Util::getObjectName()', function ($t) {
    $t->eq('Closure', \DOF\Testing\Util::getObjectName(function () {}));
    $t->null(\DOF\Testing\Util::getObjectName(null));
    $t->null(\DOF\Testing\Util::getObjectName(0));
    $t->null(\DOF\Testing\Util::getObjectName(''));
    $t->null(\DOF\Testing\Util::getObjectName([]));
});
