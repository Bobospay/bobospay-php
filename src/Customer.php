<?php

namespace Bobospay;

use Bobospay\Common\BobospayObject;

/**
 * Class Customer
 *
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $phone
 * @property string $created_at
 * @property string $updated_at
 *
 * @package Bobospay
 */

class Customer extends BobospayObject
{
    use Api\All;
    use Api\Retrieve;
    use Api\Create;

}