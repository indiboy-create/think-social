<?php

declare(strict_types=1);

namespace Youvision\ThinkSocial\Exception;

use RuntimeException;

/**
 * OAuth state 校验失败。
 */
class InvalidStateException extends RuntimeException
{
}