<?php

namespace Bobospay;

use Bobospay\Common\BobospayObject;

/**
 * Class Currency
 *
 * @property int $id
 * @property string $name
 * @property string $iso
 * @property int $code
 * @property string $created_at
 * @property string $updated_at
 *
 * @package Bobospay
 */
class Currency extends BobospayObject
{
    use Api\All;
    use Api\Retrieve;
}
