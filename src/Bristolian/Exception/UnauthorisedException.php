<?php

namespace Bristolian\Exception;

/**
 * If a user attempts to go to an endpoint that requires them to be logged
 * in, this exception is thrown.
 */
class UnauthorisedException extends BristolianException
{

}
