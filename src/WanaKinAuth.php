<?php
namespace WanaKin\Auth;

use WanaKin\Auth\Verifiable;
use WanaKin\Auth\Authenticatable;

trait WanaKinAuth {
    use Verifiable, Authenticatable;
}
