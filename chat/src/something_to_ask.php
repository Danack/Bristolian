<?php



//function foo()
//{
//    yield "first";
//    yield "second";
//    yield "third";
//}
//
//$simple_loop = function () use ($logger) {
//    while (true) {
//        $logger->info("simple loop");
//        \Amp\delay(0.5); // Wait a bit
//    }
//};
//
//$yield_loop = function () use ($logger) {
//    while (true) {
//        $value = yield from foo();
//        $logger->info("value is $value");
//        $logger->info("yield_loop");
//        \Amp\delay(0.5); // Wait a bit
//    }
//};

Amp\async($redis_loop);
//Amp\async($simple_loop);

print "Await SIGINT or SIGTERM to be received." . PHP_EOL;
$signal = Amp\trapSignal([\SIGINT, \SIGTERM]);
exit(0);